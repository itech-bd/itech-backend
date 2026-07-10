<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class Recaptcha implements ValidationRule
{
    public function __construct(
        private readonly ?string $expectedAction = null,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! config('recaptcha.enabled')) {
            return;
        }

        if (config('recaptcha.skip_in_testing') && app()->environment('testing')) {
            return;
        }

        $secret = (string) config('recaptcha.secret_key');
        if ($secret === '') {
            $fail('Captcha is not configured.');
            return;
        }

        $verifyUrl = (string) config('recaptcha.verify_url');
        $timeout = (int) config('recaptcha.timeout', 5);

        try {
            $response = Http::asForm()
                ->timeout($timeout)
                ->post($verifyUrl, [
                    'secret' => $secret,
                    'response' => (string) $value,
                    'remoteip' => request()->ip(),
                ]);
        } catch (\Throwable $e) {
            $fail('Captcha verification failed.');
            return;
        }

        if (! $response->ok()) {
            $fail('Captcha verification failed.');
            return;
        }

        $data = $response->json();
        $success = is_array($data) ? (bool) ($data['success'] ?? false) : false;

        if (! $success) {
            $fail('Captcha verification failed.');
            return;
        }

        if (config('recaptcha.version') === 'v3') {
            if (! is_array($data) || ! array_key_exists('score', $data)) {
                $fail('Captcha verification failed.');
                return;
            }

            $score = (float) ($data['score'] ?? 0.0);
            $minScore = (float) config('recaptcha.score_threshold', 0.5);

            if ($score < $minScore) {
                $fail('Captcha verification failed.');
                return;
            }

            if ($this->expectedAction) {
                $action = (string) ($data['action'] ?? '');
                if ($action === '' || $action !== $this->expectedAction) {
                    $fail('Captcha verification failed.');
                    return;
                }
            }
        }
    }
}
