<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands\Actions;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Flashcard\app\Console\Commands\Actions\PracticeFlashcardAction;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\PracticeResult;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;
use Modules\Flashcard\app\Services\StatisticService;
use Modules\Flashcard\app\Services\StudySessionService;
use PHPUnit\Framework\Attributes\Test;

final class PracticeFlashcardActionTest extends BaseTestCase
{
    use RefreshDatabase;

    private Command $command;

    private FlashcardRepositoryInterface $flashcardRepository;

    private StudySessionRepositoryInterface $studySessionRepository;

    private StatisticService $statisticService;

    private StudySessionService $studySessionService;

    private ConsoleRendererInterface $renderer;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->command = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command->user = $this->user;

        $this->flashcardRepository = app(FlashcardRepositoryInterface::class);
        $this->studySessionRepository = app(StudySessionRepositoryInterface::class);
        $this->statisticService = app(StatisticService::class);
        $this->studySessionService = app(StudySessionService::class);
        $this->renderer = $this->createMock(ConsoleRendererInterface::class);
    }

    #[Test]
    public function it_shows_message_when_no_flashcards_exist(): void
    {
        // Arrange
        $paginator = new LengthAwarePaginator([], 0, 100, 1);

        $this->command->expects($this->never())
            ->method('choice');

        $this->command->expects($this->never())
            ->method('ask');

        // Act
        $action = new PracticeFlashcardAction(
            $this->command,
            $this->studySessionRepository,
            $this->statisticService,
            $this->studySessionService,
            $this->renderer
        );

        $action->execute();
    }

    #[Test]
    public function it_shows_progress_and_allows_practice(): void
    {
        // Arrange
        $flashcards = Flashcard::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $this->command->expects($this->once())
            ->method('choice')
            ->willReturn('exit');

        // Act
        $action = new PracticeFlashcardAction(
            $this->command,
            $this->studySessionRepository,
            $this->statisticService,
            $this->studySessionService,
            $this->renderer
        );

        $action->execute();

        // Assert
        $this->assertDatabaseHas('study_sessions', [
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function it_records_practice_result_when_answering(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'Test Question',
            'answer' => 'Test Answer',
        ]);

        $this->command->expects($this->once())
            ->method('choice')
            ->willReturn($flashcard->id);

        $this->command->expects($this->once())
            ->method('ask')
            ->willReturn('Test Answer');

        // Act
        $action = new PracticeFlashcardAction(
            $this->command,
            $this->studySessionRepository,
            $this->statisticService,
            $this->studySessionService,
            $this->renderer
        );

        $action->execute();

        // Assert
        $this->assertDatabaseHas('practice_results', [
            'user_id' => $this->user->id,
            'flashcard_id' => $flashcard->id,
            'is_correct' => true,
        ]);

        // Verify statistics are updated only once
        $this->assertDatabaseHas('statistics', [
            'user_id' => $this->user->id,
            'total_correct_answers' => 1,
            'total_incorrect_answers' => 0,
        ]);
    }

    #[Test]
    public function it_tracks_study_session_duration(): void
    {
        // Arrange
        $flashcard = Flashcard::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->command->expects($this->exactly(2))
            ->method('choice')
            ->willReturnCallback(function ($question, $choices) use ($flashcard) {
                static $callCount = 0;
                $callCount++;

                return $callCount === 1 ? $flashcard->id : 'exit';
            });

        $this->command->expects($this->once())
            ->method('ask')
            ->willReturn('wrong answer');

        // Act
        $action = new PracticeFlashcardAction(
            $this->command,
            $this->studySessionRepository,
            $this->statisticService,
            $this->studySessionService,
            $this->renderer
        );

        $action->execute();

        // Assert
        $studySession = StudySession::where('user_id', $this->user->id)->first();
        $this->assertNotNull($studySession);
        $this->assertNotNull($studySession->ended_at);
    }

    #[Test]
    public function it_calculates_study_session_statistics_correctly(): void
    {
        // Arrange
        $flashcards = Flashcard::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $this->command->expects($this->exactly(4))
            ->method('choice')
            ->willReturnCallback(function ($question, $choices) use ($flashcards) {
                static $callCount = 0;
                $callCount++;
                if ($callCount === 4) {
                    return 'exit';
                }

                return $flashcards[$callCount - 1]->id;
            });

        $this->command->expects($this->exactly(3))
            ->method('ask')
            ->willReturnCallback(function ($question) use ($flashcards) {
                static $callCount = 0;
                $callCount++;
                if ($callCount === 2) {
                    return 'wrong answer';
                }

                return $flashcards[$callCount - 1]->answer;
            });

        // Act
        $action = new PracticeFlashcardAction(
            $this->command,
            $this->studySessionRepository,
            $this->statisticService,
            $this->studySessionService,
            $this->renderer
        );

        $action->execute();

        // Assert
        $this->assertDatabaseHas('statistics', [
            'user_id' => $this->user->id,
            'total_correct_answers' => 2,
            'total_incorrect_answers' => 1,
        ]);

        $practiceResults = PracticeResult::where('user_id', $this->user->id)->get();
        $this->assertCount(3, $practiceResults);
        $this->assertEquals(2, $practiceResults->where('is_correct', true)->count());
        $this->assertEquals(1, $practiceResults->where('is_correct', false)->count());
    }
}
