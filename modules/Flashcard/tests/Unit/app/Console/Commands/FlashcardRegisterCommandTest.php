<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Unit\app\Console\Commands;

use App\Models\User;
use Exception;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Hash;
use Modules\Flashcard\app\Console\Commands\FlashcardRegisterCommand;
use Modules\Flashcard\app\Helpers\ConsoleRendererInterface;
use Modules\Flashcard\app\Repositories\UserRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

trait CommandTestTrait
{
    private Application $container;

    protected function setUpCommandTest(): void
    {
        $this->container = new Application();
        $this->container->instance(Kernel::class, $this->createMock(Kernel::class));
    }

    protected function createArtisanInput(array $arguments): ArrayInput
    {
        $input = new ArrayInput($arguments);
        $input->bind($this->command->getDefinition());

        return $input;
    }

    protected function createArtisanOutput(): BufferedOutput
    {
        return new BufferedOutput();
    }
}

final class FlashcardRegisterCommandTest extends TestCase
{
    use CommandTestTrait;

    private UserRepositoryInterface|MockObject $userRepository;

    private ConsoleRendererInterface|MockObject $renderer;

    private FlashcardRegisterCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->renderer = $this->createMock(ConsoleRendererInterface::class);

        // Set up command test environment
        $this->setUpCommandTest();

        // Create the command instance
        $this->command = new FlashcardRegisterCommand($this->userRepository, $this->renderer);
        $this->command->setLaravel($this->container);
    }

    #[Test]
    public function it_tests_the_flashcard_user_registration_option(): void
    {
        $name = 'John_Wick';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';

        $this->userRepository->expects($this->once())
            ->method('create')
            ->willReturnCallback(function ($data) use ($name, $email, $password) {
                $this->assertEquals(str_replace('_', ' ', $name), $data['name']);
                $this->assertEquals($email, $data['email']);
                $this->assertTrue(Hash::check($password, $data['password']));

                return new User($data);
            });

        $this->renderer->expects($this->once())
            ->method('success')
            ->with('User John Wick registered successfully with email john@wick.com.');

        $this->command->run($this->createArtisanInput([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            '--skip-interactive' => true,
        ]), $this->createArtisanOutput());
    }

    #[Test]
    public function it_tests_the_user_registration_user_name_validation(): void
    {
        $name = '';
        $email = 'john@wick.com';
        $password = 'P4$$w0rd!';

        $this->userRepository->expects($this->never())
            ->method('create');

        $this->renderer->expects($this->never())
            ->method('success');

        $this->command->run($this->createArtisanInput([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            '--skip-interactive' => true,
        ]), $this->createArtisanOutput());
    }

    #[Test]
    public function it_tests_the_user_registration_user_email_validation(): void
    {
        $name = 'John_Wick';
        $email = 'john';
        $password = 'P4$$w0rd!';

        $this->userRepository->expects($this->never())
            ->method('create');

        $this->renderer->expects($this->never())
            ->method('success');

        $this->command->run($this->createArtisanInput([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            '--skip-interactive' => true,
        ]), $this->createArtisanOutput());
    }

    #[Test]
    public function it_tests_the_user_registration_user_password_validation(): void
    {
        $name = 'John_Wick';
        $email = 'john@wick.com';
        $password = 'Password';

        $this->userRepository->expects($this->never())
            ->method('create');

        $this->renderer->expects($this->never())
            ->method('success');

        $this->command->run($this->createArtisanInput([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            '--skip-interactive' => true,
        ]), $this->createArtisanOutput());
    }

    #[Test]
    public function it_handles_duplicate_email_error_gracefully(): void
    {
        $this->userRepository->expects($this->once())
            ->method('create')
            ->willThrowException(new QueryException(
                'sqlite',
                'insert into "users" ("email") values (?)',
                ['test@example.com'],
                new Exception('SQLSTATE[23000]: Integrity constraint violation: 19 UNIQUE constraint failed: users.email')
            ));

        $this->renderer->expects($this->never())
            ->method('success');

        $this->command->run($this->createArtisanInput([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            '--skip-interactive' => true,
        ]), $this->createArtisanOutput());
    }
}
