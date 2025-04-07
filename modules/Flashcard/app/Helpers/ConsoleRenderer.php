<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Helpers;

use function Termwind\render;

/**
 * Helper class for rendering console output that can be suppressed during testing
 */
final class ConsoleRenderer
{
    private static ?string $testOutput = null;

    /**
     * Enable test mode and start capturing output
     */
    public static function enableTestMode(): void
    {
        self::$testOutput = '';
    }

    /**
     * Get the captured output and reset it
     */
    public static function getTestOutput(): ?string
    {
        $output = self::$testOutput;
        self::$testOutput = null;

        return $output;
    }

    /**
     * Reset test output
     */
    public static function resetTestOutput(): void
    {
        self::$testOutput = '';
    }

    /**
     * Render a message in the console, but only if not in testing or if TERMWIND_SILENT is not true
     */
    public static function render(string $html): void
    {
        // During testing, capture output instead of rendering
        if (self::$testOutput !== null) {
            self::$testOutput .= strip_tags($html).PHP_EOL;

            return;
        }

        // During testing, don't output anything
        if (defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__')) {
            return;
        }

        render($html);
    }

    /**
     * Render a success message with a green background
     */
    public static function success(string $message): void
    {
        if (self::$testOutput !== null) {
            self::$testOutput .= $message.PHP_EOL;

            return;
        }
        self::render('<p class="p-3 bg-green-600 text-white font-bold">'.$message.'</p>');
    }

    /**
     * Render an error message with a red background
     */
    public static function error(string $message): void
    {
        if (self::$testOutput !== null) {
            self::$testOutput .= $message.PHP_EOL;

            return;
        }
        self::render('<p class="p-3 bg-red-600 text-white font-bold">'.$message.'</p>');
    }

    /**
     * Render an info message with a blue background
     */
    public static function info(string $message): void
    {
        if (self::$testOutput !== null) {
            self::$testOutput .= $message.PHP_EOL;

            return;
        }
        self::render('<p class="p-3 bg-blue-600 text-white font-bold">'.$message.'</p>');
    }

    /**
     * Render a warning message with an orange background
     */
    public static function warning(string $message): void
    {
        if (self::$testOutput !== null) {
            self::$testOutput .= $message.PHP_EOL;

            return;
        }
        self::render('<p class="p-3 bg-orange-500 text-white font-bold">'.$message.'</p>');
    }
}
