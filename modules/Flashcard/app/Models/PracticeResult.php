<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Models;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Flashcard\database\factories\PracticeResultFactory;

/**
 * @property int $id
 * @property int $user_id
 * @property int $flashcard_id
 * @property int $study_session_id
 * @property bool $is_correct
 * @property Carbon|CarbonImmutable|null $practiced_at*
 * @property Carbon|CarbonImmutable|null $created_at*
 * @property Carbon|CarbonImmutable|null $updated_at
 * @property-read Flashcard $flashcard
 * @property-read StudySession $studySession
 * @property-read User $user
 *
 * @method static PracticeResultFactory factory($count = null, $state = [])
 * @method static Builder<PracticeResult> newModelQuery()
 * @method static Builder<PracticeResult> newQuery()
 * @method static Builder<PracticeResult> query()
 * @method static Builder<PracticeResult> whereCreatedAt($value)
 * @method static Builder<PracticeResult> whereFlashcardId($value)
 * @method static Builder<PracticeResult> whereId($value)
 * @method static Builder<PracticeResult> whereIsCorrect($value)
 * @method static Builder<PracticeResult> whereStudySessionId($value)
 * @method static Builder<PracticeResult> whereUpdatedAt($value)
 * @method static Builder<PracticeResult> whereUserId($value)
 *
 * @mixin IdeHelperPracticeResult
 */
final class PracticeResult extends Model
{
    /** @use HasFactory<PracticeResultFactory> */
    use HasFactory;

    use HasTimestamps;

    /**
     * @var array<int, string>
     */
    protected static $columns = ['id', 'user_id', 'flashcard_id', 'study_session_id', 'is_correct', 'created_at', 'updated_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'flashcard_id',
        'study_session_id',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the practice result.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        // @phpstan-ignore-next-line return.type
        return $this->belongsTo(User::class);
    }

    /**
     * Get the flashcard associated with the practice result.
     *
     * @return BelongsTo<Flashcard, self>
     */
    public function flashcard(): BelongsTo
    {
        // @phpstan-ignore-next-line return.type
        return $this->belongsTo(Flashcard::class);
    }

    /**
     * Get the study session associated with the practice result.
     *
     * @return BelongsTo<StudySession, self>
     */
    public function studySession(): BelongsTo
    {
        // @phpstan-ignore-next-line return.type
        return $this->belongsTo(StudySession::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): PracticeResultFactory
    {
        return PracticeResultFactory::new();
    }
}
