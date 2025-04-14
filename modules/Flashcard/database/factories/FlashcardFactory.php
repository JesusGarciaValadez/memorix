<?php

declare(strict_types=1);

namespace Modules\Flashcard\database\factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Flashcard\app\Models\Flashcard;
use Random\RandomException;

/**
 * @extends Factory<Flashcard>
 */
final class FlashcardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Flashcard::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     *
     * @throws RandomException
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'question' => fake()->sentence(random_int(5, 10)).'?',
            'answer' => fake()->paragraph(random_int(1, 3)),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fn (array $attributes) => fake()->dateTimeBetween($attributes['created_at'], 'now'),
        ];
    }

    /**
     * Define a state for flashcards with short answers.
     */
    public function shortAnswer(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'answer' => fake()->sentence(random_int(1, 5)),
        ]);
    }

    /**
     * Define a state for recently created flashcards.
     */
    public function recent(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'created_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'updated_at' => fn (array $attributes) => fake()->dateTimeBetween($attributes['created_at'], 'now'),
        ]);
    }
}
