<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\database\migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StudySessionsTableTest extends TestCase
{
    use RefreshDatabase;

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
