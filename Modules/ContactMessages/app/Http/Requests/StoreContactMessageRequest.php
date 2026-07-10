<?php

namespace Modules\ContactMessages\Http\Requests;

use App\Rules\Recaptcha;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'phone'   => ['required', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ];

        $shouldRequireRecaptcha = config('recaptcha.enabled')
            && ! (config('recaptcha.skip_in_testing') && app()->environment('testing'));

        if ($shouldRequireRecaptcha) {
            $rules['g-recaptcha-response'] = ['required', new Recaptcha('contact')];
        }

        return $rules;
    }
}
