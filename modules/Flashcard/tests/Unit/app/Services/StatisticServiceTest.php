<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Services;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;
use Modules\Flashcard\app\Services\StatisticService;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

final class StatisticServiceTest extends TestCase
{
    use RefreshDatabase;

    private StatisticService $service;

    private StatisticRepositoryInterface|MockObject $statisticRepository;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->statisticRepository = $this->createMock(StatisticRepositoryInterface::class);
        $this->service = new StatisticService($this->statisticRepository);
    }

    #[Test]
    public function it_gets_statistics_for_user_with_existing_statistics(): void
    {
        // Arrange
        $userId = $this->user->id;
        $statistic = new Statistic();
        $statistic->total_flashcards = 10;
        $statistic->total_study_sessions = 5;
        $statistic->total_correct_answers = 30;
        $statistic->total_incorrect_answers = 10;

        $this->statisticRepository->expects($this->once())
            ->method('getForUser')
            ->with($userId)
            ->willReturn($statistic);

        // Act
        $result = $this->service->getStatisticsForUser($userId);

        // Assert
        $this->assertEquals([
            'flashcards_created' => 10,
            'flashcards_deleted' => 0,
            'study_sessions' => 5,
            'correct_answers' => 30,
            'incorrect_answers' => 10,
        ], $result);
    }

    #[Test]
    public function it_gets_statistics_for_user_with_no_existing_statistics(): void
    {
        // Arrange
        $userId = $this->user->id;
        $newStatistic = new Statistic();
        $newStatistic->total_flashcards = 0;
        $newStatistic->total_study_sessions = 0;
        $newStatistic->total_correct_answers = 0;
        $newStatistic->total_incorrect_answers = 0;

        $this->statisticRepository->expects($this->once())
            ->method('getForUser')
            ->with($userId)
            ->willReturn(null);

        $this->statisticRepository->expects($this->once())
            ->method('createForUser')
            ->with($userId)
            ->willReturn($newStatistic);

        // Act
        $result = $this->service->getStatisticsForUser($userId);

        // Assert
        $this->assertEquals([
            'flashcards_created' => 0,
            'flashcards_deleted' => 0,
            'study_sessions' => 0,
            'correct_answers' => 0,
            'incorrect_answers' => 0,
        ], $result);
    }

    #[Test]
    public function it_gets_practice_success_rate_with_existing_statistics(): void
    {
        // Arrange
        $userId = $this->user->id;
        $statistic = new Statistic();
        $statistic->total_correct_answers = 75;
        $statistic->total_incorrect_answers = 25;

        $this->statisticRepository->expects($this->once())
            ->method('getForUser')
            ->with($userId)
            ->willReturn($statistic);

        // Act
        $result = $this->service->getPracticeSuccessRate($userId);

        // Assert
        $this->assertEquals(75.0, $result);
    }

    #[Test]
    public function it_gets_practice_success_rate_with_no_existing_statistics(): void
    {
        // Arrange
        $userId = $this->user->id;

        $this->statisticRepository->expects($this->once())
            ->method('getForUser')
            ->with($userId)
            ->willReturn(null);

        // Act
        $result = $this->service->getPracticeSuccessRate($userId);

        // Assert
        $this->assertEquals(0.0, $result);
    }

    #[Test]
    public function it_gets_practice_success_rate_with_no_answers(): void
    {
        // Arrange
        $userId = $this->user->id;
        $statistic = new Statistic();
        $statistic->total_correct_answers = 0;
        $statistic->total_incorrect_answers = 0;

        $this->statisticRepository->expects($this->once())
            ->method('getForUser')
            ->with($userId)
            ->willReturn($statistic);

        // Act
        $result = $this->service->getPracticeSuccessRate($userId);

        // Assert
        $this->assertEquals(0.0, $result);
    }

    #[Test]
    public function it_gets_average_study_session_duration(): void
    {
        // Arrange
        $userId = $this->user->id;
        $expectedDuration = 25.5;

        $this->statisticRepository->expects($this->once())
            ->method('getAverageStudySessionDuration')
            ->with($userId)
            ->willReturn($expectedDuration);

        // Act
        $result = $this->service->getAverageStudySessionDuration($userId);

        // Assert
        $this->assertEquals($expectedDuration, $result);
    }

    #[Test]
    public function it_gets_total_study_time(): void
    {
        // Arrange
        $userId = $this->user->id;
        $expectedTime = 120.75;

        $this->statisticRepository->expects($this->once())
            ->method('getTotalStudyTime')
            ->with($userId)
            ->willReturn($expectedTime);

        // Act
        $result = $this->service->getTotalStudyTime($userId);

        // Assert
        $this->assertEquals($expectedTime, $result);
    }
}
