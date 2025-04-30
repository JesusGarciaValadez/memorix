<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Console\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardInteractiveCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_enter_the_command_for_the_first_time(): void
    {
        User::truncate();
        $name = 'John_Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive');
        $command->expectsOutput('The email is not registered. Please, register first.')
            ->expectsQuestion('Enter your user name:', $name)
            ->expectsQuestion('Enter your user email:', $email)
            ->expectsQuestion('Enter your password:', $password)
            ->expectsOutput(
                sprintf(
                    'User %s registered successfully with email %s.',
                    str_replace('_', ' ', $name),
                    $email
                )
            )
            ->expectsOutput(
                sprintf(
                    'Hi, %s. Welcome to your flashcards',
                    str_replace('_', ' ', $name),
                )
            )
            ->expectsQuestion('Please, select an option:', 'exit')
            ->expectsOutput('See you!')
            ->assertOk();
    }

    #[Test]
    public function it_enter_the_command_for_the_first_time_with_arguments(): void
    {
        User::truncate();
        $name = 'John_Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive', [
            'email' => $email,
            'password' => $password,
        ]);
        $command->expectsOutput('The email is not registered. Please, register first.')
            ->expectsQuestion('Enter your user name:', $name)
            ->expectsQuestion('Enter your user email:', $email)
            ->expectsQuestion('Enter your password:', $password)
            ->expectsOutput(
                sprintf(
                    'User %s registered successfully with email %s.',
                    str_replace('_', ' ', $name),
                    $email
                )
            )
            ->expectsOutput(
                sprintf(
                    'Hi, %s. Welcome to your flashcards',
                    str_replace('_', ' ', $name),
                )
            )
            ->expectsQuestion('Please, select an option:', 'exit')
            ->expectsOutput('See you!')
            ->assertOk();
    }

    #[Test]
    public function it_exits_the_interactive_command(): void
    {
        $name = 'John Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';
        $hashedPassword = Hash::make($password);
        User::factory()->create([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
        ]);

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive', [
            'email' => $email,
            'password' => $password,
        ]);
        $command->expectsOutput(
            sprintf(
                'Hi, %s. Welcome to your flashcards',
                $name,
            )
        )
            ->expectsQuestion('Please, select an option:', 'exit')
            ->expectsOutput('See you!')
            ->assertOk();
    }

    #[Test]
    public function it_registers_a_user(): void
    {
        $name = 'John_Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive --register');
        $command->expectsQuestion('Enter your user name:', $name)
            ->expectsQuestion('Enter your user email:', $email)
            ->expectsQuestion('Enter your password:', $password)
            ->expectsOutput(
                sprintf(
                    'User %s registered successfully with email %s.',
                    str_replace('_', ' ', $name),
                    $email
                )
            )
            ->assertOk();
    }

    #[Test]
    public function it_registers_a_user_directly(): void
    {
        $name = 'John_Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';
        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive', [
            'email' => $email,
            'password' => $password,
            '--register' => true,
        ]);
        $command->expectsQuestion('Enter your user name:', $name)
            ->expectsQuestion('Enter your user email:', $email)
            ->expectsQuestion('Enter your password:', $password)
            ->expectsOutput(
                sprintf(
                    'User %s registered successfully with email %s.',
                    str_replace('_', ' ', $name),
                    $email
                )
            )
            ->assertOk();
    }

    #[Test]
    public function it_shows_warning_when_no_flashcards_exist(): void
    {
        $name = 'John Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';
        $hashedPassword = Hash::make($password);
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
        ]);

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive');
        $command->expectsQuestion('Enter your user email:', $email)
            ->expectsQuestion('Enter your password:', $password)
            ->expectsOutput(
                sprintf(
                    'Hi, %s. Welcome to your flashcards',
                    $name,
                )
            )
            ->expectsQuestion('Please, select an option:', 'list')
            ->expectsOutput('Listing all flashcards...')
            ->expectsOutput('You have no flashcards yet.')
            ->assertOk();
    }

    #[Test]
    public function it_shows_warning_when_no_flashcards_exist_directly(): void
    {
        $name = 'Test Example';
        $email = 'test@example.com';
        $password = 'P4$$w0rd!';
        $hashedPassword = Hash::make($password);
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
        ]);

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive', [
            'email' => $email,
            'password' => $password, // Use plain password for prompt
            '--list' => true,
        ]);
        $command->expectsOutput('Listing all flashcards...')
            ->expectsOutput('You have no flashcards yet.')
            ->assertOk();
    }

    #[Test]
    public function it_lists_flashcards_directly(): void
    {
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';
        /** @var User $user */
        $user = User::create([
            'name' => 'John Wick',
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        // Create sample flashcards
        /** @var \Modules\Flashcard\app\Models\Flashcard $flashcard1 */
        $flashcard1 = $user->flashcards()->create([
            'question' => 'What is Laravel?',
            'answer' => 'A PHP framework for web development',
        ]);
        /** @var \Modules\Flashcard\app\Models\Flashcard $flashcard2 */
        $flashcard2 = $user->flashcards()->create([
            'question' => 'What is PHPUnit?',
            'answer' => 'A unit testing framework for PHP',
        ]);

        // Run the list command
        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive', [
            'email' => $email,
            'password' => $password,
            '--list' => true,
        ]);
        $command->expectsOutput('Listing all flashcards...')
            ->expectsTable(
                [
                    'Question',
                    'Answer',
                ],
                [
                    [$flashcard1->question, $flashcard1->answer],
                    [$flashcard2->question, $flashcard2->answer],
                ]
            )
            ->assertOk();
    }

    #[Test]
    public function it_runs_create_command(): void
    {
        $name = 'John Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';
        $hashedPassword = Hash::make($password);
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
        ]);

        $question = 'Test Question?';
        $answer = 'Test Answer.';

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive');
        $command->expectsQuestion('Enter your user email:', $email)
            ->expectsQuestion('Enter your password:', $password)
            ->expectsOutput(sprintf('Hi, %s. Welcome to your flashcards', $name))
            ->expectsQuestion('Please, select an option:', 'create')
            ->expectsOutput('Creating a new flashcard...')
            ->expectsQuestion('Enter the flashcard question:', $question)
            ->expectsQuestion('Enter the flashcard answer:', $answer)
            ->expectsOutput('Question: '.$question)
            ->expectsOutput('Answer: '.$answer)
            ->expectsOutput('Flashcard created successfully!')
            ->expectsQuestion('Please, select an option:', 'exit') // Exit after create
            ->expectsOutput('See you!')
            ->assertOk();
    }

    #[Test]
    public function it_runs_create_command_directly(): void
    {
        $name = 'John Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';
        $hashedPassword = Hash::make($password);
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
        ]);

        $question = 'Direct Test Question?';
        $answer = 'Direct Test Answer.';

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive', [
            'email' => $email,
            'password' => $password,
            '--create' => true,
        ]);
        $command->expectsOutput('Creating a new flashcard...')
            ->expectsQuestion('Enter the flashcard question:', $question)
            ->expectsQuestion('Enter the flashcard answer:', $answer)
            ->expectsOutput('Question: '.$question)
            ->expectsOutput('Answer: '.$answer)
            ->expectsOutput('Flashcard created successfully!')
            ->assertOk();
    }

    #[Test]
    public function it_runs_delete_command_without_flashcards(): void
    {
        $name = 'John Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';
        $hashedPassword = Hash::make($password);
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
        ]);

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive');
        $command->expectsQuestion('Enter your user email:', $email)
            ->expectsQuestion('Enter your password:', $password)
            ->expectsOutput(sprintf('Hi, %s. Welcome to your flashcards', $name))
            ->expectsQuestion('Please, select an option:', 'delete')
            ->expectsOutput('Deleting a flashcard...')
            ->expectsOutput('You have no flashcards to delete.')
            ->expectsQuestion('Please, select an option:', 'exit') // Exit after attempting delete
            ->expectsOutput('See you!')
            ->assertOk();
    }

    #[Test]
    public function it_runs_delete_command_directly_without_flashcards(): void
    {
        $name = 'John Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';
        $hashedPassword = Hash::make($password);
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
        ]);

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive', [
            'email' => $email,
            'password' => $password,
            '--delete' => true,
        ]);
        $command->expectsOutput('Deleting a flashcard...')
            ->expectsOutput('You have no flashcards to delete.')
            ->assertOk();
    }

    #[Test]
    public function it_starts_a_new_practice_command(): void
    {
        $name = 'John Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';
        /** @var User $user */
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        // Create a flashcard to practice
        /** @var \Modules\Flashcard\app\Models\Flashcard $flashcard */
        $flashcard = $user->flashcards()->create([
            'question' => 'Practice Question?',
            'answer' => 'Practice Answer.',
        ]);

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive');
        $command->expectsQuestion('Enter your user email:', $email)
            ->expectsQuestion('Enter your password:', $password)
            ->expectsOutput(sprintf('Hi, %s. Welcome to your flashcards', $name))
            ->expectsQuestion('Please, select an option:', 'practice')
            ->expectsOutput('Starting practice mode...')
            ->expectsTable(['Statistic', 'Value'], [
                ['Total Flashcards', '1'],
                ['Correct Answers', '0'],
                ['Incorrect Answers', '0'],
                ['Not Answered', '1'],
                ['Completion', '0%'],
            ])
            ->expectsQuestion('Select a flashcard to practice:', (string) $flashcard->id)
            ->expectsOutput('Question: '.$flashcard->question)
            ->expectsQuestion('Your answer:', $flashcard->answer)
            ->expectsOutput('Correct! The answer is: '.$flashcard->answer)
            ->expectsConfirmation('Continue practicing?', 'no') // Stop after one card
            ->expectsOutput('Ending practice session...')
            ->expectsQuestion('Please, select an option:', 'exit') // Exit after practice
            ->expectsOutput('See you!')
            ->assertOk();
    }

    #[Test]
    public function it_starts_a_new_practice_command_directly(): void
    {
        $name = 'John Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';
        /** @var User $user */
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        // Create a flashcard to practice
        /** @var \Modules\Flashcard\app\Models\Flashcard $flashcard */
        $flashcard = $user->flashcards()->create([
            'question' => 'Direct Practice Question?',
            'answer' => 'Direct Practice Answer.',
        ]);

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive', [
            'email' => $email,
            'password' => $password,
            '--practice' => true,
        ]);
        $command->expectsOutput('Starting practice mode...')
            ->expectsTable(['Statistic', 'Value'], [
                ['Total Flashcards', '1'],
                ['Correct Answers', '0'],
                ['Incorrect Answers', '0'],
                ['Not Answered', '1'],
                ['Completion', '0%'],
            ])
            ->expectsQuestion('Select a flashcard to practice:', (string) $flashcard->id)
            ->expectsOutput('Question: '.$flashcard->question)
            ->expectsQuestion('Your answer:', $flashcard->answer)
            ->expectsOutput('Correct! The answer is: '.$flashcard->answer)
            ->expectsConfirmation('Continue practicing?', 'no') // Stop after one card
            ->expectsOutput('Ending practice session...')
            ->assertOk();
    }

    #[Test]
    public function it_runs_statistics_command(): void
    {
        $name = 'John Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';
        $hashedPassword = Hash::make($password);
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
        ]);

        // Add some stats data if needed, e.g., via StatisticService mock or factory

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive');
        $command->expectsQuestion('Enter your user email:', $email)
            ->expectsQuestion('Enter your password:', $password)
            ->expectsOutput(sprintf('Hi, %s. Welcome to your flashcards', $name))
            ->expectsQuestion('Please, select an option:', 'stats')
            ->expectsOutput('Showing statistics...')
            ->expectsOutput('No statistics available yet.') // Assuming fresh user
            ->expectsQuestion('Please, select an option:', 'exit') // Exit after stats
            ->expectsOutput('See you!')
            ->assertOk();
    }

    #[Test]
    public function it_runs_statistics_command_directly(): void
    {
        $name = 'John Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';
        $hashedPassword = Hash::make($password);
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
        ]);

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive', [
            'email' => $email,
            'password' => $password,
            '--stats' => true,
        ]);
        $command->expectsOutput('Showing statistics...')
            ->expectsOutput('No statistics available yet.') // Assuming fresh user
            ->assertOk();
    }

    #[Test]
    public function it_runs_reset_command(): void
    {
        $name = 'John Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';
        $hashedPassword = Hash::make($password);
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
        ]);

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive');
        $command->expectsQuestion('Enter your user email:', $email)
            ->expectsQuestion('Enter your password:', $password)
            ->expectsOutput(sprintf('Hi, %s. Welcome to your flashcards', $name))
            ->expectsQuestion('Please, select an option:', 'reset')
            ->expectsOutput('Resetting flashcard data...')
            ->expectsConfirmation('Are you sure you want to reset all practice data? This will delete all practice results and study sessions.', 'yes')
            ->expectsOutput('Practice data reset successfully!')
            ->expectsQuestion('Please, select an option:', 'exit') // Exit after reset
            ->expectsOutput('See you!')
            ->assertOk();
    }

    #[Test]
    public function it_runs_reset_command_directly(): void
    {
        $name = 'John Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';
        $hashedPassword = Hash::make($password);
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
        ]);

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive', [
            'email' => $email,
            'password' => $password,
            '--reset' => true,
        ]);
        $command->expectsOutput('Resetting flashcard data...')
            ->expectsConfirmation('Are you sure you want to reset all practice data? This will delete all practice results and study sessions.', 'yes')
            ->expectsOutput('Practice data reset successfully!')
            ->assertOk();
    }

    #[Test]
    public function it_exits_the_flashcard_interactive_command(): void
    {
        $name = 'John Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';
        $hashedPassword = Hash::make($password);
        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
        ]);

        /** @var \Illuminate\Testing\PendingCommand $command */
        $command = $this->artisan('flashcard:interactive');
        $command->expectsQuestion('Enter your user email:', $email)
            ->expectsQuestion('Enter your password:', $password)
            ->expectsOutput(sprintf('Hi, %s. Welcome to your flashcards', $name))
            ->expectsQuestion('Please, select an option:', 'exit')
            ->expectsOutput('See you!')
            ->assertOk();
    }
}
