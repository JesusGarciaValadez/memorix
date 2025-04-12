<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Flashcard\database\factories\StatisticFactory;

/**
 * @mixin IdeHelperStatistic
 */
final class Statistic extends Model
{
    /** @use HasFactory<StatisticFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'statistics';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
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

        if (! $statistics) {
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
        foreach ($studySessions as $session) {
            $startedAt = $session->started_at;
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
            ->sum(function ($session) {
                $startedAt = $session->started_at;
                $endedAt = $session->ended_at;

                if ($startedAt && $endedAt) {
                    return $endedAt->diffInSeconds($startedAt);
                }

                return 0;
            });

        return round($totalDuration / 60, 2); // Return in minutes
    }

    /**
     * Get the user that owns the statistics.
     */
    public function user(): BelongsTo
    {
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
     * Get the completion percentage (answered vs total).
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
     */
    protected static function newFactory(): Factory
    {
        return StatisticFactory::new();
    }
}
