<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Services;

use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;

final readonly class StudySessionService
{
    public function __construct(
        private StudySessionRepositoryInterface $studySessionRepository,
        private FlashcardRepositoryInterface $flashcardRepository,
        private LogServiceInterface $logService,
        private StatisticServiceInterface $statisticService,
    ) {}

    /**
     * Start a new study session for a user.
     */
    public function startSession(int $userId): StudySession
    {
        // Start a new session
        $session = $this->studySessionRepository->startSession($userId);

        // Log the action
        $this->logService->logStudySessionStart($userId, $session);

        // Update statistics
        $this->statisticService->incrementStudySessions($userId);

        return $session;
    }

    /**
     * End a study session.
     */
    public function endSession(int $userId, int $sessionId): bool
    {
        $session = $this->studySessionRepository->findForUser($sessionId, $userId);

        if (! $session instanceof StudySession || $session->ended_at !== null) {
            return false;
        }

        // End the session
        $result = $this->studySessionRepository->endSession($session);

        // Log the action
        if ($result) {
            $this->logService->logStudySessionEnd($userId, $session);
        }

        return $result;
    }

    /**
     * Get flashcards for practice.
     */
    public function getFlashcardsForPractice(int $userId): array
    {
        // Ensure we have an active session
        $activeSession = $this->studySessionRepository->getActiveSessionForUser($userId);
        if (! $activeSession instanceof StudySession) {
            $this->startSession($userId);
        }

        // Get flashcards for practice
        return $this->studySessionRepository->getFlashcardsForPractice($userId);
    }

    /**
     * Record a practice result.
     */
    public function recordPracticeResult(int $userId, int $flashcardId, bool $isCorrect): bool
    {
        // Get the flashcard
        $flashcard = $this->flashcardRepository->findForUser($flashcardId, $userId);

        if (! $flashcard instanceof Flashcard) {
            return false;
        }

        // Record the result
        $result = $this->studySessionRepository->recordPracticeResult($userId, $flashcardId, $isCorrect);

        if ($result) {
            // Log the action
            $this->logService->logFlashcardPractice($userId, $flashcard, $isCorrect);

            // Update statistics
            if ($isCorrect) {
                $this->statisticService->incrementCorrectAnswers($userId);
            } else {
                $this->statisticService->incrementIncorrectAnswers($userId);
            }
        }

        return $result;
    }

    /**
     * Reset practice progress.
     */
    public function resetPracticeProgress(int $userId): bool
    {
        // Reset practice progress
        $result = $this->studySessionRepository->resetPracticeProgress($userId);

        // Reset statistics
        if ($result) {
            $this->statisticService->resetPracticeStatistics($userId);

            // Log the action
            $this->logService->logPracticeReset($userId);
        }

        return $result;
    }
}
