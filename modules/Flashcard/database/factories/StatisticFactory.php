<?php

declare(strict_types=1);

namespace Modules\Flashcard\database\factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Flashcard\app\Models\Statistic;

/**
 * @extends Factory<Statistic>
 */
final class StatisticFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Statistic::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'total_flashcards' => fake()->numberBetween(10, 100),
            'total_study_sessions' => fake()->numberBetween(1, 30),
            'total_correct_answers' => fake()->numberBetween(5, 50),
            'total_incorrect_answers' => fake()->numberBetween(5, 50),
        ];
    }

    /**
     * Define a state for statistics with a high success rate.
     */
    public function highSuccess(): Factory
    {
        return $this->state(function (array $attributes) {
            $totalAnswers = 100;
            $correctAnswers = random_int(80, 95); // 80-95% correct

            return [
                'total_flashcards' => $totalAnswers,
                'total_correct_answers' => $correctAnswers,
                'total_incorrect_answers' => $totalAnswers - $correctAnswers,
            ];
        });
    }

    /**
     * Define a state for statistics with a low success rate.
     */
    public function lowSuccess(): Factory
    {
        return $this->state(function (array $attributes) {
            $totalAnswers = 100;
            $correctAnswers = random_int(20, 40); // 20-40% correct

            return [
                'total_flashcards' => $totalAnswers,
                'total_correct_answers' => $correctAnswers,
                'total_incorrect_answers' => $totalAnswers - $correctAnswers,
            ];
        });
    }

    /**
     * Define a state for new user statistics (minimal activity).
     */
    public function newUser(): Factory
    {
        return $this->state(function () {
            $totalFlashcards = random_int(1, 5);
            $totalStudySessions = random_int(0, 2);
            $totalCorrectAnswers = random_int(0, 3);
            $totalIncorrectAnswers = random_int(0, 2);

            return [
                'total_flashcards' => $totalFlashcards,
                'total_study_sessions' => $totalStudySessions,
                'total_correct_answers' => $totalCorrectAnswers,
                'total_incorrect_answers' => $totalIncorrectAnswers,
            ];
        });
    }

    /**
     * Define a state for power user statistics (high activity).
     */
    public function powerUser(): Factory
    {
        return $this->state(function () {
            $totalFlashcards = random_int(200, 500);
            $totalStudySessions = random_int(50, 100);
            $totalCorrectAnswers = random_int(150, 400);
            $totalIncorrectAnswers = random_int(50, 100);

            return [
                'total_flashcards' => $totalFlashcards,
                'total_study_sessions' => $totalStudySessions,
                'total_correct_answers' => $totalCorrectAnswers,
                'total_incorrect_answers' => $totalIncorrectAnswers,
            ];
        });
    }
}
