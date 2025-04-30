<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class LogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only authenticated users can access logs
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
            'action' => ['required', 'string', 'max:100'],
            'details' => ['nullable', 'string', 'max:1000'],
            'created_at' => ['nullable', 'date'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'], // For index/latest
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
            'action.required' => 'An action is required',
            'action.max' => 'The action cannot exceed 100 characters',
            'details.max' => 'The details cannot exceed 1000 characters',
            'created_at.date' => 'The created at time must be a valid date',
            'limit.integer' => 'The limit must be an integer',
            'limit.min' => 'The limit must be at least 1',
            'limit.max' => 'The limit cannot be greater than 100',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->isMethod('POST') && ! $this->has('created_at')) {
            $this->merge([
                'created_at' => now(),
            ]);
        }
    }
}
