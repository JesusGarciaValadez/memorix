<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Helpers;

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

        echo $html;
    }

    /**
     * Render a success message with a green background
     */
    public static function success(string $message): void
    {
        if (self::$testOutput !== null) {
            self::render($message);

            return;
        }
        echo "\033[32m".$message."\033[0m\n";
    }

    /**
     * Render an error message with a red background
     */
    public static function error(string $message): void
    {
        if (self::$testOutput !== null) {
            self::render($message);

            return;
        }
        echo "\033[31m".$message."\033[0m\n";
    }

    /**
     * Render an info message with a blue background
     */
    public static function info(string $message): void
    {
        if (self::$testOutput !== null) {
            self::render($message);

            return;
        }
        echo "\033[34m".$message."\033[0m\n";
    }

    /**
     * Render a warning message with an orange background
     */
    public static function warning(string $message): void
    {
        if (self::$testOutput !== null) {
            self::render($message);

            return;
        }
        echo "\033[33m".$message."\033[0m\n";
    }

    public static function table(array $headers, array $rows): void
    {
        // Calculate column widths
        $widths = [];
        foreach ($headers as $i => $header) {
            $widths[$i] = mb_strlen($header);
            foreach ($rows as $row) {
                $widths[$i] = max($widths[$i], mb_strlen((string) $row[$i]));
            }
        }

        // Print headers
        echo "\033[34m"; // Blue color for headers
        foreach ($headers as $i => $header) {
            echo mb_str_pad($header, $widths[$i] + 2);
        }
        echo "\033[0m\n"; // Reset color

        // Print separator
        foreach ($widths as $width) {
            echo str_repeat('-', $width + 2);
        }
        echo "\n";

        // Print rows
        foreach ($rows as $row) {
            foreach ($row as $i => $cell) {
                echo mb_str_pad((string) $cell, $widths[$i] + 2);
            }
            echo "\n";
        }
    }
}
