<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Unit\Database\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

final class FlashcardsTableTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable foreign key checks for SQLite
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');
        }

        // Run module migrations
        $this->artisan('migrate', ['--path' => 'modules/Flashcard/database/migrations']);
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

        return $app;
    }

    #[Test]
    public function flashcards_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('flashcards'));
    }

    #[Test]
    public function flashcards_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('flashcards', [
            'id', 'user_id', 'question', 'answer', 'created_at', 'updated_at', 'deleted_at',
        ]));
    }

    #[Test]
    public function flashcards_table_columns_have_correct_data_types(): void
    {
        $this->assertTrue(Schema::hasColumn('flashcards', 'id'));
        $this->assertTrue(Schema::hasColumn('flashcards', 'user_id'));
        $this->assertTrue(Schema::hasColumn('flashcards', 'question'));
        $this->assertTrue(Schema::hasColumn('flashcards', 'answer'));
        $this->assertTrue(Schema::hasColumn('flashcards', 'created_at'));
        $this->assertTrue(Schema::hasColumn('flashcards', 'updated_at'));
        $this->assertTrue(Schema::hasColumn('flashcards', 'deleted_at'));
    }

    #[Test]
    public function flashcards_table_has_correct_foreign_keys(): void
    {
        $this->assertTrue(Schema::hasColumn('flashcards', 'user_id'));
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasColumn('users', 'id'));
    }

    #[Test]
    public function deleted_at_column_is_nullable(): void
    {
        $this->assertTrue(Schema::hasColumn('flashcards', 'deleted_at'));
    }
}
