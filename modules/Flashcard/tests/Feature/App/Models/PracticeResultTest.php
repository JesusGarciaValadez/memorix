<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\PracticeResult;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\database\factories\PracticeResultFactory;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PracticeResultTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_user_relationship_exists(): void
    {
        $result = PracticeResult::factory()->create();
        $this->assertInstanceOf(BelongsTo::class, $result->user());
        $this->assertInstanceOf(User::class, $result->user()->getRelated());
    }

    #[Test]
    public function test_flashcard_relationship_exists(): void
    {
        $result = PracticeResult::factory()->create();
        $this->assertInstanceOf(BelongsTo::class, $result->flashcard());
        $this->assertInstanceOf(Flashcard::class, $result->flashcard()->getRelated());
    }

    #[Test]
    public function test_study_session_relationship_exists(): void
    {
        $result = PracticeResult::factory()->create();
        $this->assertInstanceOf(BelongsTo::class, $result->studySession());
        $this->assertInstanceOf(StudySession::class, $result->studySession()->getRelated());
    }

    #[Test]
    public function test_factory_exists(): void
    {
        $this->assertInstanceOf(PracticeResultFactory::class, PracticeResult::factory());
    }
}
