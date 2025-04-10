<?php

declare(strict_types=1);

namespace Modules\Flashcard\Domain\Repositories;

use Illuminate\Support\Collection;
use Modules\Flashcard\Domain\Entities\Flashcard;

interface FlashcardRepository
{
    public function find(int $id): ?Flashcard;

    public function findByUser(int $userId): Collection;

    public function save(Flashcard $flashcard): void;

    public function delete(Flashcard $flashcard): void;

    public function getStatistics(int $userId): array;
}
