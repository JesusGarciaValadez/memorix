<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StudySessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow all authenticated users for now
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            // For recording practice result
            'is_correct' => 'sometimes|boolean',
            // For resetting practice
            // No specific rules needed for starting, ending, getting flashcards
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
            'is_correct.boolean' => 'The is_correct field must be true or false.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->isMethod('POST') && ! $this->has('started_at')) {
            $this->merge([
                'started_at' => now(),
            ]);
        }
    }
}
