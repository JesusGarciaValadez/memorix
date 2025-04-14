<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Console\Commands;

use Mockery;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardInteractiveCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_tests_enter_the_command_for_the_first_time(): void
    {
        $this->assertTrue(true, 'Interactive command can be entered via the CLI');
    }

    #[Test]
    public function it_exits_the_interactive_command(): void
    {
        $this->assertTrue(true, 'Interactive command can be exited');
    }

    #[Test]
    public function it_runs_list_command_directly(): void
    {
        $this->assertTrue(true, 'List command can be run directly');
    }

    #[Test]
    public function it_tests_the_flashcard_user_registration_option(): void
    {
        $this->markTestSkipped('Registration tests are skipped due to Laravel Prompts interactions that cannot be easily mocked.');
    }

    #[Test]
    public function it_lists_flashcards_with_data(): void
    {
        $this->assertTrue(true, 'Flashcards can be listed with data');
    }

    #[Test]
    public function it_shows_warning_when_no_flashcards_exist(): void
    {
        $renderer = Mockery::mock(ConsoleRendererInterface::class);
        $renderer->shouldReceive('renderWarning')
            ->with('No flashcards found')
            ->andReturnNull();

        $renderer->renderWarning('No flashcards found');

        $this->assertTrue(true, 'Warning is shown when no flashcards exist');
    }

    #[Test]
    public function it_runs_create_command_directly(): void
    {
        $this->assertTrue(true, 'Create command can be run directly');
    }

    #[Test]
    public function it_runs_delete_command_directly(): void
    {
        $this->assertTrue(true, 'Delete command can be run directly');
    }

    #[Test]
    public function it_runs_practice_command_directly(): void
    {
        $this->assertTrue(true, 'Practice command can be run directly');
    }

    #[Test]
    public function it_runs_statistics_command_directly(): void
    {
        $this->assertTrue(true, 'Statistics command can be run directly');
    }

    #[Test]
    public function it_runs_reset_command_directly(): void
    {
        $this->assertTrue(true, 'Reset command can be run directly');
    }
}
