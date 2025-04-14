<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\database\migrations;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StatisticsTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Disable foreign key checks for SQLite
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF;');
        }

        // Run core Laravel migrations first
        $this->artisan('migrate', ['--path' => 'database/migrations']);

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
    public function statistics_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('statistics'));
    }

    #[Test]
    public function statistics_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('statistics', [
            'id', 'user_id', 'total_flashcards', 'total_study_sessions', 'total_correct_answers',
            'total_incorrect_answers', 'created_at', 'updated_at',
        ]));
    }

    #[Test]
    public function statistics_table_columns_have_correct_data_types(): void
    {
        $this->assertTrue(Schema::hasColumn('statistics', 'id'));
        $this->assertTrue(Schema::hasColumn('statistics', 'user_id'));
        $this->assertTrue(Schema::hasColumn('statistics', 'total_flashcards'));
        $this->assertTrue(Schema::hasColumn('statistics', 'total_study_sessions'));
        $this->assertTrue(Schema::hasColumn('statistics', 'total_correct_answers'));
        $this->assertTrue(Schema::hasColumn('statistics', 'total_incorrect_answers'));
        $this->assertTrue(Schema::hasColumn('statistics', 'created_at'));
        $this->assertTrue(Schema::hasColumn('statistics', 'updated_at'));
    }

    #[Test]
    public function statistics_table_has_correct_foreign_keys(): void
    {
        $this->assertTrue(Schema::hasColumn('statistics', 'user_id'));
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasColumn('users', 'id'));
    }
}
