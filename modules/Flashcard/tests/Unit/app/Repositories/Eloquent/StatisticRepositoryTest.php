<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Repositories\Eloquent;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Repositories\Eloquent\StatisticRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StatisticRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private StatisticRepository $repository;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new StatisticRepository();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_gets_statistics_for_user(): void
    {
        // Arrange - Create statistics using the repository
        $statistic = Statistic::create([
            'user_id' => $this->user->id,
            'total_flashcards' => 10,
            'total_study_sessions' => 5,
            'total_correct_answers' => 15,
            'total_incorrect_answers' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Act
        $result = $this->repository->getForUser($this->user->id);

        // Assert
        $this->assertInstanceOf(Statistic::class, $result);
        $this->assertEquals(10, $result->total_flashcards);
        $this->assertEquals(5, $result->total_study_sessions);
        $this->assertEquals(15, $result->total_correct_answers);
        $this->assertEquals(5, $result->total_incorrect_answers);
    }

    #[Test]
    public function it_returns_null_when_statistics_not_found_for_user(): void
    {
        // Act
        $result = $this->repository->getForUser($this->user->id);

        // Assert
        $this->assertNull($result);
    }

    #[Test]
    public function it_creates_statistics_for_user(): void
    {
        // Act
        $result = $this->repository->createForUser($this->user->id);

        // Assert
        $this->assertInstanceOf(Statistic::class, $result);
        $this->assertEquals($this->user->id, $result->user_id);
        $this->assertEquals(0, $result->total_flashcards);
        $this->assertEquals(0, $result->total_study_sessions);
        $this->assertEquals(0, $result->total_correct_answers);
        $this->assertEquals(0, $result->total_incorrect_answers);
    }

    #[Test]
    public function it_increments_flashcards_created(): void
    {
        // Arrange
        DB::table('statistics')->insert([
            'user_id' => $this->user->id,
            'total_flashcards' => 5,
            'total_study_sessions' => 0,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Act
        $result = $this->repository->incrementFlashcardsCreated($this->user->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('statistics', [
            'user_id' => $this->user->id,
            'total_flashcards' => 6,
        ]);
    }

    #[Test]
    public function it_creates_statistics_when_incrementing_flashcards_created_if_not_exist(): void
    {
        // Act
        $result = $this->repository->incrementFlashcardsCreated($this->user->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('statistics', [
            'user_id' => $this->user->id,
            'total_flashcards' => 1,
        ]);
    }

    #[Test]
    public function it_increments_flashcards_deleted(): void
    {
        // Arrange
        DB::table('statistics')->insert([
            'user_id' => $this->user->id,
            'total_flashcards' => 5,
            'total_study_sessions' => 0,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
            'created_at' => now(),
            'updated_at' => now()->subHour(),
        ]);

        // Act
        $result = $this->repository->incrementFlashcardsDeleted($this->user->id);

        // Assert
        $this->assertTrue($result);
        // Check that updated_at was updated
        $statistic = Statistic::where('user_id', $this->user->id)->first();
        $this->assertNotNull($statistic);
        $this->assertGreaterThan(now()->subMinutes(5), $statistic->updated_at);
    }

    #[Test]
    public function it_increments_study_sessions(): void
    {
        // Arrange
        DB::table('statistics')->insert([
            'user_id' => $this->user->id,
            'total_flashcards' => 0,
            'total_study_sessions' => 7,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Act
        $result = $this->repository->incrementStudySessions($this->user->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('statistics', [
            'user_id' => $this->user->id,
            'total_study_sessions' => 8,
        ]);
    }

    #[Test]
    public function it_increments_correct_answers(): void
    {
        // Arrange
        DB::table('statistics')->insert([
            'user_id' => $this->user->id,
            'total_flashcards' => 0,
            'total_study_sessions' => 0,
            'total_correct_answers' => 12,
            'total_incorrect_answers' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Act
        $result = $this->repository->incrementCorrectAnswers($this->user->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('statistics', [
            'user_id' => $this->user->id,
            'total_correct_answers' => 13,
        ]);
    }

    #[Test]
    public function it_increments_incorrect_answers(): void
    {
        // Arrange
        DB::table('statistics')->insert([
            'user_id' => $this->user->id,
            'total_flashcards' => 0,
            'total_study_sessions' => 0,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Act
        $result = $this->repository->incrementIncorrectAnswers($this->user->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('statistics', [
            'user_id' => $this->user->id,
            'total_incorrect_answers' => 5,
        ]);
    }

    #[Test]
    public function it_resets_practice_stats(): void
    {
        // Arrange
        DB::table('statistics')->insert([
            'user_id' => $this->user->id,
            'total_flashcards' => 0,
            'total_study_sessions' => 0,
            'total_correct_answers' => 10,
            'total_incorrect_answers' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Act
        $result = $this->repository->resetPracticeStats($this->user->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('statistics', [
            'user_id' => $this->user->id,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
        ]);
    }

    #[Test]
    public function it_gets_average_study_session_duration(): void
    {
        // Arrange
        $now = now();
        StudySession::factory()->create([
            'user_id' => $this->user->id,
            'started_at' => $now->copy()->subMinutes(30),
            'ended_at' => $now,
        ]);

        StudySession::factory()->create([
            'user_id' => $this->user->id,
            'started_at' => $now->copy()->subMinutes(60),
            'ended_at' => $now,
        ]);

        // Act
        $result = $this->repository->getAverageStudySessionDuration($this->user->id);

        // Assert
        $this->assertEquals(45.0, $result);
    }

    #[Test]
    public function it_returns_zero_for_average_duration_when_no_completed_sessions(): void
    {
        // Act
        $result = $this->repository->getAverageStudySessionDuration($this->user->id);

        // Assert
        $this->assertEquals(0.0, $result);
    }

    #[Test]
    public function it_gets_total_study_time(): void
    {
        // Arrange
        $now = now();
        StudySession::factory()->create([
            'user_id' => $this->user->id,
            'started_at' => $now->copy()->subMinutes(30),
            'ended_at' => $now,
        ]);

        StudySession::factory()->create([
            'user_id' => $this->user->id,
            'started_at' => $now->copy()->subMinutes(60),
            'ended_at' => $now,
        ]);

        // Act
        $result = $this->repository->getTotalStudyTime($this->user->id);

        // Assert
        $this->assertEquals(90.0, $result);
    }

    #[Test]
    public function it_returns_zero_for_total_study_time_when_no_completed_sessions(): void
    {
        // Act
        $result = $this->repository->getTotalStudyTime($this->user->id);

        // Assert
        $this->assertEquals(0.0, $result);
    }
}
