<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'frequency' => ['required', 'string', Rule::in(['daily', 'weekly', 'monthly'])],
            'due_date' => ['nullable', 'date'],
            'duration_days' => ['nullable', 'integer', 'min:1', 'max:366'],
            'realization_time' => ['nullable', 'date_format:H:i'],

        ];
    }
}