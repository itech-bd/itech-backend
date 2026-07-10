<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PublicUrlUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'public_url' => [
                'nullable',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('user_profiles', 'public_url')
                    ->ignore($this->user()?->id, 'user_id'),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $value = $this->input('public_url');

        if (!is_string($value)) {
            return;
        }

        $value = trim($value);

        if ($value === '') {
            $this->merge(['public_url' => null]);
            return;
        }

        $this->merge(['public_url' => Str::slug($value)]);
    }
}
