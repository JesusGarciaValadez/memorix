<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Services\FlashcardCommandServiceInterface;

final class FlashcardResetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashcard:reset {userId : The ID of the user whose flashcards to reset}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset practice data for a specific user';

    /**
     * Execute the console command.
     */
    public function handle(
        FlashcardCommandServiceInterface $commandService,
        ConsoleRendererInterface $renderer
    ): int {
        $userId = (int) $this->argument('userId');

        // Find user by ID
        $user = User::find($userId);
        if (! $user) {
            $renderer->error("User not found with ID: {$userId}");

            return 1;
        }

        // Reset practice data
        $commandService->resetPracticeData($user);

        return 0;
    }
}
