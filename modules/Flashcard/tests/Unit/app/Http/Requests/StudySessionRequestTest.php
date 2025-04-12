<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Unit\app\Http\Requests;

use App\Models\User;
use Carbon\CarbonImmutable;
use Modules\Flashcard\app\Http\Requests\StudySessionRequest;
use Modules\Flashcard\app\Models\StudySession;
use Modules\Flashcard\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;

final class StudySessionRequestTest extends TestCase
{
    private StudySession $studySession;

    private StudySessionRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->studySession = StudySession::factory()->create([
            'user_id' => $user->id,
            'started_at' => now()->subHour(),
            'ended_at' => null,
        ]);

        $this->request = new StudySessionRequest();
    }

    #[Test]
    public function it_has_date_validation_rules(): void
    {
        $rules = $this->request->rules();

        $this->assertArrayHasKey('started_at', $rules);
        $this->assertArrayHasKey('ended_at', $rules);
        $this->assertContains('date', $rules['started_at']);
        $this->assertContains('date', $rules['ended_at']);
    }

    #[Test]
    public function it_has_after_or_equal_validation_rule_for_ended_at(): void
    {
        $rules = $this->request->rules();

        $this->assertContains('after_or_equal:started_at', $rules['ended_at']);
    }

    #[Test]
    public function it_has_custom_validation_messages(): void
    {
        $messages = $this->request->messages();

        $this->assertArrayHasKey('started_at.date', $messages);
        $this->assertArrayHasKey('ended_at.date', $messages);
        $this->assertArrayHasKey('ended_at.after_or_equal', $messages);
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
