<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Services\FlashcardService;

use function Laravel\Prompts\select;

final readonly class DeleteFlashcardAction implements FlashcardActionInterface
{
    public function __construct(
        private Command $command,
        private ConsoleRendererInterface $renderer,
    ) {}

    public function execute(): void
    {
        $this->command->info('Deleting a flashcard...');

        // Get the authenticated user
        $user = null;
        if ($this->command instanceof FlashcardInteractiveCommand) {
            $user = $this->command->validateUserInformation();
        }

        if (! $user) {
            $this->renderer->error('You must be logged in to delete a flashcard.');

            return;
        }

        // Get flashcard service from the container
        /** @var FlashcardService $flashcardService */
        $flashcardService = app(FlashcardService::class);

        // Get all user's flashcards
        $flashcards = $flashcardService->getAllForUser($user->id, 100)->items();

        if (count($flashcards) === 0) {
            $this->renderer->warning('You have no flashcards to delete.');

            return;
        }

        // Create options for select prompt with flashcard questions as keys and IDs as values
        $options = [];
        foreach ($flashcards as $flashcard) {
            $options[$flashcard->id] = mb_substr($flashcard->question, 0, 50).
                (mb_strlen($flashcard->question) > 50 ? '...' : '');
        }
        $options['cancel'] = 'Cancel - Go back to menu';

        // Ask user to select which flashcard to delete
        $selectedOption = select(
            label: 'Select a flashcard to delete:',
            options: $options,
            default: 'cancel'
        );

        if ($selectedOption === 'cancel') {
            $this->renderer->info('Operation cancelled.');

            return;
        }

        // Get confirmation
        $confirmation = select(
            label: 'Are you sure you want to delete this flashcard?',
            options: [
                'yes' => 'Yes, delete it',
                'no' => 'No, keep it',
            ],
            default: 'no'
        );

        if ($confirmation === 'no') {
            $this->renderer->info('Operation cancelled.');

            return;
        }

        // Delete the flashcard
        if ($flashcardService->delete($user->id, (int) $selectedOption)) {
            $this->renderer->success('Flashcard deleted successfully.');
        } else {
            $this->renderer->error('Failed to delete flashcard.');
        }
    }
}
