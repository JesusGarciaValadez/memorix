<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands\Actions;

use Illuminate\Console\Command;
use Modules\Flashcard\app\Console\Commands\FlashcardInteractiveCommand;
use Modules\Flashcard\app\Helpers\ConsoleRenderer;
use Modules\Flashcard\app\Services\FlashcardService;

use function Laravel\Prompts\text;

final readonly class CreateFlashcardAction implements FlashcardActionInterface
{
    public function __construct(
        private Command $command,
    ) {}

    public function execute(): void
    {
        $this->command->info('Creating a new flashcard...');

        // Get the authenticated user
        // We need to cast the command to FlashcardInteractiveCommand to access validateUserInformation
        $user = null;
        if ($this->command instanceof FlashcardInteractiveCommand) {
            // The validateUserInformation method will handle authentication
            $user = $this->command->validateUserInformation();
        }

        if (! $user) {
            ConsoleRenderer::error('You must be logged in to create a flashcard.');

            return;
        }

        // Get flashcard service from the container
        /** @var FlashcardService $flashcardService */
        $flashcardService = app(FlashcardService::class);

        // Get question and answer from user
        $question = text(
            label: 'Enter the flashcard question:',
            placeholder: 'What is Laravel?',
            required: true,
            validate: fn (string $value) => mb_strlen(mb_trim($value)) < 3 ? 'The question must be at least 3 characters.' : null,
        );

        $answer = text(
            label: 'Enter the flashcard answer:',
            placeholder: 'Laravel is a PHP web application framework.',
            required: true,
            validate: fn (string $value) => mb_strlen(mb_trim($value)) < 3 ? 'The answer must be at least 3 characters.' : null,
        );

        // Create flashcard
        $flashcard = $flashcardService->create($user->id, [
            'question' => $question,
            'answer' => $answer,
        ]);

        // Show success message
        ConsoleRenderer::success('Flashcard created successfully!');
        $this->command->info("Question: {$flashcard->question}");
        $this->command->info("Answer: {$flashcard->answer}");
    }
}
