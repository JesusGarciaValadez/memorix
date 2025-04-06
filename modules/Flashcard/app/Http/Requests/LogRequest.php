<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Flashcard\app\Models\Log;

final class LogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $log = Log::find($this->route('log'));

        if ($this->isMethod('POST')) {
            return $this->user()->can('create', Log::class);
        }

        return $log && $this->user()->can('update', $log);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'max:100'],
            'details' => ['nullable', 'string', 'max:1000'],
            'created_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'action.required' => 'An action is required',
            'action.max' => 'The action cannot exceed 100 characters',
            'details.max' => 'The details cannot exceed 1000 characters',
            'created_at.date' => 'The created at time must be a valid date',
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
