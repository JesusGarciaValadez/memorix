<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Flashcard\database\factories\FlashcardFactory;

final class Flashcard extends Model
{
    /** @use HasFactory<FlashcardFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'question',
        'answer',
    ];

    /**
     * Get the user that owns the flashcard.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the practice results for this flashcard.
     */
    public function practiceResults(): HasMany
    {
        return $this->hasMany(PracticeResult::class);
    }

    /**
     * Check if this flashcard has been correctly answered in a study session.
     */
    public function isCorrectlyAnswered(): bool
    {
        // Implementation will depend on future study session tracking
        return false;
    }

    /**
     * Check if this flashcard has been incorrectly answered in a study session.
     */
    public function isIncorrectlyAnswered(): bool
    {
        // Implementation will depend on future study session tracking
        return false;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return FlashcardFactory::new();
    }
}
