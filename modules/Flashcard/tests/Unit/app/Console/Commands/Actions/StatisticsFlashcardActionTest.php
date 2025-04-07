<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands\Actions;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Console\Commands\Actions\StatisticsFlashcardAction;
use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;
use Modules\Flashcard\app\Services\StatisticService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StatisticsFlashcardActionTest extends TestCase
{
    use RefreshDatabase;

    private Command $command;

    private StatisticRepositoryInterface $statisticRepository;

    private User $user;

    private Statistic $statistic;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a real user for testing
        $this->user = User::factory()->create();

        // Create a mock command
        $this->command = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Add user property to the command mock
        $this->command->user = $this->user;

        // Mock the statistic repository interface
        $this->statisticRepository = $this->createMock(StatisticRepositoryInterface::class);

        // Create a statistic model for our tests
        $this->statistic = new Statistic([
            'user_id' => $this->user->id,
            'total_flashcards' => 10,
            'total_study_sessions' => 5,
            'total_correct_answers' => 20,
            'total_incorrect_answers' => 5,
        ]);
    }

    #[Test]
    public function it_displays_statistics_for_user(): void
    {
        // Setup repository mock to return our statistic
        $this->statisticRepository->method('getForUser')
            ->with($this->user->id)
            ->willReturn($this->statistic);

        $this->statisticRepository->method('getAverageStudySessionDuration')
            ->with($this->user->id)
            ->willReturn(15.5);

        $this->statisticRepository->method('getTotalStudyTime')
            ->with($this->user->id)
            ->willReturn(77.5);

        // Create a real service with our mocked repository
        $statisticService = new StatisticService($this->statisticRepository);

        // Setup command expectations
        $this->command->expects($this->once())
            ->method('info')
            ->with('Showing statistics...');

        // Create action with our dependencies
        $action = new StatisticsFlashcardAction(
            $this->command,
            $statisticService
        );

        // Execute the action
        $action->execute();
    }

    #[Test]
    public function it_handles_empty_statistics(): void
    {
        // First return null, then return the empty statistics we create
        $this->statisticRepository->method('getForUser')
            ->with($this->user->id)
            ->willReturn(null);

        // When createForUser is called, return an empty statistic
        $emptyStatistic = new Statistic([
            'user_id' => $this->user->id,
            'total_flashcards' => 0,
            'total_study_sessions' => 0,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
        ]);

        $this->statisticRepository->method('createForUser')
            ->with($this->user->id)
            ->willReturn($emptyStatistic);

        $this->statisticRepository->method('getAverageStudySessionDuration')
            ->with($this->user->id)
            ->willReturn(0.0);

        $this->statisticRepository->method('getTotalStudyTime')
            ->with($this->user->id)
            ->willReturn(0.0);

        // Create a real service with our mocked repository
        $statisticService = new StatisticService($this->statisticRepository);

        // Setup command expectations
        $this->command->expects($this->once())
            ->method('info')
            ->with('Showing statistics...');

        // Create action with our dependencies
        $action = new StatisticsFlashcardAction(
            $this->command,
            $statisticService
        );

        // Execute the action
        $action->execute();
    }
}
