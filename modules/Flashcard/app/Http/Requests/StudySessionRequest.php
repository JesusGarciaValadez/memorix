<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Flashcard\app\Models\StudySession;

final class StudySessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $studySession = StudySession::find($this->route('study_session'));

        if ($this->isMethod('POST')) {
            return $this->user()->can('create', StudySession::class);
        }

        return $studySession && $this->user()->can('update', $studySession);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
        ];

        // For update, make fields optional
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['started_at'] = ['nullable', 'date'];
            $rules['ended_at'] = ['nullable', 'date', 'after_or_equal:started_at'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'started_at.date' => 'The start time must be a valid date',
            'ended_at.date' => 'The end time must be a valid date',
            'ended_at.after_or_equal' => 'The end time must be after or equal to the start time',
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
