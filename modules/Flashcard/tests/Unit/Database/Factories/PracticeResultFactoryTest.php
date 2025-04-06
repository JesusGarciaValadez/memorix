<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\Database\Factories;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\PracticeResult;
use Modules\Flashcard\app\Models\StudySession;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PracticeResultFactoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_practice_result(): void
    {
        $practiceResult = PracticeResult::factory()->create();

        $this->assertInstanceOf(PracticeResult::class, $practiceResult);
        $this->assertDatabaseHas('practice_results', ['id' => $practiceResult->id]);
    }

    #[Test]
    public function it_creates_a_practice_result_with_valid_data(): void
    {
        $practiceResult = PracticeResult::factory()->create();

        $this->assertNotNull($practiceResult->user_id);
        $this->assertNotNull($practiceResult->flashcard_id);
        $this->assertNotNull($practiceResult->study_session_id);
        $this->assertIsBool($practiceResult->is_correct);
        $this->assertNotNull($practiceResult->created_at);
        $this->assertNotNull($practiceResult->updated_at);
    }

    #[Test]
    public function it_creates_a_practice_result_with_a_real_user(): void
    {
        $user = User::factory()->create();

        $practiceResult = PracticeResult::factory()
            ->for($user)
            ->create();

        $this->assertEquals($user->id, $practiceResult->user_id);
        $this->assertInstanceOf(User::class, $practiceResult->user);
    }

    #[Test]
    public function it_creates_a_practice_result_with_a_real_flashcard(): void
    {
        $flashcard = Flashcard::factory()->create();

        $practiceResult = PracticeResult::factory()
            ->for($flashcard)
            ->create();

        $this->assertEquals($flashcard->id, $practiceResult->flashcard_id);
        $this->assertInstanceOf(Flashcard::class, $practiceResult->flashcard);
    }

    #[Test]
    public function it_creates_a_practice_result_with_a_real_study_session(): void
    {
        $studySession = StudySession::factory()->create();

        $practiceResult = PracticeResult::factory()
            ->for($studySession)
            ->create();

        $this->assertEquals($studySession->id, $practiceResult->study_session_id);
        $this->assertInstanceOf(StudySession::class, $practiceResult->studySession);
    }

    #[Test]
    public function it_can_create_a_correct_answer(): void
    {
        $practiceResult = PracticeResult::factory()
            ->correct()
            ->create();

        $this->assertInstanceOf(PracticeResult::class, $practiceResult);
        $this->assertTrue($practiceResult->is_correct);
    }

    #[Test]
    public function it_can_create_an_incorrect_answer(): void
    {
        $practiceResult = PracticeResult::factory()
            ->incorrect()
            ->create();

        $this->assertInstanceOf(PracticeResult::class, $practiceResult);
        $this->assertFalse($practiceResult->is_correct);
    }

    #[Test]
    public function it_can_create_a_recent_practice_result(): void
    {
        $practiceResult = PracticeResult::factory()
            ->recent()
            ->create();

        $this->assertInstanceOf(PracticeResult::class, $practiceResult);

        // A recent practice result should be created within the last week
        $oneWeekAgo = now()->subWeek();
        $this->assertTrue($practiceResult->created_at->isAfter($oneWeekAgo));
    }

    #[Test]
    public function it_can_create_multiple_practice_results(): void
    {
        $count = 5;

        $practiceResults = PracticeResult::factory()
            ->count($count)
            ->create();

        $this->assertCount($count, $practiceResults);
        $this->assertDatabaseCount('practice_results', $count);
    }
}
