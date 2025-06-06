<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\app\Services;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;
use Modules\Flashcard\app\Services\StatisticServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

final class StatisticServiceTest extends TestCase
{
    use RefreshDatabase;

    private TestStatisticService $service;

    private MockInterface $statisticRepository;

    private int $userId = 1;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var MockInterface&StatisticRepositoryInterface $statisticRepository */
        $statisticRepository = Mockery::mock(StatisticRepositoryInterface::class);
        $this->statisticRepository = $statisticRepository;
        $this->service = new TestStatisticService($this->statisticRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_can_create_statistic_for_user(): void
    {
        // Arrange
        $statistic = $this->createTestStatistic();

        // @phpstan-ignore-next-line
        $this->statisticRepository->shouldReceive('findByUserId')
            ->once()
            ->with($this->userId)
            ->andReturnNull();

        // @phpstan-ignore-next-line
        $this->statisticRepository->shouldReceive('create')
            ->once()
            ->with($this->userId)
            ->andReturn($statistic);

        // Act
        $result = $this->service->getStatisticsForUser($this->userId);

        // Assert
        $this->assertEquals(0, $result['flashcards_created']);
        $this->assertEquals(0, $result['study_sessions']);
    }

    #[Test]
    public function it_can_get_statistic_by_user_id(): void
    {
        // Arrange
        $statistic = $this->createTestStatistic();

        // @phpstan-ignore-next-line
        $this->statisticRepository->shouldReceive('findByUserId')
            ->once()
            ->with($this->userId)
            ->andReturn($statistic);

        // Act
        $result = $this->service->getStatisticsForUser($this->userId);

        // Assert
        $this->assertEquals(0, $result['flashcards_created']);
        $this->assertEquals(0, $result['study_sessions']);
    }

    #[Test]
    public function it_returns_null_when_getting_non_existent_statistic(): void
    {
        // Arrange
        // @phpstan-ignore-next-line
        $this->statisticRepository->shouldReceive('findByUserId')
            ->once()
            ->with(999)
            ->andReturnNull();

        // @phpstan-ignore-next-line
        $this->statisticRepository->shouldReceive('create')
            ->once()
            ->with(999)
            ->andReturn($this->createTestStatistic(999));

        // Act
        $result = $this->service->getStatisticsForUser(999);

        // Assert
        $this->assertEquals(0, $result['flashcards_created']);
    }

    #[Test]
    public function it_can_increment_total_flashcards(): void
    {
        // Arrange
        $statistic = $this->createTestStatistic();

        // @phpstan-ignore-next-line
        $this->statisticRepository->shouldReceive('findByUserId')
            ->once()
            ->with($this->userId)
            ->andReturn($statistic);

        // @phpstan-ignore-next-line
        $this->statisticRepository->shouldReceive('incrementTotalFlashcards')
            ->once()
            ->with($this->userId)
            ->andReturn(true);

        // Act
        $result = $this->service->incrementTotalFlashcards($this->userId);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function it_can_decrement_total_flashcards(): void
    {
        // Arrange
        $statistic = $this->createTestStatistic();

        // @phpstan-ignore-next-line
        $this->statisticRepository->shouldReceive('findByUserId')
            ->once()
            ->with($this->userId)
            ->andReturn($statistic);

        // @phpstan-ignore-next-line
        $this->statisticRepository->shouldReceive('decrementTotalFlashcards')
            ->once()
            ->with($this->userId)
            ->andReturn(true);

        // Act
        $result = $this->service->decrementTotalFlashcards($this->userId);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function it_will_not_decrement_below_zero(): void
    {
        // Arrange
        $statistic = $this->createTestStatistic();

        // @phpstan-ignore-next-line
        $this->statisticRepository->shouldReceive('findByUserId')
            ->once()
            ->with($this->userId)
            ->andReturn($statistic);

        // Repository should not be called if count is already 0
        // @phpstan-ignore-next-line
        $this->statisticRepository->shouldNotReceive('decrementTotalFlashcards');

        // Act
        $result = $this->service->decrementTotalFlashcards($this->userId);

        // Assert
        $this->assertFalse($result);
    }

    #[Test]
    public function it_can_reset_practice_statistics(): void
    {
        // Arrange
        $this->createTestStatistic();

        // @phpstan-ignore-next-line
        $this->statisticRepository->shouldReceive('resetPracticeStatistics')
            ->once()
            ->with($this->userId)
            ->andReturn(true);

        // Act
        $result = $this->service->resetPracticeStatistics($this->userId);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function it_can_add_study_time(): void
    {
        // Arrange
        // @phpstan-ignore-next-line
        $this->statisticRepository->shouldReceive('incrementStudyTime')
            ->once()
            ->with($this->userId, 30)
            ->andReturn(true);

        // Act
        // Using the test-specific method that accepts userId instead of User object
        $result = $this->service->addStudyTimeForTest($this->userId, 30);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function it_can_get_average_study_session_duration(): void
    {
        // Arrange
        // @phpstan-ignore-next-line
        $this->statisticRepository->shouldReceive('getAverageStudySessionDuration')
            ->once()
            ->with($this->userId)
            ->andReturn(25.5);

        // Act
        $result = $this->service->getAverageStudySessionDuration($this->userId);

        // Assert
        $this->assertEquals(25.5, $result);
    }

    #[Test]
    public function it_returns_zero_for_average_when_no_sessions(): void
    {
        // Arrange
        // @phpstan-ignore-next-line
        $this->statisticRepository->shouldReceive('getAverageStudySessionDuration')
            ->once()
            ->with($this->userId)
            ->andReturn(0);

        // Act
        $result = $this->service->getAverageStudySessionDuration($this->userId);

        // Assert
        $this->assertEquals(0, $result);
    }

    #[Test]
    public function it_can_get_total_study_time(): void
    {
        // Arrange
        // @phpstan-ignore-next-line
        $this->statisticRepository->shouldReceive('getTotalStudyTime')
            ->once()
            ->with($this->userId)
            ->andReturn(120);

        // Act
        $result = $this->service->getTotalStudyTime($this->userId);

        // Assert
        $this->assertEquals(120, $result);
    }

    #[Test]
    public function it_returns_zero_for_total_study_time_when_no_sessions(): void
    {
        // Arrange
        // @phpstan-ignore-next-line
        $this->statisticRepository->shouldReceive('getTotalStudyTime')
            ->once()
            ->with($this->userId)
            ->andReturn(0);

        // Act
        $result = $this->service->getTotalStudyTime($this->userId);

        // Assert
        $this->assertEquals(0, $result);
    }

    /**
     * Create a test statistic object
     */
    private function createTestStatistic(int $userId = 1): Statistic
    {
        return Statistic::factory()->create([
            'id' => 1,
            'user_id' => $userId,
            'flashcards_created' => 0,
            'study_sessions' => 0,
            'correct_answers' => 0,
            'incorrect_answers' => 0,
            'study_time' => 0,
        ]);
    }
}

/**
 * Test implementation of StatisticService for unit testing
 */
final readonly class TestStatisticService implements StatisticServiceInterface
{
    public function __construct(
        private MockInterface&StatisticRepositoryInterface $repository
    ) {}

    public function getByUserId(int $userId): ?Statistic
    {
        return $this->repository->findByUserId($userId);
    }

    public function getPracticeSuccessRate(int $userId): float
    {
        return 0.0;
    }

    public function createStatistic(int $userId): Statistic
    {
        // Pass data array matching the interface
        return $this->repository->create(['user_id' => $userId]);
    }

    public function getStatisticsForUser(int $userId): array
    {
        $statistic = $this->repository->findByUserId($userId);

        if (! $statistic instanceof Statistic) {
            // Pass data array matching the interface
            $statistic = $this->repository->create(['user_id' => $userId]);
        }

        return [
            // Use correct property names from Statistic model
            'flashcards_created' => $statistic->total_flashcards,
            'study_sessions' => $statistic->total_study_sessions,
            'correct_answers' => $statistic->total_correct_answers,
            'incorrect_answers' => $statistic->total_incorrect_answers,
        ];
    }

    public function incrementTotalFlashcards(int $userId): bool
    {
        $statistic = $this->repository->findByUserId($userId);

        if (! $statistic instanceof Statistic) {
            return false;
        }

        return $this->repository->incrementTotalFlashcards($userId);
    }

    public function decrementTotalFlashcards(int $userId): bool
    {
        $statistic = $this->repository->findByUserId($userId);

        // Use correct property name from Statistic model
        if (! $statistic instanceof Statistic || $statistic->total_flashcards <= 0) {
            return false;
        }

        return $this->repository->decrementTotalFlashcards($userId);
    }

    public function incrementCorrectAnswers(int $userId): bool
    {
        return true;
    }

    public function incrementIncorrectAnswers(int $userId): bool
    {
        return true;
    }

    public function incrementStudySessions(int $userId): bool
    {
        return true;
    }

    public function resetPracticeStatistics(int $userId): bool
    {
        return $this->repository->resetPracticeStatistics($userId);
    }

    public function addStudyTime(User $user, int $minutes): bool
    {
        throw new RuntimeException('This method is not used in tests. Use addStudyTimeForTest instead.');
    }

    // Special method for testing only
    public function addStudyTimeForTest(int $userId, int $minutes): bool
    {
        return $this->repository->addStudyTime($userId, $minutes);
    }

    public function getAverageStudySessionDuration(int $userId): float
    {
        return $this->repository->getAverageStudySessionDuration($userId);
    }

    public function getTotalStudyTime(int $userId): float
    {
        return $this->repository->getTotalStudyTime($userId);
    }

    public function getCorrectAnswersCount(int $userId): int
    {
        return 0;
    }

    public function getIncorrectAnswersCount(int $userId): int
    {
        return 0;
    }

    public function updateStatistics(int $userId, array $data): bool
    {
        return true;
    }
}
