<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Repositories\FlashcardRepositoryInterface;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;

final readonly class TrashBinAction implements FlashcardActionInterface
{
    public function __construct(
        private Command $command,
        private FlashcardRepositoryInterface $flashcardRepository,
        private LogRepositoryInterface $logRepository,
        private ConsoleRendererInterface $renderer,
    ) {}

    public function execute(): void
    {
        $user = null;
        if ($this->command instanceof FlashcardInteractiveCommand) {
            $user = $this->command->user;
        } else {
            // For our test command class
            $user = $this->command->user;
        }

        if (! $user) {
            $this->renderer->error('You must be logged in to access the trash bin.');

            return;
        }

        $deletedFlashcards = $this->flashcardRepository->getAllDeletedForUser($user->id, 15);

        if ($deletedFlashcards->isEmpty()) {
            $this->renderer->warning('No deleted flashcards found.');

            return;
        }

        $this->renderer->info('Deleted Flashcards:');
        $this->renderer->info('------------------');

        foreach ($deletedFlashcards as $index => $flashcard) {
            $this->renderer->info(($index + 1).". Question: {$flashcard->question}");
            $this->renderer->info("   Answer: {$flashcard->answer}");
            $this->renderer->info("   Deleted at: {$flashcard->deleted_at}");
            $this->renderer->info('------------------');
        }

        // Display options
        $this->renderer->info('Options:');
        $this->renderer->info('1. Restore a flashcard');
        $this->renderer->info('2. Permanently delete a flashcard');
        $this->renderer->info('3. Restore all flashcards');
        $this->renderer->info('4. Permanently delete all flashcards');
        $this->renderer->info('5. Exit');

        $choice = $this->renderer->ask('Enter your choice (1-5): ');

        switch ($choice) {
            case '1':
                $this->handleRestore($deletedFlashcards);
                break;
            case '2':
                $this->handlePermanentDelete($deletedFlashcards);
                break;
            case '3':
                $this->handleRestoreAll($user->id);
                break;
            case '4':
                $this->handlePermanentDeleteAll($user->id);
                break;
            case '5':
                $this->renderer->info('Exiting trash bin...');
                break;
            default:
                $this->renderer->error('Invalid choice. Please try again.');
                break;
        }
    }

    private function handleRestore(LengthAwarePaginator $deletedFlashcards): void
    {
        $flashcardNumber = (int) $this->renderer->ask('Enter the number of the flashcard to restore: ');
        $flashcard = $deletedFlashcards->items()[$flashcardNumber - 1] ?? null;

        if (! $flashcard) {
            $this->renderer->error('Invalid flashcard number.');

            return;
        }

        $flashcard = $this->flashcardRepository->findForUser($this->command->user->id, $flashcard->id, true);

        if (! $flashcard) {
            $this->renderer->error('Flashcard not found.');

            return;
        }

        if ($this->flashcardRepository->restore($flashcard)) {
            $this->logRepository->logFlashcardRestoration($this->command->user->id, $flashcard);
            $this->renderer->success('Flashcard restored successfully!');
        } else {
            $this->renderer->error('Failed to restore flashcard.');
        }
    }

    private function handlePermanentDelete(LengthAwarePaginator $deletedFlashcards): void
    {
        $flashcardNumber = (int) $this->renderer->ask('Enter the number of the flashcard to permanently delete: ');
        $flashcard = $deletedFlashcards->items()[$flashcardNumber - 1] ?? null;

        if (! $flashcard) {
            $this->renderer->error('Invalid flashcard number.');

            return;
        }

        $flashcard = $this->flashcardRepository->findForUser($this->command->user->id, $flashcard->id, true);

        if (! $flashcard) {
            $this->renderer->error('Flashcard not found.');

            return;
        }

        if ($this->flashcardRepository->forceDelete($flashcard)) {
            $this->logRepository->logFlashcardDeletion($this->command->user->id, $flashcard);
            $this->renderer->success('Flashcard permanently deleted successfully!');
        } else {
            $this->renderer->error('Failed to permanently delete flashcard.');
        }
    }

    private function handleRestoreAll(int $userId): void
    {
        $confirmation = $this->renderer->ask('Are you sure you want to restore all flashcards? (yes/no): ');

        if (mb_strtolower($confirmation) !== 'yes') {
            $this->renderer->info('Operation cancelled.');

            return;
        }

        $restored = $this->flashcardRepository->restoreAll($userId);

        if ($restored) {
            $this->logRepository->logAllFlashcardsRestore($userId);
            $this->renderer->success('All flashcards restored successfully!');
        } else {
            $this->renderer->error('Failed to restore all flashcards.');
        }
    }

    private function handlePermanentDeleteAll(int $userId): void
    {
        $confirmation = $this->renderer->ask('Are you sure you want to permanently delete all flashcards? (yes/no): ');

        if (mb_strtolower($confirmation) !== 'yes') {
            $this->renderer->info('Operation cancelled.');

            return;
        }

        $deleted = $this->flashcardRepository->forceDeleteAll($userId);

        if ($deleted) {
            $this->logRepository->logAllFlashcardsPermanentDelete($userId);
            $this->renderer->success('All flashcards permanently deleted!');
        } else {
            $this->renderer->error('Failed to delete all flashcards.');
        }
    }
}
