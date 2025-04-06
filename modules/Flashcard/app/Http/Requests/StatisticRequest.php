<?php

declare(strict_types=1);

namespace Modules\Flashcard\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Flashcard\app\Models\Statistic;

final class StatisticRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $statistic = Statistic::find($this->route('statistic'));

        if ($this->isMethod('POST')) {
            return $this->user()->can('create', Statistic::class);
        }

        if ($this->has('reset') && $this->input('reset') === true) {
            return $statistic && $this->user()->can('reset', $statistic);
        }

        return $statistic && $this->user()->can('update', $statistic);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'total_flashcards' => ['sometimes', 'integer', 'min:0'],
            'total_study_sessions' => ['sometimes', 'integer', 'min:0'],
            'total_correct_answers' => ['sometimes', 'integer', 'min:0'],
            'total_incorrect_answers' => ['sometimes', 'integer', 'min:0'],
            'reset' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'total_flashcards.integer' => 'The total flashcards must be an integer',
            'total_flashcards.min' => 'The total flashcards must be at least 0',
            'total_study_sessions.integer' => 'The total study sessions must be an integer',
            'total_study_sessions.min' => 'The total study sessions must be at least 0',
            'total_correct_answers.integer' => 'The total correct answers must be an integer',
            'total_correct_answers.min' => 'The total correct answers must be at least 0',
            'total_incorrect_answers.integer' => 'The total incorrect answers must be an integer',
            'total_incorrect_answers.min' => 'The total incorrect answers must be at least 0',
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
