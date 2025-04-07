<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Repositories\LogRepositoryInterface;
use Modules\Flashcard\app\Services\FlashcardService;

use function Laravel\Prompts\table;

final readonly class ListFlashcardsAction implements FlashcardActionInterface
{
    public function __construct(
        private Command $command,
        private FlashcardService $flashcardService,
        private LogRepositoryInterface $logRepository,
        private ConsoleRendererInterface $renderer,
    ) {}

    public function execute(): void
    {
        $this->command->info('Listing all flashcards...');

        // Get the authenticated user
        $user = null;
        if ($this->command instanceof FlashcardInteractiveCommand) {
            $user = $this->command->user;
        } else {
            // For our test command class
            $user = $this->command->user;
        }

        if (! $user) {
            $this->renderer->error('You must be logged in to list flashcards.');

            return;
        }

        // Log the action
        $this->logRepository->logFlashcardList($user->id);

        // Get all flashcards for the current user
        $flashcards = $this->flashcardService->getAllForUser($user->id)->items();

        if (count($flashcards) === 0) {
            $this->renderer->warning('You have no flashcards yet.');

            return;
        }

        // Prepare the data for the table
        $headers = ['Question', 'Answer'];
        $rows = [];

        foreach ($flashcards as $flashcard) {
            $rows[] = [
                'Question' => $flashcard->question,
                'Answer' => $flashcard->answer,
            ];
        }

        // Render the flashcards using Laravel Prompts table
        table(
            headers: $headers,
            rows: $rows
        );
    }
}
