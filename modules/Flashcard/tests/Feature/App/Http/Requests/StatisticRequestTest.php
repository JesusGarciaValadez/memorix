<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Http\Requests\StatisticRequest;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

final class StatisticRequestTest extends TestCase
{
    use RefreshDatabase;

    private StatisticRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();

        $this->request = new StatisticRequest();
    }

    #[Test]
    public function it_has_custom_validation_messages(): void
    {
        $messages = $this->request->messages();

        $this->assertEmpty($messages);
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
