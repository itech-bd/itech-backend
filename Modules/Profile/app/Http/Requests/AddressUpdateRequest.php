<?php

namespace Modules\Profile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'house_number' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'post_office' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:255'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
