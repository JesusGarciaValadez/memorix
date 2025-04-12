<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Models;

use App\Models\User;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\PracticeResult;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class PracticeResultTest extends TestCase
{
    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $practiceResult = new PracticeResult();
        $this->assertEquals(
            ['user_id', 'flashcard_id', 'study_session_id', 'is_correct'],
            $practiceResult->getFillable()
        );
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $practiceResult = new PracticeResult();
        $this->assertArrayHasKey('is_correct', $practiceResult->getCasts());
        $this->assertEquals('boolean', $practiceResult->getCasts()['is_correct']);
    }

    #[Test]
    public function it_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $flashcard = Flashcard::factory()->create(['user_id' => $user->id]);
        $studySession = StudySession::factory()->create(['user_id' => $user->id]);

        $practiceResult = PracticeResult::create([
            'user_id' => $user->id,
            'flashcard_id' => $flashcard->id,
            'study_session_id' => $studySession->id,
            'is_correct' => true,
        ]);

        $this->assertInstanceOf(User::class, $practiceResult->user);
        $this->assertEquals($user->id, $practiceResult->user->id);
    }

    #[Test]
    public function it_belongs_to_a_flashcard(): void
    {
        $user = User::factory()->create();
        $flashcard = Flashcard::factory()->create(['user_id' => $user->id]);
        $studySession = StudySession::factory()->create(['user_id' => $user->id]);

        $practiceResult = PracticeResult::create([
            'user_id' => $user->id,
            'flashcard_id' => $flashcard->id,
            'study_session_id' => $studySession->id,
            'is_correct' => true,
        ]);

        $this->assertInstanceOf(Flashcard::class, $practiceResult->flashcard);
        $this->assertEquals($flashcard->id, $practiceResult->flashcard->id);
    }

    #[Test]
    public function it_belongs_to_a_study_session(): void
    {
        $user = User::factory()->create();
        $flashcard = Flashcard::factory()->create(['user_id' => $user->id]);
        $studySession = StudySession::factory()->create(['user_id' => $user->id]);

        $practiceResult = PracticeResult::create([
            'user_id' => $user->id,
            'flashcard_id' => $flashcard->id,
            'study_session_id' => $studySession->id,
            'is_correct' => true,
        ]);

        $this->assertInstanceOf(StudySession::class, $practiceResult->studySession);
        $this->assertEquals($studySession->id, $practiceResult->studySession->id);
    }

    #[Test]
    public function it_can_be_created_through_factory(): void
    {
        $practiceResult = PracticeResult::factory()->create();

        $this->assertDatabaseHas('practice_results', [
            'id' => $practiceResult->id,
        ]);
    }
}
