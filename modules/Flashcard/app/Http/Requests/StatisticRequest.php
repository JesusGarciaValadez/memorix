<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StatisticRequest extends FormRequest
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
            // No specific rules needed for fetching statistics as they are read-only based on auth user
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
            // No specific messages needed
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert string boolean values to actual booleans
        if ($this->has('reset') && is_string($this->input('reset'))) {
            $resetValue = mb_strtolower($this->input('reset'));
            $this->merge([
                'reset' => in_array($resetValue, ['true', '1', 'yes'], true),
            ]);
        }
    }
}
