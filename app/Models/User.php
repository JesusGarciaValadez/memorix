<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use AllowDynamicProperties;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\Log;
use Modules\Flashcard\app\Models\Statistic;
use Modules\Flashcard\app\Models\StudySession;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Carbon\CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Flashcard> $flashcards
 * @property-read int|null $flashcards_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Log> $logs
 * @property-read int|null $logs_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Statistic|null $statistic
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StudySession> $studySessions
 * @property-read int|null $study_sessions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 * @mixin IdeHelperUser
 */
#[AllowDynamicProperties]
final class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the flashcards associated with the user.
     *
     * @phpstan-return HasMany<Flashcard, User>
     */
    public function flashcards(): HasMany
    {
        /** @var HasMany<Flashcard, User> $relation */
        $relation = $this->hasMany(Flashcard::class);

        return $relation;
    }

    /**
     * Get the study sessions associated with the user.
     *
     * @phpstan-return HasMany<StudySession, User>
     */
    public function studySessions(): HasMany
    {
        /** @var HasMany<StudySession, User> $relation */
        $relation = $this->hasMany(StudySession::class);

        return $relation;
    }

    /**
     * Get the user's statistics.
     *
     * @phpstan-return HasOne<Statistic, User>
     */
    public function statistic(): HasOne
    {
        /** @var HasOne<Statistic, User> $relation */
        $relation = $this->hasOne(Statistic::class);

        return $relation;
    }

    /**
     * Get the logs associated with the user.
     *
     * @phpstan-return HasMany<Log, User>
     */
    public function logs(): HasMany
    {
        /** @var HasMany<Log, User> $relation */
        $relation = $this->hasMany(Log::class);

        return $relation;
    }

    /**
     * Get the active study session for the user.
     */
    public function getActiveStudySession(): ?StudySession
    {
        return $this->studySessions()
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
