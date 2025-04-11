<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Unit\Database\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

final class StudySessionsTableTest extends BaseTestCase
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
    public function study_sessions_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('study_sessions'));
    }

    #[Test]
    public function study_sessions_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('study_sessions', [
            'id', 'user_id', 'started_at', 'ended_at',
        ]));
    }

    #[Test]
    public function study_sessions_table_columns_have_correct_data_types(): void
    {
        $this->assertTrue(Schema::hasColumn('study_sessions', 'id'));
        $this->assertTrue(Schema::hasColumn('study_sessions', 'user_id'));
        $this->assertTrue(Schema::hasColumn('study_sessions', 'started_at'));
        $this->assertTrue(Schema::hasColumn('study_sessions', 'ended_at'));

        $columns = DB::select('PRAGMA table_info(study_sessions)');

        $columnTypes = collect($columns)->pluck('type', 'name');

        $this->assertEquals('INTEGER', $columnTypes['id']);
        $this->assertEquals('INTEGER', $columnTypes['user_id']);
        $this->assertEquals('datetime', $columnTypes['started_at']);
        $this->assertEquals('datetime', $columnTypes['ended_at']);
    }

    #[Test]
    public function study_sessions_table_has_correct_foreign_keys(): void
    {
        $foreignKeys = DB::select('PRAGMA foreign_key_list(study_sessions)');

        $this->assertCount(1, $foreignKeys);
        $this->assertEquals('users', $foreignKeys[0]->table);
        $this->assertEquals('user_id', $foreignKeys[0]->from);
        $this->assertEquals('id', $foreignKeys[0]->to);
    }
}
