<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Flashcard\app\Http\Requests\FlashcardRequest;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardRequestTest extends TestCase
{
    use RefreshDatabase;

    private FlashcardRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();

        $this->request = new FlashcardRequest();
    }

    #[Test]
    public function it_has_required_validation_rules(): void
    {
        $rules = $this->request->rules();

        $this->assertArrayHasKey('question', $rules);
        $this->assertArrayHasKey('answer', $rules);
        $this->assertIsArray($rules['question']);
        $this->assertIsArray($rules['answer']);
        $this->assertContains('required', $rules['question']);
        $this->assertContains('required', $rules['answer']);
    }

    #[Test]
    public function it_has_min_validation_rules(): void
    {
        $rules = $this->request->rules();

        $this->assertIsArray($rules['question']);
        $this->assertIsArray($rules['answer']);
        $this->assertContains('min:2', $rules['question']);
        $this->assertContains('min:1', $rules['answer']);
    }

    #[Test]
    public function it_has_custom_validation_messages(): void
    {
        $messages = $this->request->messages();

        $this->assertArrayHasKey('question.required', $messages);
        $this->assertArrayHasKey('question.min', $messages);
        $this->assertArrayHasKey('question.max', $messages);
        $this->assertArrayHasKey('answer.required', $messages);
        $this->assertArrayHasKey('answer.min', $messages);
        $this->assertArrayHasKey('answer.max', $messages);
    }
}
