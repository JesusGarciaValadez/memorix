<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Models;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Flashcard\database\factories\StatisticFactory;

/**
 * @property int $id
 * @property int $user_id
 * @property int $total_flashcards
 * @property int $total_study_sessions
 * @property int $total_correct_answers
 * @property int $total_incorrect_answers
 * @property Carbon|CarbonImmutable|null $created_at
 * @property Carbon|CarbonImmutable|null $updated_at
 * @property-read User|null $user
 *
 * @method static StatisticFactory factory($count = null, $state = [])
 * @method static Builder<Statistic> newModelQuery()
 * @method static Builder<Statistic> newQuery()
 * @method static Builder<Statistic> query()
 * @method static Builder<Statistic> whereCreatedAt($value)
 * @method static Builder<Statistic> whereId($value)
 * @method static Builder<Statistic> whereTotalCorrectAnswers($value)
 * @method static Builder<Statistic> whereTotalFlashcards($value)
 * @method static Builder<Statistic> whereTotalIncorrectAnswers($value)
 * @method static Builder<Statistic> whereTotalStudySessions($value)
 * @method static Builder<Statistic> whereUpdatedAt($value)
 * @method static Builder<Statistic> whereUserId($value)
 *
 * @mixin IdeHelperStatistic
 */
final class Statistic extends Model
{
    /** @use HasFactory<\Modules\Flashcard\database\factories\StatisticFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected static $columns = ['id', 'user_id', 'total_flashcards', 'total_study_sessions', 'total_practice_results', 'correct_practice_results', 'incorrect_practice_results', 'average_score', 'last_studied_at', 'created_at', 'updated_at'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'statistics';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'total_flashcards' => 'integer',
        'total_study_sessions' => 'integer',
        'total_correct_answers' => 'integer',
        'total_incorrect_answers' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'total_flashcards',
        'total_study_sessions',
        'total_correct_answers',
        'total_incorrect_answers',
    ];

    /**
     * Get statistics for a user.
     */
    public static function getForUser(int $userId): ?self
    {
        return self::where('user_id', $userId)->first();
    }

    /**
     * Create statistics for a user.
     */
    public static function createForUser(int $userId): self
    {
        return self::create([
            'user_id' => $userId,
            'total_flashcards' => 0,
            'total_study_sessions' => 0,
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
        ]);
    }

    /**
     * Reset practice statistics for a user.
     */
    public static function resetPracticeStats(int $userId): bool
    {
        $statistics = self::getForUser($userId);

        if (! $statistics instanceof self) {
            return false;
        }

        return $statistics->update([
            'total_correct_answers' => 0,
            'total_incorrect_answers' => 0,
        ]);
    }

    /**
     * Get average study session duration for a user.
     */
    public static function getAverageStudySessionDuration(int $userId): float
    {
        $studySessions = StudySession::where('user_id', $userId)
            ->whereNotNull('ended_at')
            ->get();

        if ($studySessions->isEmpty()) {
            return 0.0;
        }

        $totalDuration = 0;
        /* @var StudySession $session */
        foreach ($studySessions as $session) {
            /* @var Carbon|CarbonImmutable|null $startedAt */
            $startedAt = $session->started_at;
            /* @var Carbon|CarbonImmutable|null $endedAt */
            $endedAt = $session->ended_at;

            if ($startedAt && $endedAt) {
                $totalDuration += $endedAt->diffInSeconds($startedAt);
            }
        }

        return round($totalDuration / $studySessions->count() / 60, 2); // Return in minutes
    }

    /**
     * Get total study time for a user.
     */
    public static function getTotalStudyTime(int $userId): float
    {
        $totalDuration = StudySession::where('user_id', $userId)
            ->whereNotNull('ended_at')
            ->get()
            ->sum(function (StudySession $session) {
                /* @var Carbon|CarbonImmutable|null $startedAt */
                $startedAt = $session->started_at;
                /* @var Carbon|CarbonImmutable|null $endedAt */
                $endedAt = $session->ended_at;

                if ($startedAt && $endedAt) {
                    return $endedAt->diffInSeconds($startedAt);
                }

                return 0;
            });

        return round($totalDuration / 60, 2); // Return in minutes
    }

    /**
     * Get the user associated with the statistic.
     *
     * @return BelongsTo<User, Statistic>
     */
    public function user(): BelongsTo
    {
        // @phpstan-ignore-next-line return.type
        return $this->belongsTo(User::class);
    }

    /**
     * Get the percentage of correctly answered flashcards.
     */
    public function getCorrectPercentage(): float
    {
        $total = $this->total_correct_answers + $this->total_incorrect_answers;

        if ($total === 0) {
            return 0.0;
        }

        return ($this->total_correct_answers / $total) * 100;
    }

    /**
     * Get the completion percentage (answered vs. total).
     */
    public function getCompletionPercentage(): float
    {
        if ($this->total_flashcards === 0) {
            return 0.0;
        }

        // Get the count of unique flashcards that have been practiced
        $uniqueFlashcardsPracticed = PracticeResult::where('user_id', $this->user_id)
            ->distinct()
            ->count('flashcard_id');

        // Calculate percentage and ensure it doesn't exceed 100%
        return min(($uniqueFlashcardsPracticed / $this->total_flashcards) * 100, 100.0);
    }

    /**
     * Increment the total flashcards count.
     */
    public function incrementTotalFlashcards(int $count = 1): self
    {
        $this->total_flashcards += $count;
        $this->save();

        return $this;
    }

    /**
     * Increment the total study sessions count.
     */
    public function incrementTotalStudySessions(int $count = 1): self
    {
        $this->total_study_sessions += $count;
        $this->save();

        return $this;
    }

    /**
     * Increment the total correct answers count.
     */
    public function incrementTotalCorrectAnswers(int $count = 1): self
    {
        $this->total_correct_answers += $count;
        $this->save();

        return $this;
    }

    /**
     * Increment the total incorrect answers count.
     */
    public function incrementTotalIncorrectAnswers(int $count = 1): self
    {
        $this->total_incorrect_answers += $count;
        $this->save();

        return $this;
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return StatisticFactory
     */
    protected static function newFactory(): Factory
    {
        return StatisticFactory::new();
    }
}
