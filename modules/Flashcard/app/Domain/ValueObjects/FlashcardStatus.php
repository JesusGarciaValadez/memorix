<?php

declare(strict_types=1);

namespace Modules\Flashcard\Domain\ValueObjects;

enum FlashcardStatus: string
{
    case NEW = 'new';
    case CORRECT = 'correct';
    case INCORRECT = 'incorrect';

    public function isNew(): bool
    {
        return $this === self::NEW;
    }

    public function isCorrect(): bool
    {
        return $this === self::CORRECT;
    }

    public function isIncorrect(): bool
    {
        return $this === self::INCORRECT;
    }
}
