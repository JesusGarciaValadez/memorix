<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Statistic extends Model
{
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

        $totalAnswered = $this->total_correct_answers + $this->total_incorrect_answers;

        return ($totalAnswered / $this->total_flashcards) * 100;
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
}
