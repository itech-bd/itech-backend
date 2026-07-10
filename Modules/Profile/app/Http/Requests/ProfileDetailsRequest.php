<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileDetailsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'mobile_number' => ['nullable', 'string', 'max:25'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'father_mobile' => ['nullable', 'string', 'max:25'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_mobile' => ['nullable', 'string', 'max:25'],
            'bio' => ['nullable', 'string', 'max:10000'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
