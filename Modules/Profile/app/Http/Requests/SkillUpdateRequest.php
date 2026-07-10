<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SkillUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'proficiency_level' => ['required', Rule::in(['beginner', 'intermediate', 'expert'])],
        ];
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
