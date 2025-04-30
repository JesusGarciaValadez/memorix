<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Models;

use App\Models\User;
use Carbon\CarbonImmutable;
use Eloquent;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Modules\Flashcard\database\factories\FlashcardFactory;

/**
 * @property int $id The unique identifier for the flashcard.
 * @property int $user_id The ID of the user who owns the flashcard.
 * @property string $question The question on the flashcard.
 * @property string $answer The answer to the flashcard question.
 * @property Carbon|CarbonImmutable $created_at Timestamp of creation.
 * @property Carbon|CarbonImmutable $updated_at Timestamp of last update.
 * @property Carbon|CarbonImmutable|null $deleted_at
 * @property-read User $user
 * @property-read Collection<int, PracticeResult> $practiceResults
 * @property-read int|null $practice_results_count
 *
 * @method static \Modules\Flashcard\database\factories\FlashcardFactory factory($count = null, $state = []) Creates a new factory instance for the model.
 * @method static Builder<Flashcard> newModelQuery() Creates a new query builder for the model.
 * @method static Builder<Flashcard> newQuery() Creates a new query builder for the model.
 * @method static Builder<Flashcard> query() Creates a new query builder for the model.
 * @method static Builder<Flashcard> whereAnswer($value)
 * @method static Builder<Flashcard> whereCreatedAt($value)
 * @method static Builder<Flashcard> whereDeletedAt($value)
 * @method static Builder<Flashcard> whereId($value)
 * @method static Builder<Flashcard> whereQuestion($value)
 * @method static Builder<Flashcard> whereUpdatedAt($value)
 * @method static Builder<Flashcard> whereUserId($value)
 * @method static Builder<Flashcard> onlyTrashed()
 * @method static Builder<Flashcard> withTrashed()
 * @method static Builder<Flashcard> withoutTrashed()
 * @method static Builder<Flashcard> forUser(int $userId, bool $withTrashed = false)
 *
 * @mixin Eloquent
 * @mixin IdeHelperFlashcard
 */
final class Flashcard extends Model
{
    /** @use HasFactory<\Modules\Flashcard\database\factories\FlashcardFactory> */
    use HasFactory, SoftDeletes;

    public $table = 'flashcards';

    /**
     * @var array<int, string>
     */
    protected static $columns = ['id', 'user_id', 'question', 'answer', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'question',
        'answer',
    ];

    /**
     * Get all flashcards for a specific user with pagination.
     *
     * @param  int  $userId  The ID of the user.
     * @param  int  $perPage  Number of items per page.
     * @return LengthAwarePaginator<int, Flashcard> Paginated flashcards.
     */
    public static function getAllForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return self::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all softly deleted flashcards for a specific user with pagination.
     *
     * @param  int  $userId  The ID of the user.
     * @param  int  $perPage  Number of items per page.
     * @return LengthAwarePaginator<int, Flashcard> Paginated softly deleted flashcards.
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
     * Restore all soft-deleted flashcards for a specific user.
     */
    public static function restoreAllForUser(int $userId): bool
    {
        return self::onlyTrashed()->where('user_id', $userId)->restore() > 0;
    }

    /**
     * Permanently delete all soft-deleted flashcards for a specific user.
     */
    public static function forceDeleteAllForUser(int $userId): bool
    {
        return self::onlyTrashed()->where('user_id', $userId)->forceDelete() > 0;
    }

    /**
     * Scope a query to only include flashcards for a given user.
     *
     * @param  Builder<Flashcard>  $query
     * @return Builder<Flashcard>
     */
    #[Scope]
    public function ForUser(Builder $query, int $userId, bool $withTrashed = false): Builder
    {
        $query->where('user_id', $userId);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query;
    }

    /**
     * Define the relationship with the User model.
     *
     * @return BelongsTo<User, Flashcard>
     */
    public function user(): BelongsTo
    {
        // @phpstan-ignore-next-line return.type
        return $this->belongsTo(User::class);
    }

    /**
     * Define the relationship with the PracticeResult model.
     *
     * @return HasMany<PracticeResult, Flashcard>
     */
    public function practiceResults(): HasMany
    {
        // @phpstan-ignore-next-line return.type
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
     *
     * @return FlashcardFactory The factory instance.
     */
    protected static function newFactory(): Factory
    {
        return FlashcardFactory::new();
    }
}
