<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Models;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Flashcard\database\factories\FlashcardFactory;

/**
 * @mixin IdeHelperFlashcard
 */
final class Flashcard extends Model
{
    /** @use HasFactory<FlashcardFactory> */
    use HasFactory;

    use SoftDeletes;

    public $table = 'flashcards';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'question',
        'answer',
    ];

    /**
     * Get all flashcards for a user.
     */
    public static function getAllForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all deleted flashcards for a user.
     */
    public static function getAllDeletedForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return self::onlyTrashed()
            ->where('user_id', $userId)
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find a flashcard for a user.
     */
    public static function findForUser(int $flashcardId, int $userId, bool $withTrashed = false): ?self
    {
        $query = self::where('id', $flashcardId)
            ->where('user_id', $userId);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->first();
    }

    /**
     * Restore all deleted flashcards for a user.
     */
    public static function restoreAllForUser(int $userId): bool
    {
        $result = self::onlyTrashed()
            ->where('user_id', $userId)
            ->restore();

        return $result > 0;
    }

    /**
     * Permanently delete all flashcards for a user.
     */
    public static function forceDeleteAllForUser(int $userId): bool
    {
        $result = self::where('user_id', $userId)
            ->forceDelete();

        return $result > 0;
    }

    /**
     * Scope a query to only include flashcards for a specific user.
     */
    public static function forUser(int $userId)
    {
        return self::where('user_id', $userId);
    }

    /**
     * Get the user that owns the flashcard.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the practice results for this flashcard.
     */
    public function practiceResults(): HasMany
    {
        return $this->hasMany(PracticeResult::class);
    }

    /**
     * Check if this flashcard has been correctly answered in a study session.
     */
    public function isCorrectlyAnswered(): bool
    {
        return $this->practiceResults()
            ->where('is_correct', true)
            ->exists();
    }

    /**
     * Check if this flashcard has been incorrectly answered in a study session.
     */
    public function isIncorrectlyAnswered(): bool
    {
        return $this->practiceResults()
            ->where('is_correct', false)
            ->exists();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return FlashcardFactory::new();
    }
}
