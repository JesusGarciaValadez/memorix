<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands\Actions;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Console\Commands\Actions\ResetFlashcardAction;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Repositories\PracticeResultRepositoryInterface;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ResetFlashcardActionTest extends TestCase
{
    use RefreshDatabase;

    private Command $command;

    private StatisticRepositoryInterface $statisticRepository;

    private PracticeResultRepositoryInterface $practiceResultRepository;

    private LogRepositoryInterface $logRepository;

    private StudySessionRepositoryInterface $studySessionRepository;

    private ConsoleRendererInterface $renderer;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();

        // Create mock command
        $this->command = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Add user property to the command mock
        $this->command->user = $this->user;

        // Mock the repositories
        $this->statisticRepository = $this->createMock(StatisticRepositoryInterface::class);
        $this->practiceResultRepository = $this->createMock(PracticeResultRepositoryInterface::class);
        $this->logRepository = $this->createMock(LogRepositoryInterface::class);
        $this->studySessionRepository = $this->createMock(StudySessionRepositoryInterface::class);
        $this->renderer = $this->createMock(ConsoleRendererInterface::class);
    }

    #[Test]
    public function it_resets_practice_data_successfully(): void
    {
        // Arrange
        $this->command->expects($this->exactly(2))
            ->method('info')
            ->willReturnCallback(function ($message) {
                static $calls = 0;
                $expectedMessages = [
                    'Resetting flashcard data...',
                    'All practice data has been reset successfully.',
                ];
                $this->assertEquals($expectedMessages[$calls], $message);
                $calls++;
            });

        $this->statisticRepository->expects($this->once())
            ->method('resetPracticeStats')
            ->with($this->user->id)
            ->willReturn(true);

        $this->practiceResultRepository->expects($this->once())
            ->method('deleteForUser')
            ->with($this->user->id)
            ->willReturn(true);

        $this->studySessionRepository->expects($this->once())
            ->method('deleteAllForUser')
            ->with($this->user->id)
            ->willReturn(true);

        $log = new Log();
        $this->logRepository->expects($this->once())
            ->method('logPracticeReset')
            ->with($this->user->id)
            ->willReturn($log);

        $action = new ResetFlashcardAction(
            $this->command,
            $this->statisticRepository,
            $this->practiceResultRepository,
            $this->studySessionRepository,
            $this->logRepository,
            $this->renderer
        );

        // Act
        $action->execute();
    }

    #[Test]
    public function it_handles_reset_failure(): void
    {
        // Arrange
        $this->command->expects($this->once())
            ->method('info')
            ->with('Resetting flashcard data...');

        $this->statisticRepository->expects($this->once())
            ->method('resetPracticeStats')
            ->with($this->user->id)
            ->willReturn(false);

        $this->practiceResultRepository->expects($this->once())
            ->method('deleteForUser')
            ->with($this->user->id)
            ->willReturn(false);

        $this->studySessionRepository->expects($this->once())
            ->method('deleteAllForUser')
            ->with($this->user->id)
            ->willReturn(false);

        $log = new Log();
        $this->logRepository->expects($this->once())
            ->method('logPracticeReset')
            ->with($this->user->id)
            ->willReturn($log);

        $action = new ResetFlashcardAction(
            $this->command,
            $this->statisticRepository,
            $this->practiceResultRepository,
            $this->studySessionRepository,
            $this->logRepository,
            $this->renderer
        );

        // Act
        $action->execute();
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        // Arrange
        $this->command->user = null;

        $this->command->expects($this->once())
            ->method('info')
            ->with('Resetting flashcard data...');

        $this->statisticRepository->expects($this->never())
            ->method('resetPracticeStats');

        $this->practiceResultRepository->expects($this->never())
            ->method('deleteForUser');

        $this->studySessionRepository->expects($this->never())
            ->method('deleteAllForUser');

        $this->logRepository->expects($this->never())
            ->method('logPracticeReset');

        $action = new ResetFlashcardAction(
            $this->command,
            $this->statisticRepository,
            $this->practiceResultRepository,
            $this->studySessionRepository,
            $this->logRepository,
            $this->renderer
        );

        // Act
        $action->execute();
    }
}
