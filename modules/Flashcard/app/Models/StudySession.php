<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Models;

use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Flashcard\database\factories\StudySessionFactory;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon|CarbonImmutable|CarbonInterface|null $started_at
 * @property Carbon|CarbonImmutable|CarbonInterface|null $ended_at
 * @property Carbon|CarbonImmutable|CarbonInterface|null $created_at
 * @property Carbon|CarbonImmutable|CarbonInterface|null $updated_at
 * @property-read EloquentCollection<int, PracticeResult> $practiceResults
 * @property-read int|null $practice_results_count
 * @property-read User $user
 *
 * @method static StudySessionFactory factory($count = null, $state = [])
 * @method static Builder<StudySession> newModelQuery()
 * @method static Builder<StudySession> newQuery()
 * @method static Builder<StudySession> query()
 * @method static Builder<StudySession> whereCreatedAt($value)
 * @method static Builder<StudySession> whereEndedAt($value)
 * @method static Builder<StudySession> whereId($value)
 * @method static Builder<StudySession> whereStartedAt($value)
 * @method static Builder<StudySession> whereUpdatedAt($value)
 * @method static Builder<StudySession> whereUserId($value)
 *
 * @mixin Model
 * @mixin IdeHelperStudySession
 */
final class StudySession extends Model
{
    /** @use HasFactory<\Modules\Flashcard\database\factories\StudySessionFactory> */
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected static $columns = ['id', 'user_id', 'started_at', 'ended_at', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
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
        'started_at',
        'ended_at',
    ];

    /**
     * Get the user that owns the study session.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        // @phpstan-ignore-next-line return.type
        return $this->belongsTo(User::class);
    }

    /**
     * Get the practice results for this study session.
     *
     * @return HasMany<PracticeResult, StudySession>
     */
    public function practiceResults(): HasMany
    {
        // @phpstan-ignore-next-line return.type
        return $this->hasMany(PracticeResult::class);
    }

    /**
     * Check if the study session has ended.
     */
    public function isEnded(): bool
    {
        return (bool) $this->ended_at;
    }

    /**
     * End the study session.
     */
    public function end(): self
    {
        $this->ended_at = now();
        $this->save();

        return $this;
    }

    /**
     * Check if the study session is active.
     */
    public function isActive(): bool
    {
        return ! (bool) $this->ended_at;
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return StudySessionFactory
     */
    protected static function newFactory(): Factory
    {
        return StudySessionFactory::new();
    }
}
