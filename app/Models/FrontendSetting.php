<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Frontend setting model.
 *
 * @category Model
 * @package  App\Models
 * @author   Unknown <unknown@example.invalid>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://laravel.com
 */
class FrontendSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value_en',
        'value_bn',
    ];

    /**
     * Get default frontend setting values for the given locale.
     *
     * @param string|null $locale Locale code.
     *
     * @return array<string, string|null>
     */
    public static function defaultValues(?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        $isBangla = $locale === 'bn';

        return [
            'site_address' => $isBangla ? 'ঢাকা, বাংলাদেশ' : 'Dhaka, Bangladesh',
            'site_phone' => '+880 10 0000 0000',
            'site_email' => 'info@example.com',
            'site_logo_path' => null,
            'site_favicon_path' => null,
            'footer_brand_tagline' => $isBangla
                ? 'ট্রেনিং ইনস্টিটিউট • ক্যারিয়ার-ফোকাসড'
                : 'Training Institute • Career-focused',
            'footer_brand_description' => $isBangla
                ? 'ক্যারিয়ার-ফোকাসড টেক ও ক্রিয়েটিভ স্কিলের জন্য ট্রেনিং ইনস্টিটিউট। প্র্যাকটিক্যাল প্রজেক্ট, রিভিউ এবং নিয়মিত মেন্টর সাপোর্টের মাধ্যমে শিখুন।'
                : 'Training institute for career-focused tech & creative skills. Learn with practical projects, reviews, and ongoing mentor support.',
            'footer_updates_title' => $isBangla ? 'আপডেট পান' : 'Get updates',
            'footer_updates_subtitle' => $isBangla
                ? 'ব্যাচ আপডেট ও ওয়ার্কশপ নিউজ পেতে আপনার ইমেইল দিন।'
                : 'Drop your email to get batch updates and workshop news.',
            'footer_contact_title' => $isBangla ? 'যোগাযোগের তথ্য' : 'Contact Info',
            'footer_phone_label' => $isBangla ? 'ফোন' : 'Phone',
            'footer_email_label' => $isBangla ? 'ইমেইল' : 'Email',
            'footer_location_label' => $isBangla ? 'লোকেশন' : 'Location',
            'footer_copyright' => $isBangla ? 'সর্বস্বত্ব সংরক্ষিত।' : 'All rights reserved.',
            'footer_facebook_url' => '#',
            'footer_linkedin_url' => '#',
            'footer_youtube_url' => '#',
        ];
    }

    /**
     * Get value for current locale with fallback.
     *
     * @param string|null $locale Locale code.
     *
     * @return string|null
     */
    public function localizedValue(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        if ($locale === 'bn') {
            return $this->value_bn ?: $this->value_en;
        }

        return $this->value_en ?: $this->value_bn;
    }

    /**
     * Cached settings keyed by `key`.
     *
     * @return Collection<string, self>
     */
    public static function getCachedKeyed(): Collection
    {
        /**
         * Cached collection.
         *
         * @var Collection<string, self> $keyed
         */
        $keyed = Cache::rememberForever(
            'frontend_settings.keyed',
            function () {
                return self::query()->get()->keyBy('key');
            }
        );

        return $keyed;
    }

    /**
     * Forget settings cache.
     *
     * @return void
     */
    public static function forgetCache(): void
    {
        Cache::forget('frontend_settings.keyed');
    }
}
