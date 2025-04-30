<?php

declare(strict_types=1);

namespace Modules\Flashcard\database\factories;

use App\Models\User;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use InvalidArgumentException;
use Modules\Flashcard\app\Models\StudySession;

/**
 * @extends Factory<StudySession>
 */
final class StudySessionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<StudySession>
     */
    protected $model = StudySession::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-30 days', 'now');

        return [
            'user_id' => User::factory(),
            'started_at' => $startedAt,
            'ended_at' => fake()->dateTimeBetween($startedAt, 'now'),
        ];
    }

    /**
     * Define a state for active study sessions.
     */
    public function active(): self
    {
        return $this->state([
            'ended_at' => null,
        ]);
    }

    /**
     * Define a state for completed study sessions.
     */
    public function completed(): self
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
    public function recent(): static
    {
        return $this->state(fn (): array => [
            'started_at' => fake()->dateTimeBetween('-2 days', 'now'),
        ]);
    }

    /**
     * Define a state for short study sessions (less than 10 minutes).
     */
    public function shortSession(): static
    {
        /**
         * @param  array{started_at: DateTimeInterface|string}  $attributes
         */
        return $this->state(function (array $attributes): array {
            $startTime = $attributes['started_at'];
            if (! $startTime instanceof DateTimeInterface) {
                throw new InvalidArgumentException('started_at attribute must be a DateTimeInterface instance.');
            }
            $startedAt = Carbon::parse($startTime);
            $minutesToAdd = random_int(1, 10);

            return [
                'ended_at' => (clone $startedAt)->addMinutes($minutesToAdd),
            ];
        });
    }
}
