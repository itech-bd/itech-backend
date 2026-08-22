<?php

namespace Modules\Profile\Http\Requests;

use App\Support\Accounts;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

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
                ...Accounts::publicUrlUniqueRules($this->user()),
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
