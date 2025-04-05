<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\Database\Migrations;

use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;

final class StatisticsTableTest extends BaseMigrationTest
{
    #[Test]
    public function statistics_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('statistics'));
    }

    #[Test]
    public function statistics_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('statistics', [
            'id',
            'user_id',
            'total_flashcards',
            'total_study_sessions',
            'total_correct_answers',
            'total_incorrect_answers',
            'created_at',
            'updated_at',
        ]));
    }

    #[Test]
    public function statistics_table_columns_have_correct_data_types(): void
    {
        $this->assertColumnHasDataType('statistics', 'id', ['bigint']);
        $this->assertColumnHasDataType('statistics', 'user_id', ['bigint']);

        // Integer columns
        $integerTypes = ['integer', 'int'];
        $this->assertColumnHasDataType('statistics', 'total_flashcards', $integerTypes);
        $this->assertColumnHasDataType('statistics', 'total_study_sessions', $integerTypes);
        $this->assertColumnHasDataType('statistics', 'total_correct_answers', $integerTypes);
        $this->assertColumnHasDataType('statistics', 'total_incorrect_answers', $integerTypes);

        // Datetime columns
        $dateTimeTypes = ['datetime', 'datetime_immutable'];
        $this->assertColumnHasDataType('statistics', 'created_at', $dateTimeTypes);
        $this->assertColumnHasDataType('statistics', 'updated_at', $dateTimeTypes);
    }

    #[Test]
    public function statistics_table_has_correct_foreign_keys(): void
    {
        $this->assertHasForeignKey('statistics', 'user_id', 'users', 'id');
    }

    #[Test]
    public function integer_columns_have_default_zero_values(): void
    {
        $this->assertColumnHasDefaultValue('statistics', 'total_flashcards', '0');
        $this->assertColumnHasDefaultValue('statistics', 'total_study_sessions', '0');
        $this->assertColumnHasDefaultValue('statistics', 'total_correct_answers', '0');
        $this->assertColumnHasDefaultValue('statistics', 'total_incorrect_answers', '0');
    }
}
