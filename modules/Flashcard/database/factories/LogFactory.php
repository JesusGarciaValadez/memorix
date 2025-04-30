<?php

declare(strict_types=1);

namespace Modules\Flashcard\database\factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Flashcard\app\Models\Log;

/**
 * @extends Factory<Log>
 */
final class LogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Log>
     */
    protected $model = Log::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => $this->faker->word(),
            'level' => $this->faker->randomElement([Log::LEVEL_INFO, Log::LEVEL_WARNING, Log::LEVEL_ERROR, Log::LEVEL_DEBUG]),
            'description' => $this->faker->sentence(),
            'details' => null,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function info(): self
    {
        return $this->state(['level' => Log::LEVEL_INFO]);
    }

    public function warning(): self
    {
        return $this->state(['level' => Log::LEVEL_WARNING]);
    }

    public function error(): self
    {
        return $this->state(['level' => Log::LEVEL_ERROR]);
    }

    public function debug(): self
    {
        return $this->state(['level' => Log::LEVEL_DEBUG]);
    }

    /**
     * @param  array<mixed>|null  $details
     */
    public function withDetails(?array $details): self
    {
        return $this->state(['details' => $details !== null && $details !== [] ? json_encode($details, JSON_THROW_ON_ERROR) : null]);
    }

    /**
     * Define a state for flashcard creation logs.
     */
    public function flashcardCreation(): static
    {
        return $this->state(fn (): array => [
            'action' => 'created_flashcard',
            'description' => 'Created flashcard ID: '.fake()->numberBetween(1, 100).
                ', Question: '.fake()->sentence(6).'?',
            'details' => null,
        ]);
    }

    /**
     * Define a state for flashcard deletion logs.
     */
    public function flashcardDeletion(): static
    {
        return $this->state(fn (): array => [
            'action' => 'deleted_flashcard',
            'description' => 'Deleted flashcard ID: '.fake()->numberBetween(1, 100).
                ', Question: '.fake()->sentence(6).'?',
            'details' => [
                'flashcard_id' => fake()->numberBetween(1, 100),
            ],
        ]);
    }

    /**
     * Define a state for study session start logs.
     */
    public function studySessionStart(): static
    {
        return $this->state(fn (): array => [
            'action' => 'started_study_session',
            'description' => 'Started study session ID: '.fake()->numberBetween(1, 50),
        ]);
    }

    /**
     * Define a state for study session end logs.
     */
    public function studySessionEnd(): static
    {
        return $this->state(fn (): array => [
            'action' => 'ended_study_session',
            'description' => 'Ended study session ID: '.fake()->numberBetween(1, 50),
        ]);
    }

    /**
     * Define a state for recent logs.
     */
    public function recent(): static
    {
        return $this->state(fn (): array => [
            'created_at' => fake()->dateTimeBetween('-1 day', 'now'),
        ]);
    }
}
