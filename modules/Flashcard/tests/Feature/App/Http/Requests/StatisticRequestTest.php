<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Http\Requests;

use App\Models\User;
use Modules\Flashcard\app\Http\Requests\StatisticRequest;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

final class StatisticRequestTest extends TestCase
{
    private StatisticRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();

        $this->request = new StatisticRequest();
    }

    #[Test]
    public function it_has_integer_validation_rules(): void
    {
        $rules = $this->request->rules();

        $this->assertContains('integer', $rules['total_flashcards']);
        $this->assertContains('integer', $rules['total_study_sessions']);
        $this->assertContains('integer', $rules['total_correct_answers']);
        $this->assertContains('integer', $rules['total_incorrect_answers']);
    }

    #[Test]
    public function it_has_min_validation_rules(): void
    {
        $rules = $this->request->rules();

        $this->assertContains('min:0', $rules['total_flashcards']);
        $this->assertContains('min:0', $rules['total_study_sessions']);
        $this->assertContains('min:0', $rules['total_correct_answers']);
        $this->assertContains('min:0', $rules['total_incorrect_answers']);
    }

    #[Test]
    public function it_has_boolean_validation_rule_for_reset(): void
    {
        $rules = $this->request->rules();

        $this->assertContains('boolean', $rules['reset']);
    }

    #[Test]
    public function it_has_custom_validation_messages(): void
    {
        $messages = $this->request->messages();

        $this->assertArrayHasKey('total_flashcards.integer', $messages);
        $this->assertArrayHasKey('total_flashcards.min', $messages);
        $this->assertArrayHasKey('total_study_sessions.integer', $messages);
        $this->assertArrayHasKey('total_study_sessions.min', $messages);
        $this->assertArrayHasKey('total_correct_answers.integer', $messages);
        $this->assertArrayHasKey('total_correct_answers.min', $messages);
        $this->assertArrayHasKey('total_incorrect_answers.integer', $messages);
        $this->assertArrayHasKey('total_incorrect_answers.min', $messages);
    }

    #[Test]
    public function it_converts_string_reset_values_to_boolean_during_preparation(): void
    {
        // Test with 'true' string value
        $request = new StatisticRequest();
        $request->merge(['reset' => 'true']);

        // Call the protected method using reflection
        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        // Check that reset was converted to boolean true
        $this->assertIsBool($request->input('reset'));
        $this->assertTrue($request->input('reset'));

        // Test with 'false' string value
        $request->merge(['reset' => 'false']);
        $method->invoke($request);

        // Check that reset was converted to boolean false
        $this->assertIsBool($request->input('reset'));
        $this->assertFalse($request->input('reset'));

        // Test with '1' string value
        $request->merge(['reset' => '1']);
        $method->invoke($request);

        // Check that reset was converted to boolean true
        $this->assertIsBool($request->input('reset'));
        $this->assertTrue($request->input('reset'));
    }
}
