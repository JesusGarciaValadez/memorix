<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
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
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	final class IdeHelperUser {}
}

namespace Modules\Flashcard\app\Models{
/**
 * 
 *
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
 * @mixin Eloquent
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	final class IdeHelperFlashcard {}
}

namespace Modules\Flashcard\app\Models{
/**
 * 
 *
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
 * @mixin Model
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	final class IdeHelperLog {}
}

namespace Modules\Flashcard\app\Models{
/**
 * 
 *
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
 * @property \Carbon\CarbonImmutable|null $created_at
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	final class IdeHelperPracticeResult {}
}

namespace Modules\Flashcard\app\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $total_flashcards
 * @property int $total_study_sessions
 * @property int $total_correct_answers
 * @property int $total_incorrect_answers
 * @property Carbon|CarbonImmutable|null $created_at
 * @property Carbon|CarbonImmutable|null $updated_at
 * @property-read User|null $user
 * @method static StatisticFactory factory($count = null, $state = [])
 * @method static Builder<Statistic> newModelQuery()
 * @method static Builder<Statistic> newQuery()
 * @method static Builder<Statistic> query()
 * @method static Builder<Statistic> whereCreatedAt($value)
 * @method static Builder<Statistic> whereId($value)
 * @method static Builder<Statistic> whereTotalCorrectAnswers($value)
 * @method static Builder<Statistic> whereTotalFlashcards($value)
 * @method static Builder<Statistic> whereTotalIncorrectAnswers($value)
 * @method static Builder<Statistic> whereTotalStudySessions($value)
 * @method static Builder<Statistic> whereUpdatedAt($value)
 * @method static Builder<Statistic> whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	final class IdeHelperStatistic {}
}

namespace Modules\Flashcard\app\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon|CarbonImmutable|CarbonInterface|null $started_at
 * @property Carbon|CarbonImmutable|CarbonInterface|null $ended_at
 * @property Carbon|CarbonImmutable|CarbonInterface|null $created_at
 * @property Carbon|CarbonImmutable|CarbonInterface|null $updated_at
 * @property-read EloquentCollection<int, PracticeResult> $practiceResults
 * @property-read int|null $practice_results_count
 * @property-read User $user
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
 * @mixin Model
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	final class IdeHelperStudySession {}
}

