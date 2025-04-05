<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Log extends Model
{
    use HasFactory;

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
        ?string $details = null
    ): self {
        return self::create([
            'user_id' => $user->id,
            'action' => $action,
            'details' => $details,
            'created_at' => now(),
        ]);
    }

    /**
     * Log a flashcard creation.
     */
    public static function logFlashcardCreation(User $user, Flashcard $flashcard): self
    {
        return self::createEntry(
            $user,
            'created_flashcard',
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
            "Deleted flashcard ID: {$flashcard->id}, Question: {$flashcard->question}"
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
            "Ended study session ID: {$session->id}"
        );
    }

    /**
     * Get the user that owns the log entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
