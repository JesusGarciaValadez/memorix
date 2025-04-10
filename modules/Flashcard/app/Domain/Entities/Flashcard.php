<?php

declare(strict_types=1);

namespace Modules\Flashcard\Domain\Entities;

use DateTimeInterface;
use Modules\Flashcard\Domain\ValueObjects\FlashcardStatus;

final class Flashcard
{
    private function __construct(
        private readonly string $id,
        private readonly string $userId,
        private readonly string $question,
        private readonly string $answer,
        private FlashcardStatus $status,
        private ?DateTimeInterface $lastReviewedAt
    ) {}

    public static function create(
        string $id,
        string $userId,
        string $question,
        string $answer
    ): self {
        return new self(
            id: $id,
            userId: $userId,
            question: $question,
            answer: $answer,
            status: FlashcardStatus::NEW,
            lastReviewedAt: null
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function question(): string
    {
        return $this->question;
    }

    public function answer(): string
    {
        return $this->answer;
    }

    public function status(): FlashcardStatus
    {
        return $this->status;
    }

    public function lastReviewedAt(): ?DateTimeInterface
    {
        return $this->lastReviewedAt;
    }

    public function review(bool $isCorrect, DateTimeInterface $reviewedAt): void
    {
        $this->status = $isCorrect ? FlashcardStatus::CORRECT : FlashcardStatus::INCORRECT;
        $this->lastReviewedAt = $reviewedAt;
    }

    public function isNew(): bool
    {
        return $this->status->isNew();
    }

    public function isCorrect(): bool
    {
        return $this->status->isCorrect();
    }

    public function isIncorrect(): bool
    {
        return $this->status->isIncorrect();
    }
}
