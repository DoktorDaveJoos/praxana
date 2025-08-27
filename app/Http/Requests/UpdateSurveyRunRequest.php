<?php

namespace App\Http\Requests;

use App\SurveyRunStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSurveyRunRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'required',
                Rule::enum(SurveyRunStatus::class)
            ],
            'started_at' => [
                'sometimes',
                'nullable',
                'date'
            ],
            'finished_at' => [
                'sometimes',
                'nullable',
                'date',
                'after_or_equal:started_at'
            ],
            'current_step_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:steps,id'
            ]
        ];
    }
}
