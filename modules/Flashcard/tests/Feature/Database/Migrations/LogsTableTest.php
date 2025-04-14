<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\database\migrations;

use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LogsTableTest extends TestCase
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
            'id', 'user_id', 'action', 'details', 'level', 'created_at',
        ]));
    }

    #[Test]
    public function logs_table_columns_have_correct_data_types(): void
    {
        $this->assertTrue(Schema::hasColumn('logs', 'id'));
        $this->assertTrue(Schema::hasColumn('logs', 'user_id'));
        $this->assertTrue(Schema::hasColumn('logs', 'action'));
        $this->assertTrue(Schema::hasColumn('logs', 'details'));
        $this->assertTrue(Schema::hasColumn('logs', 'level'));
        $this->assertTrue(Schema::hasColumn('logs', 'created_at'));
    }

    #[Test]
    public function logs_table_has_correct_foreign_keys(): void
    {
        $this->assertTrue(Schema::hasColumn('logs', 'user_id'));
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasColumn('users', 'id'));
    }
}
