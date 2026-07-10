<?php

namespace App\Support;

class TextEncoding
{
    /**
     * Repair common UTF-8 mojibake that was misread as Windows-1252 text.
     */
    public static function repairMojibake(?string $value): ?string
    {
        if (! is_string($value) || $value === '' || ! self::looksLikeMojibake($value)) {
            return $value;
        }

        $candidate = @mb_convert_encoding($value, 'Windows-1252', 'UTF-8');

        if (! is_string($candidate) || $candidate === '' || ! mb_check_encoding($candidate, 'UTF-8')) {
            return $value;
        }

        return $candidate;
    }

    public static function looksLikeMojibake(?string $value): bool
    {
        if (! is_string($value) || $value === '') {
            return false;
        }

        return self::scoreSuspiciousFragments($value) > 0;
    }

    private static function scoreSuspiciousFragments(string $value): int
    {
        $markers = [
            'Ã',
            'Â·',
            'Â ',
            'â€',
            'â€“',
            'â€”',
            'â€¢',
            'â†',
            'à¦',
            'à§',
            'ðŸ',
            'ï¸',
        ];

        $score = 0;

        foreach ($markers as $marker) {
            $score += substr_count($value, $marker);
        }

        return $score;
    }
}
