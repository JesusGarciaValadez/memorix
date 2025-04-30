<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Http\Requests;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Http\Requests\StudySessionRequest;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

final class StudySessionRequestTest extends TestCase
{
    use RefreshDatabase;

    private StudySessionRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();

        $this->request = new StudySessionRequest();
    }

    #[Test]
    public function it_has_boolean_validation_rule_for_is_correct(): void
    {
        $rules = $this->request->rules();

        $this->assertArrayHasKey('is_correct', $rules);
        $isCorrectRule = $rules['is_correct'];
        $this->assertIsString($isCorrectRule); // Ensure it's a string
        $this->assertStringContainsString('boolean', $isCorrectRule);
        $this->assertStringContainsString('sometimes', $isCorrectRule);
    }

    #[Test]
    public function it_has_custom_validation_messages(): void
    {
        $messages = $this->request->messages();

        $this->assertArrayHasKey('is_correct.boolean', $messages);
    }

    #[Test]
    public function it_prepares_for_validation_by_setting_started_at_for_new_sessions(): void
    {
        // Set method to POST in the request
        $request = new StudySessionRequest();
        $request->setMethod('POST');

        // Call the protected method using reflection
        $reflectionClass = new ReflectionClass(StudySessionRequest::class);
        $prepareMethod = $reflectionClass->getMethod('prepareForValidation');
        $prepareMethod->setAccessible(true);
        $prepareMethod->invoke($request);

        // Check that started_at was set
        $this->assertTrue($request->has('started_at'));
        $startedAt = $request->input('started_at');
        $this->assertTrue(
            $startedAt instanceof CarbonImmutable || $startedAt instanceof \Carbon\Carbon,
            'Started at should be a Carbon instance'
        );
    }

    #[Test]
    public function it_does_not_change_started_at_for_update_requests(): void
    {
        // Set method to PUT in the request and add a started_at value
        $request = new StudySessionRequest();
        $request->setMethod('PUT');
        $startedAt = now()->subDays(2);
        $request->merge(['started_at' => $startedAt]);

        // Call the protected method using reflection
        $reflectionClass = new ReflectionClass(StudySessionRequest::class);
        $prepareMethod = $reflectionClass->getMethod('prepareForValidation');
        $prepareMethod->setAccessible(true);
        $prepareMethod->invoke($request);

        // Check that started_at was not changed
        $this->assertEquals($startedAt, $request->input('started_at'));
    }
}
