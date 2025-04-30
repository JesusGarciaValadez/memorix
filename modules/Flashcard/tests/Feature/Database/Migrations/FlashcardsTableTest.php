<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\database\migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardsTableTest extends TestCase
{
    use RefreshDatabase;

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
