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
     * @var string
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
            'action' => fake()->randomElement([
                'created_flashcard',
                'deleted_flashcard',
                'started_study_session',
                'ended_study_session',
                'viewed_flashcard',
                'edited_flashcard',
            ]),
            'details' => fake()->sentence(10),
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Define a state for flashcard creation logs.
     */
    public function flashcardCreation(): Factory
    {
        return $this->state(function () {
            return [
                'action' => 'created_flashcard',
                'details' => 'Created flashcard ID: '.fake()->numberBetween(1, 100).
                    ', Question: '.fake()->sentence(6).'?',
            ];
        });
    }

    /**
     * Define a state for flashcard deletion logs.
     */
    public function flashcardDeletion(): Factory
    {
        return $this->state(function () {
            return [
                'action' => 'deleted_flashcard',
                'details' => 'Deleted flashcard ID: '.fake()->numberBetween(1, 100).
                    ', Question: '.fake()->sentence(6).'?',
            ];
        });
    }

    /**
     * Define a state for study session start logs.
     */
    public function studySessionStart(): Factory
    {
        return $this->state(function () {
            return [
                'action' => 'started_study_session',
                'details' => 'Started study session ID: '.fake()->numberBetween(1, 50),
            ];
        });
    }

    /**
     * Define a state for study session end logs.
     */
    public function studySessionEnd(): Factory
    {
        return $this->state(function () {
            return [
                'action' => 'ended_study_session',
                'details' => 'Ended study session ID: '.fake()->numberBetween(1, 50),
            ];
        });
    }

    /**
     * Define a state for recent logs.
     */
    public function recent(): Factory
    {
        return $this->state(function () {
            return [
                'created_at' => fake()->dateTimeBetween('-1 day', 'now'),
            ];
        });
    }
}
