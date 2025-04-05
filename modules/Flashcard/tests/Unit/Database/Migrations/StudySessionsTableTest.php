<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\Database\Migrations;

use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

final class StudySessionsTableTest extends BaseMigrationTest
{
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
        $this->assertColumnHasDataType('study_sessions', 'id', ['bigint']);
        $this->assertColumnHasDataType('study_sessions', 'user_id', ['bigint']);
        $this->assertColumnHasDataType('study_sessions', 'started_at', ['datetime', 'datetime_immutable']);
        $this->assertColumnHasDataType('study_sessions', 'ended_at', ['datetime', 'datetime_immutable']);
    }

    #[Test]
    public function study_sessions_table_has_correct_foreign_keys(): void
    {
        $this->assertHasForeignKey('study_sessions', 'user_id', 'users', 'id');
    }

    #[Test]
    public function ended_at_column_is_nullable(): void
    {
        $this->assertColumnIsNullable('study_sessions', 'ended_at');
    }
}
