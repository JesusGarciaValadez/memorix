<?php

declare(strict_types=1);

namespace Modules\Flashcard\tests\Feature\app\Http\Requests;

use App\Models\User;
use Modules\Flashcard\app\Http\Requests\FlashcardRequest;
use Modules\Flashcard\app\Models\Flashcard;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class FlashcardRequestTest extends TestCase
{
    private Flashcard $flashcard;

    private FlashcardRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->flashcard = Flashcard::factory()->create([
            'user_id' => $user->id,
            'question' => 'What is Laravel?',
            'answer' => 'A PHP framework',
        ]);

        $this->request = new FlashcardRequest();
    }

    #[Test]
    public function it_has_required_validation_rules(): void
    {
        $rules = $this->request->rules();

        $this->assertArrayHasKey('question', $rules);
        $this->assertArrayHasKey('answer', $rules);
        $this->assertContains('required', $rules['question']);
        $this->assertContains('required', $rules['answer']);
    }

    #[Test]
    public function it_has_string_validation_rules(): void
    {
        $rules = $this->request->rules();

        $this->assertContains('string', $rules['question']);
        $this->assertContains('string', $rules['answer']);
    }

    #[Test]
    public function it_has_min_validation_rules(): void
    {
        $rules = $this->request->rules();

        $this->assertContains('min:2', $rules['question']);
        $this->assertContains('min:1', $rules['answer']);
    }

    #[Test]
    public function it_has_max_validation_rules(): void
    {
        $rules = $this->request->rules();

        $this->assertContains('max:500', $rules['question']);
        $this->assertContains('max:500', $rules['answer']);
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
