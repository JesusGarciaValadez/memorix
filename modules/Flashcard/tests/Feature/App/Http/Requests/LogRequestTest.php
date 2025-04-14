<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Http\Requests;

use App\Models\User;
use Carbon\CarbonImmutable;
use Modules\Flashcard\app\Http\Requests\LogRequest;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

final class LogRequestTest extends TestCase
{
    private LogRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();

        $this->request = new LogRequest();
    }

    #[Test]
    public function it_has_required_validation_rules(): void
    {
        $rules = $this->request->rules();

        $this->assertArrayHasKey('action', $rules);
        $this->assertContains('required', $rules['action']);
    }

    #[Test]
    public function it_has_string_validation_rules(): void
    {
        $rules = $this->request->rules();

        $this->assertContains('string', $rules['action']);
        $this->assertContains('string', $rules['details']);
    }

    #[Test]
    public function it_has_max_validation_rules(): void
    {
        $rules = $this->request->rules();

        $this->assertContains('max:100', $rules['action']);
        $this->assertContains('max:1000', $rules['details']);
    }

    #[Test]
    public function it_has_date_validation_rule_for_created_at(): void
    {
        $rules = $this->request->rules();

        $this->assertContains('date', $rules['created_at']);
    }

    #[Test]
    public function it_has_custom_validation_messages(): void
    {
        $messages = $this->request->messages();

        $this->assertArrayHasKey('action.required', $messages);
        $this->assertArrayHasKey('action.max', $messages);
        $this->assertArrayHasKey('details.max', $messages);
        $this->assertArrayHasKey('created_at.date', $messages);
    }

    #[Test]
    public function it_prepares_for_validation_by_setting_created_at_for_new_logs(): void
    {
        // Set method to POST in the request
        $request = new LogRequest();
        $request->setMethod('POST');

        // Call the protected method using reflection
        $reflectionClass = new ReflectionClass(LogRequest::class);
        $prepareMethod = $reflectionClass->getMethod('prepareForValidation');
        $prepareMethod->setAccessible(true);
        $prepareMethod->invoke($request);

        // Check that created_at was set
        $this->assertTrue($request->has('created_at'));
        $createdAt = $request->input('created_at');
        $this->assertTrue(
            $createdAt instanceof CarbonImmutable || $createdAt instanceof \Carbon\Carbon,
            'Created at should be a Carbon instance'
        );
    }

    #[Test]
    public function it_does_not_change_created_at_for_update_requests(): void
    {
        // Set method to PUT in the request and add a created_at value
        $request = new LogRequest();
        $request->setMethod('PUT');
        $createdAt = now()->subDay();
        $request->merge(['created_at' => $createdAt]);

        // Call the protected method using reflection
        $reflectionClass = new ReflectionClass(LogRequest::class);
        $prepareMethod = $reflectionClass->getMethod('prepareForValidation');
        $prepareMethod->setAccessible(true);
        $prepareMethod->invoke($request);

        // Check that created_at was not changed
        $this->assertEquals($createdAt, $request->input('created_at'));
    }
}
