<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Models\Flashcard;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $flashcard = new Flashcard();
        $this->assertEquals(['user_id', 'question', 'answer'], $flashcard->getFillable());
    }

    #[Test]
    public function it_uses_soft_deletes(): void
    {
        $flashcard = new Flashcard();
        $this->assertTrue(method_exists($flashcard, 'bootSoftDeletes'));
    }

    #[Test]
    public function it_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $flashcard = Flashcard::create([
            'user_id' => $user->id,
            'question' => 'What is Laravel?',
            'answer' => 'A PHP framework',
        ]);

        $this->assertInstanceOf(User::class, $flashcard->user);
        $this->assertEquals($user->id, $flashcard->user->id);
    }

    #[Test]
    public function it_can_check_if_correctly_answered(): void
    {
        $flashcard = new Flashcard();
        // Current implementation returns false
        $this->assertFalse($flashcard->isCorrectlyAnswered());
    }

    #[Test]
    public function it_can_check_if_incorrectly_answered(): void
    {
        $flashcard = new Flashcard();
        // Current implementation returns false
        $this->assertFalse($flashcard->isIncorrectlyAnswered());
    }
}
