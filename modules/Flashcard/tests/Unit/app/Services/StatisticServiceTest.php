<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Unit\app\Services;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\PracticeResult;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Services\StatisticService;
use PHPUnit\Framework\Attributes\Test;

final class StatisticServiceTest extends BaseTestCase
{
    use RefreshDatabase;

    private StatisticService $statisticService;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->statisticService = app(StatisticService::class);
        $this->user = User::factory()->create();
    }

    public function createApplication()
    {
        $app = require __DIR__.'/../../../../../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    #[Test]
    public function it_can_track_practice_results(): void
    {
        // Create flashcards
        $flashcards = Flashcard::factory()
            ->count(4)
            ->create(['user_id' => $this->user->id]);

        // Create a study session
        $studySession = StudySession::create([
            'user_id' => $this->user->id,
            'started_at' => now(),
        ]);

        // No answers yet
        $stats = $this->statisticService->getStatisticsForUser($this->user->id);
        $this->assertEquals(0, $stats['correct_answers']);
        $this->assertEquals(0, $stats['incorrect_answers']);

        // Answer 2 flashcards correctly
        foreach ($flashcards->take(2) as $flashcard) {
            PracticeResult::create([
                'user_id' => $this->user->id,
                'flashcard_id' => $flashcard->id,
                'study_session_id' => $studySession->id,
                'is_correct' => true,
            ]);
            $this->statisticService->incrementCorrectAnswers($this->user->id);
        }

        // Should have 2 correct answers
        $stats = $this->statisticService->getStatisticsForUser($this->user->id);
        $this->assertEquals(2, $stats['correct_answers']);
        $this->assertEquals(0, $stats['incorrect_answers']);

        // Answer another flashcard incorrectly
        PracticeResult::create([
            'user_id' => $this->user->id,
            'flashcard_id' => $flashcards[2]->id,
            'study_session_id' => $studySession->id,
            'is_correct' => false,
        ]);
        $this->statisticService->incrementIncorrectAnswers($this->user->id);

        // Should have 2 correct and 1 incorrect answers
        $stats = $this->statisticService->getStatisticsForUser($this->user->id);
        $this->assertEquals(2, $stats['correct_answers']);
        $this->assertEquals(1, $stats['incorrect_answers']);

        // Check success rate
        $successRate = $this->statisticService->getPracticeSuccessRate($this->user->id);
        $this->assertEquals(66.67, $successRate);
    }

    #[Test]
    public function it_can_track_study_sessions(): void
    {
        // Initially no study sessions
        $stats = $this->statisticService->getStatisticsForUser($this->user->id);
        $this->assertEquals(0, $stats['study_sessions']);

        // Create and track a study session
        StudySession::create([
            'user_id' => $this->user->id,
            'started_at' => now(),
        ]);
        $this->statisticService->incrementStudySessions($this->user->id);

        // Should have 1 study session
        $stats = $this->statisticService->getStatisticsForUser($this->user->id);
        $this->assertEquals(1, $stats['study_sessions']);

        // Create and track another study session
        StudySession::create([
            'user_id' => $this->user->id,
            'started_at' => now(),
        ]);
        $this->statisticService->incrementStudySessions($this->user->id);

        // Should have 2 study sessions
        $stats = $this->statisticService->getStatisticsForUser($this->user->id);
        $this->assertEquals(2, $stats['study_sessions']);
    }
}
