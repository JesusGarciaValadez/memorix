<?php

declare(strict_types=1);

namespace Modules\Flashcard\database\factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\PracticeResult;
use Modules\Flashcard\app\Models\StudySession;

/**
 * @extends Factory<PracticeResult>
 */
final class PracticeResultFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PracticeResult::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'flashcard_id' => Flashcard::factory(),
            'study_session_id' => StudySession::factory(),
            'is_correct' => fake()->boolean(70), // 70% chance of being correct
            'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'updated_at' => fn (array $attributes) => fake()->dateTimeBetween($attributes['created_at'], 'now'),
        ];
    }

    /**
     * Define a state for correct answers.
     */
    public function correct(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'is_correct' => true,
        ]);
    }

    /**
     * Define a state for incorrect answers.
     */
    public function incorrect(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'is_correct' => false,
        ]);
    }

    /**
     * Define a state for recent practice results.
     */
    public function recent(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'created_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'updated_at' => fn (array $attributes) => fake()->dateTimeBetween($attributes['created_at'], 'now'),
        ]);
    }
}
