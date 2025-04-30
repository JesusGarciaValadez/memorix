<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\database\factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Models\Flashcard;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardFactoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_flashcard(): void
    {
        $flashcard = Flashcard::factory()->create();

        $this->assertInstanceOf(Flashcard::class, $flashcard);
        $this->assertDatabaseHas('flashcards', ['id' => $flashcard->id]);
    }

    #[Test]
    public function it_creates_a_flashcard_with_valid_data(): void
    {
        $flashcard = Flashcard::factory()->create();

        $this->assertNotNull($flashcard->user_id);
        $this->assertNotEmpty($flashcard->question);
        $this->assertNotEmpty($flashcard->answer);
        $this->assertStringEndsWith('?', $flashcard->question);
        $this->assertNotNull($flashcard->created_at);
        $this->assertNotNull($flashcard->updated_at);
    }

    #[Test]
    public function it_creates_a_flashcard_with_a_real_user(): void
    {
        $user = User::factory()->create();

        $flashcard = Flashcard::factory()
            ->for($user)
            ->create();

        $this->assertEquals($user->id, $flashcard->user_id);
        $this->assertInstanceOf(User::class, $flashcard->user);
    }

    #[Test]
    public function it_can_create_a_flashcard_with_short_answer(): void
    {
        $flashcard = Flashcard::factory()
            ->shortAnswer()
            ->create();

        $this->assertInstanceOf(Flashcard::class, $flashcard);

        // Count words in the answer (simple approximation by splitting on spaces)
        $wordCount = count(explode(' ', $flashcard->answer));

        // A short answer should have fewer words than a default answer
        // Default is a paragraph (1-3 sentences), short is 1-5 words
        $this->assertLessThanOrEqual(10, $wordCount);
    }

    #[Test]
    public function it_can_create_a_recent_flashcard(): void
    {
        $flashcard = Flashcard::factory()
            ->recent()
            ->create();

        $this->assertInstanceOf(Flashcard::class, $flashcard);

        // A recent flashcard should be created within the last week
        $oneWeekAgo = now()->subWeek();
        $this->assertNotNull($flashcard->created_at);
        $this->assertTrue($flashcard->created_at->isAfter($oneWeekAgo));
    }

    #[Test]
    public function it_can_create_multiple_flashcards(): void
    {
        $count = 5;

        $flashcards = Flashcard::factory()
            ->count($count)
            ->create();

        $this->assertCount($count, $flashcards);
        $this->assertDatabaseCount('flashcards', $count);
    }

    public function test_created_at_is_recent(): void
    {
        $flashcard = Flashcard::factory()->create();
        $this->assertNotNull($flashcard->created_at);
        // @phpstan-ignore-next-line class.notFound
        $this->assertTrue($flashcard->created_at->isAfter(Carbon::now()->subYears(1)));
    }
}
