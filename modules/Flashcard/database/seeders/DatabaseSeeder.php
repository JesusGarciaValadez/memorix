<?php

declare(strict_types=1);

namespace Modules\Flashcard\database\seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            FlashcardSeeder::class,
            StudySessionSeeder::class,
            PracticeResultSeeder::class,
            StatisticSeeder::class,
            LogSeeder::class,
        ]);
    }
}
