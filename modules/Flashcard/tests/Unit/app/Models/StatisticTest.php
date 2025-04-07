<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\PracticeResult;
use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\app\Models\StudySession;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StatisticTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Statistic $statistic;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and statistic
        $this->user = User::factory()->create();
        $this->statistic = Statistic::factory()->create([
            'user_id' => $this->user->id,
            'total_flashcards' => 8,
            'total_study_sessions' => 0,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
        ]);
    }

    #[Test]
    public function it_has_correct_table_name(): void
    {
        $this->assertEquals('statistics', (new Statistic())->getTable());
    }

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $fillable = [
            'user_id',
            'total_flashcards',
            'total_study_sessions',
            'total_correct_answers',
            'total_incorrect_answers',
        ];

        $this->assertEquals($fillable, (new Statistic())->getFillable());
    }

    #[Test]
    public function it_belongs_to_a_user(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new Statistic())->user());
    }

    #[Test]
    public function it_can_calculate_correct_percentage(): void
    {
        $statistic = new Statistic([
            'total_correct_answers' => 7,
            'total_incorrect_answers' => 3,
        ]);

        $this->assertEquals(70.0, $statistic->getCorrectPercentage());
    }

    #[Test]
    public function it_can_calculate_completion_percentage(): void
    {
        // Case 1: No flashcards practiced
        $this->assertEquals(0.0, $this->statistic->getCompletionPercentage());

        // Create a study session
        $studySession = StudySession::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Create 8 flashcards
        $flashcards = Flashcard::factory()->count(8)->create([
            'user_id' => $this->user->id,
        ]);

        // Case 2: Practice 5 unique flashcards (some multiple times)
        foreach ([0, 1, 2, 3, 4] as $index) {
            // Practice each flashcard once
            PracticeResult::factory()->create([
                'user_id' => $this->user->id,
                'flashcard_id' => $flashcards[$index]->id,
                'study_session_id' => $studySession->id,
                'is_correct' => true,
            ]);

            // Practice some flashcards multiple times
            if ($index < 3) {
                PracticeResult::factory()->create([
                    'user_id' => $this->user->id,
                    'flashcard_id' => $flashcards[$index]->id,
                    'study_session_id' => $studySession->id,
                    'is_correct' => false,
                ]);
            }
        }

        // Should be 62.5% (5 unique flashcards out of 8 total)
        $this->assertEquals(62.5, $this->statistic->getCompletionPercentage());

        // Case 3: Practice the remaining 3 flashcards
        foreach ([5, 6, 7] as $index) {
            PracticeResult::factory()->create([
                'user_id' => $this->user->id,
                'flashcard_id' => $flashcards[$index]->id,
                'study_session_id' => $studySession->id,
                'is_correct' => true,
            ]);
        }

        // Should be 100% (all 8 flashcards practiced)
        $this->assertEquals(100.0, $this->statistic->getCompletionPercentage());

        // Case 4: Practice some flashcards again
        foreach ([0, 1, 2] as $index) {
            PracticeResult::factory()->create([
                'user_id' => $this->user->id,
                'flashcard_id' => $flashcards[$index]->id,
                'study_session_id' => $studySession->id,
                'is_correct' => true,
            ]);
        }

        // Should still be 100% (practicing same flashcards multiple times doesn't exceed 100%)
        $this->assertEquals(100.0, $this->statistic->getCompletionPercentage());
    }

    #[Test]
    public function it_can_increment_total_flashcards(): void
    {
        $this->statistic->incrementTotalFlashcards();
        $this->assertEquals(9, $this->statistic->total_flashcards);

        $this->statistic->incrementTotalFlashcards(2);
        $this->assertEquals(11, $this->statistic->total_flashcards);
    }

    #[Test]
    public function it_can_increment_total_study_sessions(): void
    {
        $this->statistic->incrementTotalStudySessions();
        $this->assertEquals(1, $this->statistic->total_study_sessions);

        $this->statistic->incrementTotalStudySessions(2);
        $this->assertEquals(3, $this->statistic->total_study_sessions);
    }

    #[Test]
    public function it_can_increment_total_correct_answers(): void
    {
        $this->statistic->incrementTotalCorrectAnswers();
        $this->assertEquals(1, $this->statistic->total_correct_answers);

        $this->statistic->incrementTotalCorrectAnswers(2);
        $this->assertEquals(3, $this->statistic->total_correct_answers);
    }

    #[Test]
    public function it_can_increment_total_incorrect_answers(): void
    {
        $this->statistic->incrementTotalIncorrectAnswers();
        $this->assertEquals(1, $this->statistic->total_incorrect_answers);

        $this->statistic->incrementTotalIncorrectAnswers(2);
        $this->assertEquals(3, $this->statistic->total_incorrect_answers);
    }
}
