<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

use App\Models\User;
use Illuminate\Console\Command;
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
    public function createFlashcard(User $user, FlashcardInteractiveCommand $command): void;

    /**
     * Delete a flashcard for a user.
     */
    public function deleteFlashcard(User $user, FlashcardInteractiveCommand $command): void;

    /**
     * Show statistics for a user.
     */
    public function showStatistics(User $user, FlashcardInteractiveCommand $command): void;

    /**
     * Reset practice data for a user.
     */
    public function resetPracticeData(User $user, FlashcardInteractiveCommand $command): void;

    /**
     * View logs for a user.
     */
    public function viewLogs(User $user, FlashcardInteractiveCommand $command): void;

    /**
     * Access the trash bin for a user.
     */
    public function accessTrashBin(User $user, FlashcardInteractiveCommand $command): void;

    /**
     * Log user exit.
     */
    public function logExit(User $user, FlashcardInteractiveCommand $command): void;

    /**
     * Register a new user.
     */
    public function registerUser(Command $command): User;

    /**
     * Practice flashcards for a user.
     */
    public function practiceFlashcards(User $user, FlashcardInteractiveCommand $command): void;

    /**
     * Import flashcards from a CSV file for a user.
     */
    public function importFlashcardsFromFile(int $userId, string $filePath): bool;
}
