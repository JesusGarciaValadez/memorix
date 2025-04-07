<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Helpers;

interface ConsoleRendererInterface
{
    public function enableTestMode(): void;

    public function captureOutput(): void;

    public function getCapturedOutput(): string;

    public function success(string $message): void;

    public function error(string $message): void;

    public function info(string $message): void;

    public function warning(string $message): void;

    public function ask(string $question): string;

    public function table(array $headers, array $rows): void;
}
