<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SkillStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'skill_name' => ['required', 'string', 'max:80'],
            'proficiency_level' => ['required', Rule::in(['beginner', 'intermediate', 'expert'])],
        ];
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
