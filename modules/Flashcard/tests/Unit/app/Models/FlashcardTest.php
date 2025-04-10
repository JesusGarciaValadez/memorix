<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Unit\app\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Providers\FlashcardServiceProvider;
use PHPUnit\Framework\Attributes\Test;

final class FlashcardTest extends BaseTestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable foreign key checks for SQLite
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');
        }

        // Run module migrations
        $this->artisan('migrate', ['--path' => 'modules/Flashcard/database/migrations']);

        // Create a test user
        $this->user = User::factory()->create();
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
        $flashcard = Flashcard::create([
            'user_id' => $this->user->id,
            'question' => 'What is Laravel?',
            'answer' => 'A PHP framework',
        ]);

        $this->assertInstanceOf(User::class, $flashcard->user);
        $this->assertEquals($this->user->id, $flashcard->user->id);
    }

    #[Test]
    public function it_can_get_all_flashcards_for_user(): void
    {
        Flashcard::factory()->count(3)->create(['user_id' => $this->user->id]);
        Flashcard::factory()->count(2)->create(); // Other user's flashcards

        $flashcards = Flashcard::getAllForUser($this->user->id);

        $this->assertCount(3, $flashcards);
        $this->assertEquals(3, $flashcards->total());
    }

    #[Test]
    public function it_can_get_all_deleted_flashcards_for_user(): void
    {
        $flashcards = Flashcard::factory()->count(3)->create(['user_id' => $this->user->id]);
        $flashcards->each->delete();

        $deletedFlashcards = Flashcard::getAllDeletedForUser($this->user->id);

        $this->assertCount(3, $deletedFlashcards);
        $this->assertEquals(3, $deletedFlashcards->total());
    }

    #[Test]
    public function it_can_find_flashcard_for_user(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);

        $found = Flashcard::findForUser($flashcard->id, $this->user->id);

        $this->assertNotNull($found);
        $this->assertEquals($flashcard->id, $found->id);
    }

    #[Test]
    public function it_can_find_deleted_flashcard_for_user(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);
        $flashcard->delete();

        $found = Flashcard::findForUser($flashcard->id, $this->user->id, true);

        $this->assertNotNull($found);
        $this->assertEquals($flashcard->id, $found->id);
    }

    #[Test]
    public function it_can_restore_all_deleted_flashcards_for_user(): void
    {
        $flashcards = Flashcard::factory()->count(3)->create(['user_id' => $this->user->id]);
        $flashcards->each->delete();

        $result = Flashcard::restoreAllForUser($this->user->id);

        $this->assertTrue($result);
        $this->assertCount(3, Flashcard::where('user_id', $this->user->id)->get());
    }

    #[Test]
    public function it_can_force_delete_all_deleted_flashcards_for_user(): void
    {
        $flashcards = Flashcard::factory()->count(3)->create(['user_id' => $this->user->id]);
        $flashcards->each->delete();

        $result = Flashcard::forceDeleteAllForUser($this->user->id);

        $this->assertTrue($result);
        $this->assertCount(0, Flashcard::withTrashed()->where('user_id', $this->user->id)->get());
    }

    #[Test]
    public function it_can_check_if_correctly_answered(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);
        $studySession = StudySession::create([
            'user_id' => $this->user->id,
            'started_at' => now(),
        ]);

        $flashcard->practiceResults()->create([
            'user_id' => $this->user->id,
            'study_session_id' => $studySession->id,
            'is_correct' => true,
        ]);

        $this->assertTrue($flashcard->isCorrectlyAnswered());
    }

    #[Test]
    public function it_can_check_if_incorrectly_answered(): void
    {
        $flashcard = Flashcard::factory()->create(['user_id' => $this->user->id]);
        $studySession = StudySession::create([
            'user_id' => $this->user->id,
            'started_at' => now(),
        ]);

        $flashcard->practiceResults()->create([
            'user_id' => $this->user->id,
            'study_session_id' => $studySession->id,
            'is_correct' => false,
        ]);

        $this->assertTrue($flashcard->isIncorrectlyAnswered());
    }
}
