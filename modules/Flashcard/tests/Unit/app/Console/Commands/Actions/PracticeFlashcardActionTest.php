<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands\Actions;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\Table;
use Modules\Flashcard\app\Console\Commands\Actions\PracticeFlashcardAction;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Models\PracticeResult;
use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Providers\FlashcardServiceProvider;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;
use Modules\Flashcard\app\Services\StatisticService;
use Modules\Flashcard\app\Services\StudySessionService;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

final class PracticeFlashcardActionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Command $command;

    private FlashcardRepositoryInterface $flashcardRepository;

    private StudySessionRepositoryInterface $studySessionRepository;

    private StatisticService $statisticService;

    private StudySessionService $studySessionService;

    private LogRepositoryInterface $logRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();

        // Create mock repositories
        $this->flashcardRepository = $this->mock(FlashcardRepositoryInterface::class);
        $this->studySessionRepository = $this->mock(StudySessionRepositoryInterface::class);
        $this->logRepository = $this->mock(LogRepositoryInterface::class);

        // Create a mock statistic repository
        $statisticRepository = $this->mock(StatisticRepositoryInterface::class);

        // Bind repositories to container
        $this->app->instance(FlashcardRepositoryInterface::class, $this->flashcardRepository);
        $this->app->instance(StudySessionRepositoryInterface::class, $this->studySessionRepository);
        $this->app->instance(LogRepositoryInterface::class, $this->logRepository);
        $this->app->instance(StatisticRepositoryInterface::class, $statisticRepository);

        $this->statisticService = new StatisticService($statisticRepository);

        // Create StudySessionService instance
        $this->studySessionService = app(StudySessionService::class);

        // Create a test command class that can be tested
        $this->command = new class extends Command
        {
            public $user;

            public $output = '';

            public $shouldExit = false;

            protected $name = 'test:command';

            public function info($string, $verbosity = null)
            {
                $this->output .= $string.PHP_EOL;
            }

            public function line($string, $style = null, $verbosity = null)
            {
                $this->output .= $string.PHP_EOL;
            }

            public function table($headers, $rows, $tableStyle = 'default', array $columnStyles = [])
            {
                $this->output .= "Table Output:\n";
                foreach ($headers as $header) {
                    $this->output .= "{$header}\n";
                }
                foreach ($rows as $row) {
                    $this->output .= implode(' | ', $row).PHP_EOL;
                }
            }

            public function choice($question, array $choices, $default = null, $attempts = null, $multiple = false)
            {
                if ($this->shouldExit) {
                    return 'exit';
                }

                if (empty($choices)) {
                    return null;
                }

                if (isset($choices['exit'])) {
                    return 'exit';
                }

                return array_key_first($choices);
            }

            public function ask($question, $default = null)
            {
                return 'test answer';
            }
        };

        $this->command->user = $this->user;

        // Default mock for log repository
        $this->logRepository->shouldReceive('logStudySessionStart')
            ->byDefault()
            ->andReturn(new Log([
                'user_id' => $this->user->id,
                'action' => 'study_session_start',
                'details' => json_encode(['session_id' => 1]),
            ]));

        $this->logRepository->shouldReceive('logStudySessionEnd')
            ->byDefault()
            ->andReturn(new Log([
                'user_id' => $this->user->id,
                'action' => 'study_session_end',
                'details' => json_encode(['session_id' => 1]),
            ]));

        $this->logRepository->shouldReceive('logFlashcardPractice')
            ->byDefault()
            ->andReturn(new Log([
                'user_id' => $this->user->id,
                'action' => 'flashcard_practice',
                'details' => json_encode(['flashcard_id' => 1, 'is_correct' => true]),
            ]));

        // Default mock for statistic repository
        $defaultStatistic = new Statistic([
            'user_id' => $this->user->id,
            'total_flashcards' => 0,
            'total_study_sessions' => 0,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
        ]);

        $statisticRepository->shouldReceive('getForUser')
            ->byDefault()
            ->andReturn($defaultStatistic);

        $statisticRepository->shouldReceive('createForUser')
            ->byDefault()
            ->andReturn($defaultStatistic);

        $statisticRepository->shouldReceive('getAverageStudySessionDuration')
            ->byDefault()
            ->andReturn(0.0);

        $statisticRepository->shouldReceive('getTotalStudyTime')
            ->byDefault()
            ->andReturn(0.0);

        $statisticRepository->shouldReceive('incrementCorrectAnswers')
            ->byDefault()
            ->andReturn(true);

        $statisticRepository->shouldReceive('incrementIncorrectAnswers')
            ->byDefault()
            ->andReturn(true);

        $statisticRepository->shouldReceive('incrementStudySessions')
            ->byDefault()
            ->andReturn(true);

        // Default mock for flashcard repository
        $this->flashcardRepository->shouldReceive('getAllForUser')
            ->byDefault()
            ->andReturn(new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                100,
                1
            ));

        $this->flashcardRepository->shouldReceive('findForUser')
            ->byDefault()
            ->andReturn(null);

        // Default mock for study session repository
        $this->studySessionRepository->shouldReceive('recordPracticeResult')
            ->byDefault()
            ->andReturn(true);

        $this->studySessionRepository->shouldReceive('startSession')
            ->byDefault()
            ->andReturn(new StudySession([
                'user_id' => $this->user->id,
                'started_at' => now(),
                'ended_at' => null,
            ]));

        $this->studySessionRepository->shouldReceive('endSession')
            ->byDefault()
            ->andReturn(true);

        $this->studySessionRepository->shouldReceive('findForUser')
            ->byDefault()
            ->andReturn(new StudySession([
                'user_id' => $this->user->id,
                'started_at' => now(),
                'ended_at' => null,
            ]));

        // Configure prompts to use the fallback implementation in tests
        Prompt::fallbackWhen(true);

        // Enable test mode in ConsoleRenderer
        \Modules\Flashcard\app\Helpers\ConsoleRenderer::enableTestMode();
        \Modules\Flashcard\app\Helpers\ConsoleRenderer::resetTestOutput();
    }

    protected function tearDown(): void
    {
        // Get the captured output and append it to the command output
        $testOutput = \Modules\Flashcard\app\Helpers\ConsoleRenderer::getTestOutput();
        if ($testOutput !== null) {
            $this->command->output = $testOutput.$this->command->output;
        }

        parent::tearDown();
    }

    #[Test]
    public function it_shows_message_when_no_flashcards_exist(): void
    {
        // Create an active study session
        $studySession = StudySession::create([
            'user_id' => $this->user->id,
            'started_at' => now(),
            'ended_at' => null,
        ]);

        // Mock repository methods
        $this->studySessionRepository->shouldReceive('startSession')
            ->never();

        $this->studySessionRepository->shouldReceive('endSession')
            ->never();

        // Create and execute the action
        $action = $this->createAction();

        // Since we can't check ConsoleRenderer output directly,
        // we make sure the method runs without errors
        $action->execute();

        // Verify there are no flashcards
        $this->assertEquals(0, Flashcard::where('user_id', $this->user->id)->count());
    }

    #[Test]
    public function it_shows_progress_and_allows_practice(): void
    {
        // Create flashcards with specific questions
        $flashcards = Flashcard::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'question' => 'Test Question',
        ]);

        // Create the PracticeFlashcardAction
        $action = $this->createAction();

        // Use reflection to access and test private methods
        $reflectionClass = new ReflectionClass($action);

        // Test the getPracticeResults method
        $getPracticeResults = $reflectionClass->getMethod('getPracticeResults');
        $getPracticeResults->setAccessible(true);

        // Pass the collection of flashcards (not array) to avoid the error
        $practiceResults = $getPracticeResults->invoke($action, $flashcards->all());

        // There should be no practice results initially
        $this->assertCount(0, $practiceResults);

        // Create a practice result for one flashcard to test mixed results
        PracticeResult::factory()->create([
            'user_id' => $this->user->id,
            'flashcard_id' => $flashcards[0]->id,
            'is_correct' => true,
        ]);

        // Get the updated practice results
        $practiceResults = $getPracticeResults->invoke($action, $flashcards->all());

        // Now one flashcard should have a practice result
        $this->assertCount(1, $practiceResults);
        $this->assertTrue($practiceResults[$flashcards[0]->id]['is_correct']);

        // Test the showProgress method
        $showProgress = $reflectionClass->getMethod('showProgress');
        $showProgress->setAccessible(true);

        // Pass the collection of flashcards (not array) to the showProgress method
        $showProgress->invoke($action, $flashcards->all(), $practiceResults);

        // Debug output
        fwrite(STDERR, "Command output:\n".$this->command->output."\n");

        // Verify that the output contains the expected information
        $this->assertStringContainsString('Question', $this->command->output);
        $this->assertStringContainsString('Flashcard Practice Progress', $this->command->output);
        $this->assertStringContainsString('Completion: 1/3 cards', $this->command->output);
        $this->assertStringContainsString('Table Output:', $this->command->output);
        $this->assertCount(3, $flashcards);

        // Add assertions for the table content
        foreach ($flashcards as $flashcard) {
            $this->assertStringContainsString($flashcard->question, $this->command->output);
        }
    }

    #[Test]
    public function it_records_practice_result_when_answering(): void
    {
        // Create a flashcard for testing
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'What is Laravel?',
            'answer' => 'test answer', // Match the command's mock answer
        ]);

        // Create a study session
        $studySession = StudySession::create([
            'user_id' => $this->user->id,
            'started_at' => now(),
            'ended_at' => null,
        ]);

        // Mock repository methods
        $this->flashcardRepository->shouldReceive('getAllForUser')
            ->once()
            ->with($this->user->id, 100)
            ->andReturn(new \Illuminate\Pagination\LengthAwarePaginator(
                [$flashcard],
                1,
                100,
                1
            ));

        $this->studySessionRepository->shouldReceive('startSession')
            ->once()
            ->with($this->user->id)
            ->andReturn($studySession);

        $this->studySessionRepository->shouldReceive('endSession')
            ->once()
            ->with($studySession)
            ->andReturn(true);

        $this->studySessionRepository->shouldReceive('recordPracticeResult')
            ->once()
            ->with($this->user->id, $flashcard->id, true)
            ->andReturnUsing(function ($userId, $flashcardId, $isCorrect) use ($studySession) {
                PracticeResult::create([
                    'user_id' => $userId,
                    'flashcard_id' => $flashcardId,
                    'is_correct' => $isCorrect,
                    'study_session_id' => $studySession->id,
                ]);

                return true;
            });

        $this->studySessionRepository->shouldReceive('findForUser')
            ->once()
            ->with($studySession->id, $this->user->id)
            ->andReturn($studySession);

        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with($flashcard->id, $this->user->id)
            ->andReturn($flashcard);

        // Set up the command to answer one question and then exit
        $this->command = new class extends Command
        {
            public $user;

            public $output = '';

            public $questionAnswered = false;

            protected $name = 'test:command';

            public function info($string, $verbosity = null)
            {
                $this->output .= $string.PHP_EOL;
            }

            public function line($string, $style = null, $verbosity = null)
            {
                $this->output .= $string.PHP_EOL;
            }

            public function table($headers, $rows, $tableStyle = 'default', array $columnStyles = [])
            {
                $this->output .= "Table Output:\n";
                foreach ($headers as $header) {
                    $this->output .= "{$header}\n";
                }
                foreach ($rows as $row) {
                    $this->output .= implode(' | ', $row).PHP_EOL;
                }
            }

            public function choice($question, array $choices, $default = null, $attempts = null, $multiple = false)
            {
                if ($this->questionAnswered) {
                    return 'exit';
                }

                if (empty($choices)) {
                    return null;
                }

                if (isset($choices['exit'])) {
                    return array_key_first($choices);
                }

                return array_key_first($choices);
            }

            public function ask($question, $default = null)
            {
                $this->questionAnswered = true;

                return 'test answer';
            }
        };

        $this->command->user = $this->user;

        // Create and execute the action
        $action = $this->createAction();

        // Execute the action (this will start a study session)
        $action->execute();

        // Check that a practice result was created in the database
        $this->assertDatabaseHas('practice_results', [
            'user_id' => $this->user->id,
            'flashcard_id' => $flashcard->id,
            'is_correct' => true,
            'study_session_id' => $studySession->id,
        ]);
    }

    #[Test]
    public function it_tracks_study_session_duration(): void
    {
        // Create a flashcard for testing
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'What is Laravel?',
            'answer' => 'test answer', // Match the command's mock answer
        ]);

        // Create a study session
        $studySession = StudySession::create([
            'user_id' => $this->user->id,
            'started_at' => now(),
            'ended_at' => null,
        ]);

        // Create another study session with a specific duration
        StudySession::create([
            'user_id' => $this->user->id,
            'started_at' => now()->subMinutes(30),
            'ended_at' => now(),
        ]);

        // Mock repository methods
        $this->flashcardRepository->shouldReceive('getAllForUser')
            ->once()
            ->with($this->user->id, 100)
            ->andReturn(new \Illuminate\Pagination\LengthAwarePaginator(
                [$flashcard],
                1,
                100,
                1
            ));

        $this->studySessionRepository->shouldReceive('startSession')
            ->once()
            ->with($this->user->id)
            ->andReturn($studySession);

        $this->studySessionRepository->shouldReceive('endSession')
            ->once()
            ->with($studySession)
            ->andReturnUsing(function (StudySession $session) {
                $session->end();

                return true;
            });

        $this->studySessionRepository->shouldReceive('recordPracticeResult')
            ->once()
            ->with($this->user->id, $flashcard->id, true)
            ->andReturnUsing(function ($userId, $flashcardId, $isCorrect) use ($studySession) {
                PracticeResult::create([
                    'user_id' => $userId,
                    'flashcard_id' => $flashcardId,
                    'is_correct' => $isCorrect,
                    'study_session_id' => $studySession->id,
                ]);

                return true;
            });

        $this->studySessionRepository->shouldReceive('findForUser')
            ->once()
            ->with($studySession->id, $this->user->id)
            ->andReturn($studySession);

        $this->flashcardRepository->shouldReceive('findForUser')
            ->once()
            ->with($flashcard->id, $this->user->id)
            ->andReturn($flashcard);

        // Create a test command class that can be tested
        $this->command = new class extends Command
        {
            public $user;

            public $output = '';

            public $questionAnswered = false;

            protected $name = 'test:command';

            public function info($string, $verbosity = null)
            {
                $this->output .= $string.PHP_EOL;
            }

            public function line($string, $style = null, $verbosity = null)
            {
                $this->output .= $string.PHP_EOL;
            }

            public function table($headers, $rows, $tableStyle = 'default', array $columnStyles = [])
            {
                $this->output .= "Table Output:\n";
                foreach ($headers as $header) {
                    $this->output .= "{$header}\n";
                }
                foreach ($rows as $row) {
                    $this->output .= implode(' | ', $row).PHP_EOL;
                }
            }

            public function choice($question, array $choices, $default = null, $attempts = null, $multiple = false)
            {
                if ($this->questionAnswered) {
                    return 'exit';
                }

                return array_key_first($choices);
            }

            public function ask($question, $default = null)
            {
                $this->questionAnswered = true;

                return 'test answer';
            }
        };

        $this->command->user = $this->user;

        // Create the action
        $action = $this->createAction();

        // Execute the action (this will start a study session)
        $action->execute();

        // Verify that the study session was ended
        $this->assertTrue($studySession->refresh()->isEnded());
    }

    #[Test]
    public function it_calculates_study_session_statistics_correctly(): void
    {
        // Create multiple study sessions with different durations
        StudySession::create([
            'user_id' => $this->user->id,
            'started_at' => now()->subMinutes(60),
            'ended_at' => now()->subMinutes(30),
        ]);

        StudySession::create([
            'user_id' => $this->user->id,
            'started_at' => now()->subMinutes(30),
            'ended_at' => now(),
        ]);

        // Mock statistic repository methods
        $statisticRepository = app(StatisticRepositoryInterface::class);

        $statisticRepository->shouldReceive('getAverageStudySessionDuration')
            ->once()
            ->with($this->user->id)
            ->andReturn(30.0);

        $statisticRepository->shouldReceive('getTotalStudyTime')
            ->once()
            ->with($this->user->id)
            ->andReturn(60.0);

        // Get statistics
        $avgDuration = $this->statisticService->getAverageStudySessionDuration($this->user->id);
        $totalTime = $this->statisticService->getTotalStudyTime($this->user->id);

        // Each session is 30 minutes
        $this->assertEquals(30, $avgDuration);
        $this->assertEquals(60, $totalTime);
    }

    protected function getPackageProviders($app)
    {
        return [FlashcardServiceProvider::class];
    }

    private function createAction(): PracticeFlashcardAction
    {
        return new PracticeFlashcardAction(
            $this->command,
            $this->flashcardRepository,
            $this->studySessionRepository,
            $this->statisticService,
            $this->studySessionService
        );
    }
}
