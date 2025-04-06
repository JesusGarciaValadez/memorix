<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Find a user by email.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Get user password by email.
     */
    public function getPasswordByEmail(string $email): ?string;

    /**
     * Create a new user.
     */
    public function create(array $data): User;
}
