<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Store frontend section request.
 *
 * @category Request
 * @package  App\Http\Requests\Admin
 * @author   Unknown <unknown@example.invalid>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://laravel.com
 */
class StoreFrontendSectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && method_exists($user, 'hasRole') && $user->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'section_key' => ['required', 'string', 'max:64', 'alpha_dash'],

            'title_bn' => ['nullable', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],

            'content_bn' => ['nullable', 'string'],
            'content_en' => ['nullable', 'string'],

            'image' => ['nullable', 'image', 'max:2048'],

            'icon' => [
                'nullable',
                'string',
                'max:80',
                'regex:/^(code|search|dotnet|design|sparkles|rocket|chart|shield|fa-(solid|regular|brands)\s+fa-[a-z0-9-]+)$/',
            ],

            'button_text_bn' => ['nullable', 'string', 'max:255'],
            'button_text_en' => ['nullable', 'string', 'max:255'],
            'button_link' => ['nullable', 'string', 'max:255'],

            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
