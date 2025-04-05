<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\Database\Migrations;

use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

final class LogsTableTest extends BaseMigrationTest
{
    #[Test]
    public function logs_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('logs'));
    }

    #[Test]
    public function logs_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('logs', [
            'id', 'user_id', 'action', 'details', 'created_at',
        ]));
    }

    #[Test]
    public function logs_table_columns_have_correct_data_types(): void
    {
        $this->assertColumnHasDataType('logs', 'id', ['bigint']);
        $this->assertColumnHasDataType('logs', 'user_id', ['bigint']);
        $this->assertColumnHasDataType('logs', 'action', ['text']);
        $this->assertColumnHasDataType('logs', 'details', ['text']);
        $this->assertColumnHasDataType('logs', 'created_at', ['datetime', 'datetime_immutable']);
    }

    #[Test]
    public function logs_table_has_correct_foreign_keys(): void
    {
        $this->assertHasForeignKey('logs', 'user_id', 'users', 'id');
    }

    #[Test]
    public function details_column_is_nullable(): void
    {
        $this->assertColumnIsNullable('logs', 'details');
    }
}
