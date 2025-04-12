<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\Database\Factories;

use App\Models\User;
use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class StatisticFactoryTest extends TestCase
{
    #[Test]
    public function it_can_create_a_statistic(): void
    {
        $statistic = Statistic::factory()->create();

        $this->assertInstanceOf(Statistic::class, $statistic);
        $this->assertDatabaseHas('statistics', ['id' => $statistic->id]);
    }

    #[Test]
    public function it_creates_a_statistic_with_valid_data(): void
    {
        $statistic = Statistic::factory()->create();

        $this->assertNotNull($statistic->user_id);
        $this->assertIsInt($statistic->total_flashcards);
        $this->assertIsInt($statistic->total_study_sessions);
        $this->assertIsInt($statistic->total_correct_answers);
        $this->assertIsInt($statistic->total_incorrect_answers);

        // Default values should be in expected ranges
        $this->assertGreaterThanOrEqual(10, $statistic->total_flashcards);
        $this->assertLessThanOrEqual(100, $statistic->total_flashcards);

        $this->assertGreaterThanOrEqual(1, $statistic->total_study_sessions);
        $this->assertLessThanOrEqual(30, $statistic->total_study_sessions);
    }

    #[Test]
    public function it_creates_a_statistic_with_a_real_user(): void
    {
        $user = User::factory()->create();

        $statistic = Statistic::factory()
            ->for($user)
            ->create();

        $this->assertEquals($user->id, $statistic->user_id);
        $this->assertInstanceOf(User::class, $statistic->user);
    }

    #[Test]
    public function it_can_create_a_statistic_with_high_success_rate(): void
    {
        $statistic = Statistic::factory()
            ->highSuccess()
            ->create();

        $this->assertInstanceOf(Statistic::class, $statistic);
        $this->assertEquals(100, $statistic->total_flashcards);

        // High success rate should be between 80% and 95%
        $successRate = $statistic->getCorrectPercentage();
        $this->assertGreaterThanOrEqual(80, $successRate);
        $this->assertLessThanOrEqual(95, $successRate);

        // Total answers should equal total flashcards
        $totalAnswered = $statistic->total_correct_answers + $statistic->total_incorrect_answers;
        $this->assertEquals($statistic->total_flashcards, $totalAnswered);
    }

    #[Test]
    public function it_can_create_a_statistic_with_low_success_rate(): void
    {
        $statistic = Statistic::factory()
            ->lowSuccess()
            ->create();

        $this->assertInstanceOf(Statistic::class, $statistic);
        $this->assertEquals(100, $statistic->total_flashcards);

        // Low success rate should be between 20% and 40%
        $successRate = $statistic->getCorrectPercentage();
        $this->assertGreaterThanOrEqual(20, $successRate);
        $this->assertLessThanOrEqual(40, $successRate);

        // Total answers should equal total flashcards
        $totalAnswered = $statistic->total_correct_answers + $statistic->total_incorrect_answers;
        $this->assertEquals($statistic->total_flashcards, $totalAnswered);
    }

    #[Test]
    public function it_can_create_a_new_user_statistic(): void
    {
        $statistic = Statistic::factory()
            ->newUser()
            ->create();

        $this->assertInstanceOf(Statistic::class, $statistic);

        // New user values should be low
        $this->assertLessThanOrEqual(5, $statistic->total_flashcards);
        $this->assertLessThanOrEqual(2, $statistic->total_study_sessions);
        $this->assertLessThanOrEqual(3, $statistic->total_correct_answers);
        $this->assertLessThanOrEqual(2, $statistic->total_incorrect_answers);
    }

    #[Test]
    public function it_can_create_a_power_user_statistic(): void
    {
        $statistic = Statistic::factory()
            ->powerUser()
            ->create();

        $this->assertInstanceOf(Statistic::class, $statistic);

        // Power user values should be high
        $this->assertGreaterThanOrEqual(200, $statistic->total_flashcards);
        $this->assertGreaterThanOrEqual(50, $statistic->total_study_sessions);
        $this->assertGreaterThanOrEqual(150, $statistic->total_correct_answers);
        $this->assertGreaterThanOrEqual(50, $statistic->total_incorrect_answers);
    }

    #[Test]
    public function it_can_create_multiple_statistics(): void
    {
        $count = 3;

        $statistics = Statistic::factory()
            ->count($count)
            ->create();

        $this->assertCount($count, $statistics);
        $this->assertDatabaseCount('statistics', $count);
    }
}
