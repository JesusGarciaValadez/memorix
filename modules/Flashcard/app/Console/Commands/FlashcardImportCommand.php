<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Services\FlashcardCommandServiceInterface;

final class FlashcardImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashcard:import 
                            {--file= : Path to the CSV file containing flashcards}
                            {--email= : Email of the user to import flashcards for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import flashcards from a CSV file for a specific user';

    /**
     * Execute the console command.
     */
    public function handle(
        FlashcardCommandServiceInterface $commandService,
        ConsoleRendererInterface $renderer
    ): int {
        // Get file path from the option
        $filePath = $this->option('file');
        if (! $filePath) {
            $renderer->error('File path is required. Use --file option to specify the CSV file path.');

            return 1;
        }

        // Check if file exists
        if (! file_exists($filePath)) {
            $renderer->error("File not found: {$filePath}");

            return 1;
        }

        // Get user email from the option
        $email = $this->option('email');
        if (! $email) {
            $renderer->error('User email is required. Use --email option to specify the user email.');

            return 1;
        }

        // Find user by email
        $user = User::where('email', $email)->first();
        if (! $user) {
            $renderer->error("User not found with email: {$email}");

            return 1;
        }

        // Import flashcards
        $success = $commandService->importFlashcardsFromFile($user->id, $filePath);

        if ($success) {
            $renderer->success('Flashcards imported successfully!');

            return 0;
        }
        $renderer->error('Failed to import flashcards.');

        return 1;
    }
}
