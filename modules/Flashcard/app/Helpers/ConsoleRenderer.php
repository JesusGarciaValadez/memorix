<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Helpers;

/**
 * Helper class for rendering console output that can be suppressed during testing
 */
final class ConsoleRenderer implements ConsoleRendererInterface
{
    private static ?string $testOutput = null;

    /**
     * Enable test mode and start capturing output
     */
    public function enableTestMode(): void
    {
        self::$testOutput = '';
    }

    /**
     * Get the captured output and reset it
     */
    public function getCapturedOutput(): string
    {
        $output = self::$testOutput ?? '';
        self::$testOutput = null;

        return $output;
    }

    /**
     * Capture output for testing
     */
    public function captureOutput(): void
    {
        self::$testOutput = '';
    }

    /**
     * Reset test output
     */
    public function resetTestOutput(): void
    {
        self::$testOutput = '';
    }

    /**
     * Render a success message with a green background
     */
    public function success(string $message): void
    {
        if (self::$testOutput !== null) {
            self::$testOutput .= $message.PHP_EOL;

            return;
        }

        // Don't output during PHPUnit tests
        if (defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__')) {
            return;
        }

        echo "\033[32m".$message."\033[0m\n";
    }

    /**
     * Render an error message with a red background
     */
    public function error(string $message): void
    {
        if (self::$testOutput !== null) {
            self::$testOutput .= $message.PHP_EOL;

            return;
        }

        // Don't output during PHPUnit tests
        if (defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__')) {
            return;
        }

        echo "\033[31m".$message."\033[0m\n";
    }

    /**
     * Render an info message with a blue background
     */
    public function info(string $message): void
    {
        if (self::$testOutput !== null) {
            self::$testOutput .= $message.PHP_EOL;

            return;
        }

        // Don't output during PHPUnit tests
        if (defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__')) {
            return;
        }

        echo "\033[34m".$message."\033[0m\n";
    }

    /**
     * Render a warning message with a yellow background
     */
    public function warning(string $message): void
    {
        if (self::$testOutput !== null) {
            self::$testOutput .= $message.PHP_EOL;

            return;
        }

        // Don't output during PHPUnit tests
        if (defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__')) {
            return;
        }

        echo "\033[33m".$message."\033[0m\n";
    }

    /**
     * Ask a question and return the answer
     */
    public function ask(string $question): string
    {
        if (self::$testOutput !== null) {
            self::$testOutput .= $question.PHP_EOL;

            return '';
        }

        // Don't output during PHPUnit tests
        if (defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__')) {
            return '';
        }

        echo $question.': ';
        $handle = fopen('php://stdin', 'r');
        $line = fgets($handle);
        fclose($handle);

        return mb_trim($line);
    }

    /**
     * Render a table
     */
    public function table(array $headers, array $rows): void
    {
        if (self::$testOutput !== null) {
            self::$testOutput .= implode(' | ', $headers).PHP_EOL;
            self::$testOutput .= str_repeat('-', mb_strlen(implode(' | ', $headers))).PHP_EOL;
            foreach ($rows as $row) {
                self::$testOutput .= implode(' | ', $row).PHP_EOL;
            }

            return;
        }

        // Don't output during PHPUnit tests
        if (defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__')) {
            return;
        }

        // Simple table rendering for console
        echo implode(' | ', $headers).PHP_EOL;
        echo str_repeat('-', mb_strlen(implode(' | ', $headers))).PHP_EOL;
        foreach ($rows as $row) {
            echo implode(' | ', $row).PHP_EOL;
        }
    }
}
