<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

use App\Models\User;
use Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand;

interface FlashcardCommandServiceInterface
{
    /**
     * List all flashcards for a user.
     */
    public function listFlashcards(User $user, FlashcardInteractiveCommand $command): void;

    /**
     * Create a new flashcard for a user.
     */
    public function createFlashcard(User $user): void;

    /**
     * Delete a flashcard for a user.
     */
    public function deleteFlashcard(User $user): void;

    /**
     * Show statistics for a user.
     */
    public function showStatistics(User $user): void;

    /**
     * Reset practice data for a user.
     */
    public function resetPracticeData(User $user): void;

    /**
     * View logs for a user.
     */
    public function viewLogs(User $user): void;

    /**
     * Access the trash bin for a user.
     */
    public function accessTrashBin(User $user): void;

    /**
     * Log user exit.
     */
    public function logExit(User $user): void;

    /**
     * Register a new user.
     */
    public function registerUser(): User;

    /**
     * Practice flashcards for a user.
     */
    public function practiceFlashcards(User $user): void;

    /**
     * Import flashcards from a CSV file for a user.
     */
    public function importFlashcardsFromFile(int $userId, string $filePath): bool;
}
