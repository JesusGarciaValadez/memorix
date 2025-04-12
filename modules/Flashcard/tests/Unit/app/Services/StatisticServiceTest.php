<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Services;

use App\Models\User;
use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Services\StatisticService;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class StatisticServiceTest extends TestCase
{
    private StatisticService $statisticService;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->statisticService = new StatisticService();
        $this->user = User::factory()->create([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    #[Test]
    public function it_can_create_statistic_for_user(): void
    {
        $statistic = $this->statisticService->createStatistic($this->user->id);

        $this->assertNotNull($statistic);
        $this->assertInstanceOf(Statistic::class, $statistic);
        $this->assertEquals($this->user->id, $statistic->user_id);
        $this->assertEquals(0, $statistic->total_flashcards);
        $this->assertEquals(0, $statistic->total_study_sessions);
        $this->assertEquals(0, $statistic->total_correct_answers);
        $this->assertEquals(0, $statistic->total_incorrect_answers);
    }

    #[Test]
    public function it_can_get_statistic_by_user_id(): void
    {
        $createdStatistic = $this->statisticService->createStatistic($this->user->id);

        $statistic = $this->statisticService->getByUserId($this->user->id);

        $this->assertNotNull($statistic);
        $this->assertEquals($createdStatistic->id, $statistic->id);
    }

    #[Test]
    public function it_returns_null_when_getting_non_existent_statistic(): void
    {
        $statistic = $this->statisticService->getByUserId(999);

        $this->assertNull($statistic);
    }

    #[Test]
    public function it_can_increment_total_flashcards(): void
    {
        $this->statisticService->createStatistic($this->user->id);

        $result = $this->statisticService->incrementTotalFlashcards($this->user->id);

        $this->assertTrue($result);
        $statistic = $this->statisticService->getByUserId($this->user->id);
        $this->assertEquals(1, $statistic->total_flashcards);
    }

    #[Test]
    public function it_can_decrement_total_flashcards(): void
    {
        $statistic = $this->statisticService->createStatistic($this->user->id);
        $statistic->total_flashcards = 2;
        $statistic->save();

        $result = $this->statisticService->decrementTotalFlashcards($this->user->id);

        $this->assertTrue($result);
        $statistic = $this->statisticService->getByUserId($this->user->id);
        $this->assertEquals(1, $statistic->total_flashcards);
    }

    #[Test]
    public function it_will_not_decrement_below_zero(): void
    {
        $statistic = $this->statisticService->createStatistic($this->user->id);
        $statistic->total_flashcards = 0;
        $statistic->save();

        $result = $this->statisticService->decrementTotalFlashcards($this->user->id);

        $this->assertTrue($result);
        $statistic = $this->statisticService->getByUserId($this->user->id);
        $this->assertEquals(0, $statistic->total_flashcards);
    }

    #[Test]
    public function it_can_increment_study_sessions(): void
    {
        $this->statisticService->createStatistic($this->user->id);

        $result = $this->statisticService->incrementStudySessions($this->user->id);

        $this->assertTrue($result);
        $statistic = $this->statisticService->getByUserId($this->user->id);
        $this->assertEquals(1, $statistic->total_study_sessions);
    }

    #[Test]
    public function it_can_increment_correct_answers(): void
    {
        $this->statisticService->createStatistic($this->user->id);

        $result = $this->statisticService->incrementCorrectAnswers($this->user->id);

        $this->assertTrue($result);
        $statistic = $this->statisticService->getByUserId($this->user->id);
        $this->assertEquals(1, $statistic->total_correct_answers);
    }

    #[Test]
    public function it_can_increment_incorrect_answers(): void
    {
        $this->statisticService->createStatistic($this->user->id);

        $result = $this->statisticService->incrementIncorrectAnswers($this->user->id);

        $this->assertTrue($result);
        $statistic = $this->statisticService->getByUserId($this->user->id);
        $this->assertEquals(1, $statistic->total_incorrect_answers);
    }

    #[Test]
    public function it_can_reset_practice_statistics(): void
    {
        $statistic = $this->statisticService->createStatistic($this->user->id);
        $statistic->total_study_sessions = 5;
        $statistic->total_correct_answers = 10;
        $statistic->total_incorrect_answers = 3;
        $statistic->save();

        $result = $this->statisticService->resetPracticeStatistics($this->user->id);

        $this->assertTrue($result);
        $statistic = $this->statisticService->getByUserId($this->user->id);
        $this->assertEquals(0, $statistic->total_study_sessions);
        $this->assertEquals(0, $statistic->total_correct_answers);
        $this->assertEquals(0, $statistic->total_incorrect_answers);
    }

    #[Test]
    public function it_can_add_study_time(): void
    {
        $this->statisticService->createStatistic($this->user->id);

        $result = $this->statisticService->addStudyTime($this->user, 30);

        $this->assertTrue($result);

        $studySession = StudySession::where('user_id', $this->user->id)->first();
        $this->assertNotNull($studySession);
        $this->assertEquals($this->user->id, $studySession->user_id);
        $this->assertNotNull($studySession->started_at);
        $this->assertNotNull($studySession->ended_at);
        $this->assertEquals(30, $studySession->started_at->diffInMinutes($studySession->ended_at));
    }

    #[Test]
    public function it_can_get_average_study_session_duration(): void
    {
        $this->statisticService->createStatistic($this->user->id);

        // Create two study sessions of 30 and 60 minutes
        $this->statisticService->addStudyTime($this->user, 30);
        $this->statisticService->addStudyTime($this->user, 60);

        $average = $this->statisticService->getAverageStudySessionDuration($this->user->id);

        $this->assertEquals(45.0, $average);
    }

    #[Test]
    public function it_returns_zero_for_average_when_no_sessions(): void
    {
        $this->statisticService->createStatistic($this->user->id);

        $average = $this->statisticService->getAverageStudySessionDuration($this->user->id);

        $this->assertEquals(0.0, $average);
    }

    #[Test]
    public function it_can_get_total_study_time(): void
    {
        $this->statisticService->createStatistic($this->user->id);

        // Create two study sessions of 30 and 60 minutes
        $this->statisticService->addStudyTime($this->user, 30);
        $this->statisticService->addStudyTime($this->user, 60);

        $total = $this->statisticService->getTotalStudyTime($this->user->id);

        $this->assertEquals(90.0, $total);
    }

    #[Test]
    public function it_returns_zero_for_total_study_time_when_no_sessions(): void
    {
        $this->statisticService->createStatistic($this->user->id);

        $total = $this->statisticService->getTotalStudyTime($this->user->id);

        $this->assertEquals(0.0, $total);
    }
}
