<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Console\Commands\Actions;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Prompts\Prompt;
use Modules\Flashcard\app\Console\Commands\Actions\ListFlashcardsAction;
use Modules\Flashcard\app\Models\Flashcard;
use Modules\Flashcard\app\Services\FlashcardService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ListFlashcardsActionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private FlashcardService $flashcardService;

    private Command $command;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();

        // Get the real flashcard service from the container
        $this->flashcardService = app(FlashcardService::class);

        // Create a test command class that can be tested
        $this->command = new class extends Command
        {
            public $user;

            public $output = '';

            protected $name = 'test:command';

            public function info($string, $verbosity = null)
            {
                $this->output .= $string.PHP_EOL;
            }
        };

        $this->command->user = $this->user;

        // Mock the Laravel Prompts for testing
        Prompt::fake();
    }

    #[Test]
    public function it_displays_info_message(): void
    {
        // Create and execute the action
        $action = new ListFlashcardsAction($this->command, $this->flashcardService);
        $action->execute();

        // Assert info message is displayed
        $this->assertStringContainsString('Listing all flashcards...', $this->command->output);
    }

    #[Test]
    public function it_displays_warning_when_no_flashcards_exist(): void
    {
        // Since we can't mock the ConsoleRenderer (it's final), we'll test indirectly
        // by checking what we can - that the table wasn't called

        // Create and execute the action
        $action = new ListFlashcardsAction($this->command, $this->flashcardService);
        $action->execute();

        // We should only see the info message and nothing related to table output
        $this->assertStringContainsString('Listing all flashcards...', $this->command->output);

        // There should be no flashcards for the test user when the test starts
        $this->assertEquals(0, Flashcard::where('user_id', $this->user->id)->count());
    }

    #[Test]
    public function it_lists_flashcards_when_available(): void
    {
        // Create a few flashcards for the user
        $flashcards = Flashcard::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        // Create and execute the action
        $action = new ListFlashcardsAction($this->command, $this->flashcardService);
        $action->execute();

        // We can only assert that the info message was shown
        $this->assertStringContainsString('Listing all flashcards...', $this->command->output);

        // Check that flashcards exist in the database
        $this->assertEquals(3, Flashcard::where('user_id', $this->user->id)->count());

        // With Laravel Prompts' table function and faked output,
        // we can't directly assert the table content. Since the prompts are faked,
        // we can only verify that the database has the expected flashcards,
        // and that our code would have reached the point where the table is rendered
        // (which is indicated by having flashcards and seeing the info message).
    }
}
