<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\Database\Migrations;

use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

final class PracticeResultsTableTest extends BaseMigrationTest
{
    #[Test]
    public function practice_results_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('practice_results'));
    }

    #[Test]
    public function practice_results_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('practice_results', [
            'id', 'user_id', 'flashcard_id', 'study_session_id', 'is_correct', 'created_at', 'updated_at',
        ]));
    }

    #[Test]
    public function practice_results_table_columns_have_correct_data_types(): void
    {
        $this->assertColumnHasDataType('practice_results', 'id', ['bigint']);
        $this->assertColumnHasDataType('practice_results', 'user_id', ['bigint']);
        $this->assertColumnHasDataType('practice_results', 'flashcard_id', ['bigint']);
        $this->assertColumnHasDataType('practice_results', 'study_session_id', ['bigint']);
        $this->assertColumnHasDataType('practice_results', 'is_correct', ['boolean', 'tinyint']);
        $this->assertColumnHasDataType('practice_results', 'created_at', ['datetime', 'datetime_immutable']);
        $this->assertColumnHasDataType('practice_results', 'updated_at', ['datetime', 'datetime_immutable']);
    }

    #[Test]
    public function practice_results_table_has_correct_foreign_keys(): void
    {
        $this->assertHasForeignKey('practice_results', 'user_id', 'users', 'id');
        $this->assertHasForeignKey('practice_results', 'flashcard_id', 'flashcards', 'id');
        $this->assertHasForeignKey('practice_results', 'study_session_id', 'study_sessions', 'id');
    }

    #[Test]
    public function is_correct_column_has_default_value(): void
    {
        $this->assertColumnHasDefaultValue('practice_results', 'is_correct', false);
    }
}
