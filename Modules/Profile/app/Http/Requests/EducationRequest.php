<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EducationRequest extends FormRequest
{
    public function rules(): array
    {
        $currentYear = (int) date('Y');

        return [
            'degree_name' => ['required', 'string', 'max:255'],
            'institute_name' => ['required', 'string', 'max:255'],
            'board_or_university' => ['nullable', 'string', 'max:255'],
            'start_year' => ['nullable', 'integer', 'min:1950', 'max:' . ($currentYear + 1)],
            'end_year' => ['nullable', 'integer', 'min:1950', 'max:' . ($currentYear + 10)],
            'result_or_grade' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
