<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Repositories\Eloquent;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Flashcard\app\Repositories\Eloquent\UserRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository();
    }

    #[Test]
    public function it_finds_user_by_email(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $result = $this->repository->findByEmail($user->email);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
        $this->assertEquals($user->email, $result->email);
    }

    #[Test]
    public function it_returns_null_when_user_not_found_by_email(): void
    {
        // Arrange
        $nonExistentEmail = 'nonexistent@example.com';

        // Act
        $result = $this->repository->findByEmail($nonExistentEmail);

        // Assert
        $this->assertNull($result);
    }

    #[Test]
    public function it_gets_password_by_email(): void
    {
        // Arrange
        $password = 'password123';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        // Act
        $result = $this->repository->getPasswordByEmail($user->email);

        // Assert
        $this->assertNotNull($result);
        $this->assertTrue(Hash::check($password, $result));
    }

    #[Test]
    public function it_returns_null_when_getting_password_for_nonexistent_email(): void
    {
        // Arrange
        $nonExistentEmail = 'nonexistent@example.com';

        // Act
        $result = $this->repository->getPasswordByEmail($nonExistentEmail);

        // Assert
        $this->assertNull($result);
    }

    #[Test]
    public function it_creates_a_new_user(): void
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ];

        // Act
        $result = $this->repository->create($data);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('John Doe', $result->name);
        $this->assertEquals('john@example.com', $result->email);
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }
}
