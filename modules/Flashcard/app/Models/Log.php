<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Models;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JsonException;
use Modules\Flashcard\database\factories\LogFactory;

/**
 * @property int $id
 * @property int $user_id
 * @property string $action
 * @property string $level
 * @property string|null $description
 * @property Collection<int, mixed>|null $details
 * @property Carbon|CarbonImmutable|null $created_at
 * @property Carbon|CarbonImmutable|null $updated_at
 * @property-read User $user
 * @property array<string, mixed> $attributes
 *
 * @method static LogFactory factory($count = null, $state = [])
 * @method static Builder<Log> newModelQuery()
 * @method static Builder<Log> newQuery()
 * @method static Builder<Log> query()
 * @method static Builder<Log> whereAction($value)
 * @method static Builder<Log> whereCreatedAt($value)
 * @method static Builder<Log> whereDescription($value)
 * @method static Builder<Log> whereDetails($value)
 * @method static Builder<Log> whereId($value)
 * @method static Builder<Log> whereLevel($value)
 * @method static Builder<Log> whereUpdatedAt($value)
 * @method static Builder<Log> whereUserId($value)
 *
 * @mixin Model
 * @mixin IdeHelperLog
 */
final class Log extends Model
{
    /** @use HasFactory<\Modules\Flashcard\database\factories\LogFactory> */
    use HasFactory;

    public const string LEVEL_DEBUG = 'debug';

    public const string LEVEL_INFO = 'info';

    public const string LEVEL_WARNING = 'warning';

    public const string LEVEL_ERROR = 'error';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The columns that should be returned.
     *
     * @var array<int, string>
     */
    protected static $columns = [
        'id',
        'user_id',
        'action',
        'level',
        'description',
        'details',
        'created_at',
    ];

    /**
     * The model's attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'user_id' => null,
        'action' => '',
        'level' => self::LEVEL_INFO,
        'description' => null,
        'details' => null,
        'created_at' => null,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'level',
        'description',
        'details',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'details' => AsCollection::class,
        'created_at' => 'datetime',
    ];

    /**
     * Create a new log entry.
     */
    public static function createEntry(
        User $user,
        string $action,
        string $level,
        string $description,
        ?string $details = null
    ): self {
        return self::create([
            'user_id' => $user->id,
            'action' => $action,
            'level' => $level,
            'description' => $description,
            'details' => $details,
            'created_at' => now(),
        ]);
    }

    /**
     * Log a user login.
     */
    public static function logUserLogin(User $user): self
    {
        return self::createEntry(
            $user,
            'user_login',
            self::LEVEL_INFO,
            "User {$user->name} logged in"
        );
    }

    /**
     * Log a flashcard creation.
     *
     * @throws JsonException
     */
    public static function logFlashcardCreation(User $user, Flashcard $flashcard): self
    {
        return self::createEntry(
            $user,
            'created_flashcard',
            self::LEVEL_INFO,
            "Created flashcard ID: {$flashcard->id}, Question: {$flashcard->question}",
            json_encode(['flashcard_id' => $flashcard->id], JSON_THROW_ON_ERROR),
        );
    }

    /**
     * Log a flashcard deletion.
     */
    public static function logFlashcardDeletion(User $user, Flashcard $flashcard): self
    {
        return self::createEntry(
            $user,
            'deleted_flashcard',
            self::LEVEL_WARNING,
            "Deleted flashcard ID: {$flashcard->id}, Question: {$flashcard->question}"
        );
    }

    /**
     * Log a flashcard list view.
     */
    public static function logFlashcardList(User $user): self
    {
        return self::createEntry(
            $user,
            'viewed_flashcard_list',
            self::LEVEL_DEBUG,
            'User viewed flashcard list'
        );
    }

    /**
     * Log a study session start.
     */
    public static function logStudySessionStart(User $user, StudySession $session): self
    {
        return self::createEntry(
            $user,
            'started_study_session',
            self::LEVEL_INFO,
            "Started study session ID: {$session->id}"
        );
    }

    /**
     * Log a study session end.
     */
    public static function logStudySessionEnd(User $user, StudySession $session): self
    {
        return self::createEntry(
            $user,
            'ended_study_session',
            self::LEVEL_INFO,
            "Ended study session ID: {$session->id}"
        );
    }

    /**
     * Log a practice answer.
     */
    public static function logPracticeAnswer(User $user, Flashcard $flashcard, bool $isCorrect): self
    {
        return self::createEntry(
            $user,
            $isCorrect ? 'flashcard_answered_correctly' : 'flashcard_answered_incorrectly',
            $isCorrect ? self::LEVEL_INFO : self::LEVEL_WARNING,
            "Answered flashcard ID: {$flashcard->id}, Result: ".($isCorrect ? 'Correct' : 'Incorrect')
        );
    }

    /**
     * Log statistics view.
     */
    public static function logStatisticsView(User $user): self
    {
        return self::createEntry(
            $user,
            'statistics_viewed',
            self::LEVEL_DEBUG,
            'User viewed statistics'
        );
    }

    /**
     * Log practice reset.
     */
    public static function logPracticeReset(User $user): self
    {
        return self::createEntry(
            $user,
            'practice_reset',
            self::LEVEL_WARNING,
            'User reset practice progress'
        );
    }

    /**
     * Get the user that owns the log.
     *
     * @return BelongsTo<User, Log>
     */
    public function user(): BelongsTo
    {
        // @phpstan-ignore-next-line return.type
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return LogFactory
     */
    protected static function newFactory(): Factory
    {
        return LogFactory::new();
    }
}
