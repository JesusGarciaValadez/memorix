<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Flashcard\database\factories\StudySessionFactory;

/**
 * @mixin IdeHelperStudySession
 */
final class StudySession extends Model
{
    /** @use HasFactory<StudySessionFactory> */
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The columns that should be returned.
     *
     * @var array|null
     */
    public $columns = [
        'user_id',
        'starter_at',
        'ended_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'started_at',
        'ended_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Get the user that owns the study session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
     */
    protected static function newFactory(): Factory
    {
        return StudySessionFactory::new();
    }
}
