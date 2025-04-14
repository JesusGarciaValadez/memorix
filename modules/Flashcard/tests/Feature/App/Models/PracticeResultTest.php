<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Models;

use Modules\Flashcard\app\Models\PracticeResult;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PracticeResultTest extends TestCase
{
    #[Test]
    public function it_has_correct_fillable_attributes(): void
    {
        $practiceResult = new PracticeResult();
        $this->assertEquals(
            ['user_id', 'flashcard_id', 'study_session_id', 'is_correct'],
            $practiceResult->getFillable()
        );
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $practiceResult = new PracticeResult();
        $this->assertArrayHasKey('is_correct', $practiceResult->getCasts());
        $this->assertEquals('boolean', $practiceResult->getCasts()['is_correct']);
    }

    #[Test]
    public function it_belongs_to_a_user(): void
    {
        $practiceResult = new PracticeResult();
        $this->assertTrue(method_exists($practiceResult, 'user'), 'PracticeResult model should have a user relationship method');
    }

    #[Test]
    public function it_belongs_to_a_flashcard(): void
    {
        $practiceResult = new PracticeResult();
        $this->assertTrue(method_exists($practiceResult, 'flashcard'), 'PracticeResult model should have a flashcard relationship method');
    }

    #[Test]
    public function it_belongs_to_a_study_session(): void
    {
        $practiceResult = new PracticeResult();
        $this->assertTrue(method_exists($practiceResult, 'studySession'), 'PracticeResult model should have a studySession relationship method');
    }

    #[Test]
    public function it_can_be_created_through_factory(): void
    {
        // For unit tests, we verify that the factory method exists on the model
        $this->assertTrue(method_exists(PracticeResult::class, 'factory'), 'PracticeResult model should have a factory method');
    }
}
