<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\database\migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StatisticsTableTest extends TestCase
{
    use RefreshDatabase;

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
