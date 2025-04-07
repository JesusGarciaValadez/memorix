<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand;
use Modules\Flashcard\app\Helpers\ConsoleRenderer;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Repositories\PracticeResultRepositoryInterface;
use Modules\Flashcard\app\Repositories\StatisticRepositoryInterface;
use Modules\Flashcard\app\Repositories\StudySessionRepositoryInterface;

final readonly class ResetFlashcardAction implements FlashcardActionInterface
{
    private readonly StatisticRepositoryInterface $statisticRepository;

    private readonly PracticeResultRepositoryInterface $practiceResultRepository;

    private readonly LogRepositoryInterface $logRepository;

    private readonly StudySessionRepositoryInterface $studySessionRepository;

    public function __construct(
        private readonly Command $command,
        ?StatisticRepositoryInterface $statisticRepository = null,
        ?PracticeResultRepositoryInterface $practiceResultRepository = null,
        ?LogRepositoryInterface $logRepository = null,
        ?StudySessionRepositoryInterface $studySessionRepository = null,
    ) {
        $this->statisticRepository = $statisticRepository ?? app(StatisticRepositoryInterface::class);
        $this->practiceResultRepository = $practiceResultRepository ?? app(PracticeResultRepositoryInterface::class);
        $this->logRepository = $logRepository ?? app(LogRepositoryInterface::class);
        $this->studySessionRepository = $studySessionRepository ?? app(StudySessionRepositoryInterface::class);
    }

    public function execute(): void
    {
        $this->command->info('Resetting flashcard data...');

        // Get the authenticated user
        $user = null;
        if ($this->command instanceof FlashcardInteractiveCommand) {
            $user = $this->command->user;
        } else {
            // For our test command class
            $user = $this->command->user;
        }

        if (! $user) {
            ConsoleRenderer::error('You must be logged in to reset flashcard data.');

            return;
        }

        // Reset practice statistics
        $statsReset = $this->statisticRepository->resetPracticeStats($user->id);

        // Delete all practice results
        $resultsDeleted = $this->practiceResultRepository->deleteForUser($user->id);

        // Delete all study sessions
        $sessionsDeleted = $this->studySessionRepository->deleteAllForUser($user->id);

        // Log the reset action
        $this->logRepository->logPracticeReset($user->id);

        if ($statsReset && $resultsDeleted && $sessionsDeleted) {
            $this->command->info('All practice data has been reset successfully.');
        } else {
            ConsoleRenderer::error('There was an error resetting practice data.');
        }
    }
}
