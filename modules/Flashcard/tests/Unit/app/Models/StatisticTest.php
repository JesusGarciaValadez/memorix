<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Unit\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    use RefreshDatabase;

    private User $user;

    private Statistic $statistic;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable foreign key checks for SQLite
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');
        }

        // Run module migrations
        $this->artisan('migrate', ['--path' => 'modules/Flashcard/database/migrations']);

        // Create a user and statistic
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
        }

        // Refresh the statistic to get updated data
        $this->statistic->refresh();
        $this->assertEquals(62.5, $this->statistic->getCompletionPercentage());

        // Case 3: Practice all flashcards
        foreach ([5, 6, 7] as $index) {
            PracticeResult::factory()->create([
                'user_id' => $this->user->id,
                'flashcard_id' => $flashcards[$index]->id,
                'study_session_id' => $studySession->id,
                'is_correct' => true,
            ]);
        }

        // Refresh the statistic to get updated data
        $this->statistic->refresh();
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
