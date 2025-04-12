<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Unit\app\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\PracticeResult;
use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Providers\FlashcardServiceProvider;
use PHPUnit\Framework\Attributes\Test;

final class StatisticTest extends BaseTestCase
{
    private User $user;

    private Statistic $statistic;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable foreign key checks for SQLite
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');
        }

        // Run core Laravel migrations first
        $this->artisan('migrate', ['--path' => 'database/migrations']);

        // Run module migrations
        $this->artisan('migrate', ['--path' => 'modules/Flashcard/database/migrations']);

        // Create a test user
        $this->user = User::factory()->create();
        $this->statistic = Statistic::create([
            'user_id' => $this->user->id,
            'total_flashcards' => 8,
            'total_study_sessions' => 0,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
        ]);
    }

    protected function tearDown(): void
    {
        // Re-enable foreign key checks for SQLite
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=ON;');
        }

        parent::tearDown();
    }

    public function createApplication()
    {
        $app = require __DIR__.'/../../../../../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $app->register(FlashcardServiceProvider::class);

        return $app;
    }

    #[Test]
    public function it_can_track_learning_progress(): void
    {
        // Create a study session
        $studySession = StudySession::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Create flashcards
        $flashcards = Flashcard::factory()->count(8)->create([
            'user_id' => $this->user->id,
        ]);

        // Practice some flashcards correctly
        foreach ([0, 1, 2, 3, 4] as $index) {
            PracticeResult::factory()->create([
                'user_id' => $this->user->id,
                'flashcard_id' => $flashcards[$index]->id,
                'study_session_id' => $studySession->id,
                'is_correct' => true,
            ]);
            $this->statistic->incrementTotalCorrectAnswers();
        }

        // Practice some flashcards incorrectly
        foreach ([5, 6] as $index) {
            PracticeResult::factory()->create([
                'user_id' => $this->user->id,
                'flashcard_id' => $flashcards[$index]->id,
                'study_session_id' => $studySession->id,
                'is_correct' => false,
            ]);
            $this->statistic->incrementTotalIncorrectAnswers();
        }

        // Increment study sessions
        $this->statistic->incrementTotalStudySessions();

        // Refresh statistics
        $this->statistic->refresh();

        // Verify progress tracking
        // 7 out of 8 flashcards have been practiced (87.5%)
        $this->assertEqualsWithDelta(87.5, $this->statistic->getCompletionPercentage(), 0.01);
        // 5 correct out of 7 total answers (71.43%)
        $this->assertEqualsWithDelta(71.43, $this->statistic->getCorrectPercentage(), 0.01);
        $this->assertEquals(1, $this->statistic->total_study_sessions);
        $this->assertEquals(5, $this->statistic->total_correct_answers);
        $this->assertEquals(2, $this->statistic->total_incorrect_answers);
    }

    #[Test]
    public function it_can_handle_edge_cases_in_statistics(): void
    {
        // Case 1: No answers yet
        $this->assertEquals(0.0, $this->statistic->getCorrectPercentage());
        $this->assertEquals(0.0, $this->statistic->getCompletionPercentage());

        // Case 2: All correct answers
        $this->statistic->update([
            'total_correct_answers' => 8,
            'total_incorrect_answers' => 0,
        ]);
        $this->assertEquals(100.0, $this->statistic->getCorrectPercentage());

        // Case 3: All incorrect answers
        $this->statistic->update([
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 8,
        ]);
        $this->assertEquals(0.0, $this->statistic->getCorrectPercentage());

        // Case 4: Large numbers
        $this->statistic->update([
            'total_correct_answers' => 1000,
            'total_incorrect_answers' => 1000,
        ]);
        $this->assertEquals(50.0, $this->statistic->getCorrectPercentage());
    }

    #[Test]
    public function it_can_handle_bulk_operations(): void
    {
        // Increment multiple statistics at once
        $this->statistic->incrementTotalFlashcards(5);
        $this->statistic->incrementTotalStudySessions(3);
        $this->statistic->incrementTotalCorrectAnswers(10);
        $this->statistic->incrementTotalIncorrectAnswers(5);

        // Verify all increments
        $this->assertEquals(13, $this->statistic->total_flashcards);
        $this->assertEquals(3, $this->statistic->total_study_sessions);
        $this->assertEquals(10, $this->statistic->total_correct_answers);
        $this->assertEquals(5, $this->statistic->total_incorrect_answers);
    }

    #[Test]
    public function it_can_handle_concurrent_updates(): void
    {
        // Simulate concurrent updates
        $statistic1 = $this->statistic;
        $statistic2 = Statistic::find($this->statistic->id);

        // First update
        $statistic1->incrementTotalCorrectAnswers();
        $statistic1->save();

        // Second update
        $statistic2->incrementTotalIncorrectAnswers();
        $statistic2->save();

        // Verify both updates are reflected
        $this->statistic->refresh();
        $this->assertEquals(1, $this->statistic->total_correct_answers);
        $this->assertEquals(1, $this->statistic->total_incorrect_answers);
    }
}
