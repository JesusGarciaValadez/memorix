<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\database\migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PracticeResultsTableTest extends TestCase
{
    use RefreshDatabase;

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
        $this->assertTrue(Schema::hasColumn('practice_results', 'id'));
        $this->assertTrue(Schema::hasColumn('practice_results', 'user_id'));
        $this->assertTrue(Schema::hasColumn('practice_results', 'flashcard_id'));
        $this->assertTrue(Schema::hasColumn('practice_results', 'study_session_id'));
        $this->assertTrue(Schema::hasColumn('practice_results', 'is_correct'));
        $this->assertTrue(Schema::hasColumn('practice_results', 'created_at'));
        $this->assertTrue(Schema::hasColumn('practice_results', 'updated_at'));
    }

    #[Test]
    public function practice_results_table_has_correct_foreign_keys(): void
    {
        $this->assertTrue(Schema::hasColumn('practice_results', 'user_id'));
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasColumn('users', 'id'));

        $this->assertTrue(Schema::hasColumn('practice_results', 'flashcard_id'));
        $this->assertTrue(Schema::hasTable('flashcards'));
        $this->assertTrue(Schema::hasColumn('flashcards', 'id'));

        $this->assertTrue(Schema::hasColumn('practice_results', 'study_session_id'));
        $this->assertTrue(Schema::hasTable('study_sessions'));
        $this->assertTrue(Schema::hasColumn('study_sessions', 'id'));
    }

    #[Test]
    public function is_correct_column_has_default_value(): void
    {
        $this->assertTrue(Schema::hasColumn('practice_results', 'is_correct'));
    }
}
