<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Models\Statistic;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StatisticTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_correct_table_name(): void
    {
        $statistic = new Statistic();
        $this->assertEquals('statistics', $statistic->getTable());
    }

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $statistic = new Statistic();
        $expectedFillable = [
            'user_id',
            'total_flashcards',
            'total_study_sessions',
            'total_correct_answers',
            'total_incorrect_answers',
        ];
        $this->assertEquals($expectedFillable, $statistic->getFillable());
    }

    #[Test]
    public function it_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $statistic = Statistic::create([
            'user_id' => $user->id,
            'total_flashcards' => 0,
            'total_study_sessions' => 0,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
        ]);

        $this->assertInstanceOf(User::class, $statistic->user);
        $this->assertEquals($user->id, $statistic->user->id);
    }

    #[Test]
    public function it_can_calculate_correct_percentage(): void
    {
        // Case 1: No answers
        $statistic1 = new Statistic([
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
        ]);
        $this->assertEquals(0.0, $statistic1->getCorrectPercentage());

        // Case 2: Some answers
        $statistic2 = new Statistic([
            'total_correct_answers' => 6,
            'total_incorrect_answers' => 4,
        ]);
        $this->assertEquals(60.0, $statistic2->getCorrectPercentage());

        // Case 3: All correct
        $statistic3 = new Statistic([
            'total_correct_answers' => 10,
            'total_incorrect_answers' => 0,
        ]);
        $this->assertEquals(100.0, $statistic3->getCorrectPercentage());
    }

    #[Test]
    public function it_can_calculate_completion_percentage(): void
    {
        // Case 1: No flashcards
        $statistic1 = new Statistic([
            'total_flashcards' => 0,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
        ]);
        $this->assertEquals(0.0, $statistic1->getCompletionPercentage());

        // Case 2: Partial completion
        $statistic2 = new Statistic([
            'total_flashcards' => 20,
            'total_correct_answers' => 5,
            'total_incorrect_answers' => 5,
        ]);
        $this->assertEquals(50.0, $statistic2->getCompletionPercentage());

        // Case 3: Complete
        $statistic3 = new Statistic([
            'total_flashcards' => 10,
            'total_correct_answers' => 7,
            'total_incorrect_answers' => 3,
        ]);
        $this->assertEquals(100.0, $statistic3->getCompletionPercentage());
    }

    #[Test]
    public function it_can_increment_total_flashcards(): void
    {
        $statistic = Statistic::create([
            'user_id' => User::factory()->create()->id,
            'total_flashcards' => 5,
            'total_study_sessions' => 0,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
        ]);

        $statistic->incrementTotalFlashcards();
        $this->assertEquals(6, $statistic->total_flashcards);

        $statistic->incrementTotalFlashcards(3);
        $this->assertEquals(9, $statistic->total_flashcards);
    }

    #[Test]
    public function it_can_increment_total_study_sessions(): void
    {
        $statistic = Statistic::create([
            'user_id' => User::factory()->create()->id,
            'total_flashcards' => 0,
            'total_study_sessions' => 2,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
        ]);

        $statistic->incrementTotalStudySessions();
        $this->assertEquals(3, $statistic->total_study_sessions);

        $statistic->incrementTotalStudySessions(2);
        $this->assertEquals(5, $statistic->total_study_sessions);
    }

    #[Test]
    public function it_can_increment_total_correct_answers(): void
    {
        $statistic = Statistic::create([
            'user_id' => User::factory()->create()->id,
            'total_flashcards' => 0,
            'total_study_sessions' => 0,
            'total_correct_answers' => 10,
            'total_incorrect_answers' => 0,
        ]);

        $statistic->incrementTotalCorrectAnswers();
        $this->assertEquals(11, $statistic->total_correct_answers);

        $statistic->incrementTotalCorrectAnswers(4);
        $this->assertEquals(15, $statistic->total_correct_answers);
    }

    #[Test]
    public function it_can_increment_total_incorrect_answers(): void
    {
        $statistic = Statistic::create([
            'user_id' => User::factory()->create()->id,
            'total_flashcards' => 0,
            'total_study_sessions' => 0,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 3,
        ]);

        $statistic->incrementTotalIncorrectAnswers();
        $this->assertEquals(4, $statistic->total_incorrect_answers);

        $statistic->incrementTotalIncorrectAnswers(2);
        $this->assertEquals(6, $statistic->total_incorrect_answers);
    }
}
