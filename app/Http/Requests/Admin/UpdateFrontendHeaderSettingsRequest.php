<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate frontend header settings updates.
 *
 * @category Request
 * @package  App\Http\Requests\Admin
 * @author   Unknown <unknown@example.invalid>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://laravel.com
 */
class UpdateFrontendHeaderSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && method_exists($user, 'hasRole')
            ? $user->hasRole('admin')
            : false;
    }

    /**
     * Get validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'site_address_en' => ['required', 'string', 'max:255'],
            'site_address_bn' => ['required', 'string', 'max:255'],
            'site_phone' => ['required', 'string', 'max:50'],
            'site_email' => ['required', 'email', 'max:255'],
            'site_logo' => ['nullable', 'image', 'max:2048'],
            'site_favicon' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,svg,webp', 'max:1024'],
        ];
    }
}
