<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFrontendFooterSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && method_exists($user, 'hasRole')
            ? $user->hasRole('admin')
            : false;
    }

    public function rules(): array
    {
        return [
            'footer_brand_tagline_en' => ['required', 'string', 'max:255'],
            'footer_brand_tagline_bn' => ['required', 'string', 'max:255'],
            'footer_brand_description_en' => ['required', 'string'],
            'footer_brand_description_bn' => ['required', 'string'],
            'footer_updates_title_en' => ['required', 'string', 'max:255'],
            'footer_updates_title_bn' => ['required', 'string', 'max:255'],
            'footer_updates_subtitle_en' => ['required', 'string', 'max:500'],
            'footer_updates_subtitle_bn' => ['required', 'string', 'max:500'],
            'footer_contact_title_en' => ['required', 'string', 'max:255'],
            'footer_contact_title_bn' => ['required', 'string', 'max:255'],
            'footer_phone_label_en' => ['required', 'string', 'max:100'],
            'footer_phone_label_bn' => ['required', 'string', 'max:100'],
            'footer_email_label_en' => ['required', 'string', 'max:100'],
            'footer_email_label_bn' => ['required', 'string', 'max:100'],
            'footer_location_label_en' => ['required', 'string', 'max:100'],
            'footer_location_label_bn' => ['required', 'string', 'max:100'],
            'footer_copyright_en' => ['required', 'string', 'max:255'],
            'footer_copyright_bn' => ['required', 'string', 'max:255'],
            'footer_facebook_url' => ['nullable', 'url', 'max:255'],
            'footer_linkedin_url' => ['nullable', 'url', 'max:255'],
            'footer_youtube_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}