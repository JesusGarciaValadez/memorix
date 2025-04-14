<?php

declare(strict_types=1);

namespace Modules\Flashcard\database\factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Flashcard\app\Models\StudySession;

/**
 * @extends Factory<StudySession>
 */
final class StudySessionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StudySession::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'started_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'ended_at' => null,
        ];
    }

    /**
     * Define a state for completed study sessions.
     */
    public function completed(): Factory
    {
        return $this->state(function (array $attributes): array {
            $startedAt = $attributes['started_at'];

            return [
                'ended_at' => fake()->dateTimeBetween($startedAt, now()),
            ];
        });
    }

    /**
     * Define a state for recent study sessions.
     */
    public function recent(): Factory
    {
        return $this->state(fn (): array => [
            'started_at' => fake()->dateTimeBetween('-2 days', 'now'),
        ]);
    }

    /**
     * Define a state for short study sessions (less than 10 minutes).
     */
    public function shortSession(): Factory
    {
        return $this->state(function (array $attributes): array {
            $startedAt = $attributes['started_at'];
            $minutesToAdd = random_int(1, 10);

            return [
                'ended_at' => (clone $startedAt)->modify("+{$minutesToAdd} minutes"),
            ];
        });
    }
}
