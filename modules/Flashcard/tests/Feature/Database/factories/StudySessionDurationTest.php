<?php

declare(strict_types=1);

namespace Modules\Flashcard\Tests\Feature\database\factories;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StudySessionDurationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function duration_is_calculated_correctly(): void
    {
        // Create two Carbon instances 30 minutes apart
        $started = Carbon::parse('2023-01-01 10:00:00');
        $ended = Carbon::parse('2023-01-01 10:30:00');

        // Calculate the duration in minutes
        $duration = (int) $started->diffInMinutes($ended);

        // Assert that the duration is 30 minutes
        $this->assertEquals(30, $duration);
    }

    #[Test]
    public function time_difference_is_displayed_properly(): void
    {
        // Create two Carbon instances with different dates/times
        $startedAt = Carbon::parse('2023-01-01 08:45:00');
        $endedAt = Carbon::parse('2023-01-01 09:15:30');

        // Calculate various time differences
        $diffInMinutes = (int) $startedAt->diffInMinutes($endedAt);
        $diffInSeconds = $startedAt->diffInSeconds($endedAt);

        // Assert the correct time differences
        $this->assertEquals(30, $diffInMinutes);
        $this->assertEquals(1830, $diffInSeconds);
    }

    #[Test]
    public function dates_comparison_works_correctly(): void
    {
        // Create two Carbon instances
        $earlier = Carbon::parse('2023-01-01 10:00:00');
        $later = Carbon::parse('2023-01-01 10:30:00');

        // Test the isAfter method
        $this->assertTrue($later->isAfter($earlier));
        $this->assertFalse($earlier->isAfter($later));

        // Test with string representation
        $this->assertTrue($later->isAfter($earlier->toDateTimeString()));
    }
}
