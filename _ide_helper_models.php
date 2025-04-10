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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Flashcard\app\Models\Flashcard> $flashcards
 * @property-read int|null $flashcards_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Flashcard\app\Models\Log> $logs
 * @property-read int|null $logs_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Modules\Flashcard\app\Models\Statistic|null $statistic
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Flashcard\app\Models\StudySession> $studySessions
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
 * @property int $id
 * @property int $user_id
 * @property string $question
 * @property string $answer
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Flashcard\app\Models\PracticeResult> $practiceResults
 * @property-read int|null $practice_results_count
 * @property-read \App\Models\User $user
 * @method static \Modules\Flashcard\database\factories\FlashcardFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flashcard withoutTrashed()
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
 * @property string|null $details
 * @property \Carbon\CarbonImmutable $created_at
 * @property string $level
 * @property-read \App\Models\User $user
 * @method static \Modules\Flashcard\database\factories\LogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Log whereUserId($value)
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
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Modules\Flashcard\app\Models\Flashcard $flashcard
 * @property-read \Modules\Flashcard\app\Models\StudySession $studySession
 * @property-read \App\Models\User $user
 * @method static \Modules\Flashcard\database\factories\PracticeResultFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeResult query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeResult whereFlashcardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeResult whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeResult whereIsCorrect($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeResult whereStudySessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeResult whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeResult whereUserId($value)
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
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Modules\Flashcard\database\factories\StatisticFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic whereTotalCorrectAnswers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic whereTotalFlashcards($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic whereTotalIncorrectAnswers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic whereTotalStudySessions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistic whereUserId($value)
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
 * @property \Carbon\CarbonImmutable $started_at
 * @property \Carbon\CarbonImmutable|null $ended_at
 * @property-read \App\Models\User $user
 * @method static \Modules\Flashcard\database\factories\StudySessionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudySession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudySession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudySession query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudySession whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudySession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudySession whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudySession whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	final class IdeHelperStudySession {}
}

