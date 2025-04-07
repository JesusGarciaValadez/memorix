<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;
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

        $deletedFlashcards = $this->flashcardRepository->getAllDeletedForUser($user->id);

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
                $this->handleRestore($user->id, $deletedFlashcards->items());
                break;
            case '2':
                $this->handlePermanentDelete($user->id, $deletedFlashcards->items());
                break;
            case '3':
                $this->handleRestoreAll($user->id, $deletedFlashcards->items());
                break;
            case '4':
                $this->handlePermanentDeleteAll($user->id, $deletedFlashcards->items());
                break;
            case '5':
                $this->renderer->info('Exiting trash bin...');
                break;
            default:
                $this->renderer->error('Invalid choice. Please try again.');
                break;
        }
    }

    private function handleRestore(int $userId, array $deletedFlashcards): void
    {
        $flashcardNumber = $this->renderer->ask('Enter the number of the flashcard to restore: ');
        $index = (int) $flashcardNumber - 1;

        if (! isset($deletedFlashcards[$index])) {
            $this->renderer->error('Invalid flashcard number.');

            return;
        }

        $flashcard = $deletedFlashcards[$index];
        $flashcardModel = $this->flashcardRepository->findForUser($flashcard->id, $userId, true);

        if (! $flashcardModel) {
            $this->renderer->error('Flashcard not found.');

            return;
        }

        $restored = $this->flashcardRepository->restore($flashcardModel);

        if ($restored) {
            $this->logRepository->logFlashcardRestoration($userId, $flashcard);
            $this->renderer->success('Flashcard restored successfully!');
        } else {
            $this->renderer->error('Failed to restore flashcard.');
        }
    }

    private function handlePermanentDelete(int $userId, array $deletedFlashcards): void
    {
        $flashcardNumber = $this->renderer->ask('Enter the number of the flashcard to permanently delete: ');
        $index = (int) $flashcardNumber - 1;

        if (! isset($deletedFlashcards[$index])) {
            $this->renderer->error('Invalid flashcard number.');

            return;
        }

        $flashcard = $deletedFlashcards[$index];
        $flashcardModel = $this->flashcardRepository->findForUser($flashcard->id, $userId, true);

        if (! $flashcardModel) {
            $this->renderer->error('Flashcard not found.');

            return;
        }

        $deleted = $this->flashcardRepository->forceDelete($flashcardModel);

        if ($deleted) {
            $this->logRepository->logFlashcardDeletion($userId, $flashcard);
            $this->renderer->success('Flashcard permanently deleted!');
        } else {
            $this->renderer->error('Failed to permanently delete flashcard.');
        }
    }

    private function handleRestoreAll(int $userId, array $deletedFlashcards): void
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

    private function handlePermanentDeleteAll(int $userId, array $deletedFlashcards): void
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
