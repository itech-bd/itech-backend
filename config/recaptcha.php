<?php

return [
    // Supported values: v2, v2_checkbox, v3
    'version' => (static function (): string {
        $raw = strtolower(trim((string) env('RECAPTCHA_VERSION', 'v2')));

        if ($raw === 'v3' || $raw === '3') {
            return 'v3';
        }

        return 'v2';
    })(),

    // Only used for v3. Typical values: 0.3 - 0.7
    'score_threshold' => (float) env('RECAPTCHA_SCORE_THRESHOLD', 0.5),

    'site_key' => (string) env('RECAPTCHA_SITE_KEY', ''),
    'secret_key' => (string) env('RECAPTCHA_SECRET_KEY', ''),

    'enabled' => (static function (): bool {
        $siteKey = (string) env('RECAPTCHA_SITE_KEY', '');
        $secretKey = (string) env('RECAPTCHA_SECRET_KEY', '');

        $enabled = env('RECAPTCHA_ENABLED');
        $enabledBool = $enabled === null
            ? ($siteKey !== '' && $secretKey !== '')
            : filter_var($enabled, FILTER_VALIDATE_BOOL);

        return $enabledBool && $siteKey !== '' && $secretKey !== '';
    })(),

    'verify_url' => (string) env('RECAPTCHA_VERIFY_URL', 'https://www.google.com/recaptcha/api/siteverify'),
    'timeout' => (int) env('RECAPTCHA_TIMEOUT', 5),

    // Avoid external HTTP calls in automated tests unless you explicitly want them.
    'skip_in_testing' => (static function (): bool {
        $skip = env('RECAPTCHA_SKIP_IN_TESTING');
        if ($skip === null) {
            return true;
        }

        return filter_var($skip, FILTER_VALIDATE_BOOL);
    })(),
];
