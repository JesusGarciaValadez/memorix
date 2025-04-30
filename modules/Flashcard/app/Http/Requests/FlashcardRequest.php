<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Flashcard\app\Models\Flashcard;

final class FlashcardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $flashcard = Flashcard::find($this->route('flashcard'));

        return $flashcard && $this->user()?->can('update', $flashcard);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'min:2', 'max:500'],
            'answer' => ['required', 'string', 'min:1', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'question.required' => 'A question is required',
            'question.min' => 'The question must be at least 2 characters',
            'question.max' => 'The question cannot exceed 500 characters',
            'answer.required' => 'An answer is required',
            'answer.min' => 'The answer must be at least 1 character',
            'answer.max' => 'The answer cannot exceed 500 characters',
        ];
    }
}
