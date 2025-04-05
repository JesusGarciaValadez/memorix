<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\Database\Migrations;

use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

final class FlashcardsTableTest extends BaseMigrationTest
{
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
        $this->assertColumnHasDataType('flashcards', 'id', ['bigint']);
        $this->assertColumnHasDataType('flashcards', 'user_id', ['bigint']);
        $this->assertColumnHasDataType('flashcards', 'question', ['text']);
        $this->assertColumnHasDataType('flashcards', 'answer', ['text']);
        $this->assertColumnHasDataType('flashcards', 'created_at', ['datetime', 'datetime_immutable']);
        $this->assertColumnHasDataType('flashcards', 'updated_at', ['datetime', 'datetime_immutable']);
        $this->assertColumnHasDataType('flashcards', 'deleted_at', ['datetime', 'datetime_immutable']);
    }

    #[Test]
    public function flashcards_table_has_correct_foreign_keys(): void
    {
        $this->assertHasForeignKey('flashcards', 'user_id', 'users', 'id');
    }

    #[Test]
    public function deleted_at_column_is_nullable(): void
    {
        $this->assertColumnIsNullable('flashcards', 'deleted_at');
    }
}
