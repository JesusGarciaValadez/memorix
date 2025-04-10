<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Flashcard\database\factories\LogFactory;

/**
 * @mixin IdeHelperLog
 */
final class Log extends Model
{
    /** @use HasFactory<LogFactory> */
    use HasFactory;

    public const LEVEL_DEBUG = 'debug';

    public const LEVEL_INFO = 'info';

    public const LEVEL_WARNING = 'warning';

    public const LEVEL_ERROR = 'error';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'level',
        'details',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Create a new log entry.
     */
    public static function createEntry(
        User $user,
        string $action,
        string $level = self::LEVEL_INFO,
        ?string $details = null
    ): self {
        return self::create([
            'user_id' => $user->id,
            'action' => $action,
            'level' => $level,
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
     */
    public static function logFlashcardCreation(User $user, Flashcard $flashcard): self
    {
        return self::createEntry(
            $user,
            'created_flashcard',
            self::LEVEL_INFO,
            "Created flashcard ID: {$flashcard->id}, Question: {$flashcard->question}"
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
     * Log user exit.
     */
    public static function logUserExit(User $user): self
    {
        return self::createEntry(
            $user,
            'user_exit',
            self::LEVEL_INFO,
            "User {$user->name} exited the application"
        );
    }

    /**
     * Get the user that owns the log entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return LogFactory::new();
    }
}
