<?php

namespace App\Providers;

use App\Models\FrontendSetting;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Application service provider.
 *
 * @category Provider
 * @package  App\Providers
 * @author   Unknown <unknown@example.invalid>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://laravel.com
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        View::composer(
            ['layouts.site', 'layouts.app', 'layouts.guest', 'layouts.public-profile'],
            function ($view) {
                $defaults = [
                    ...FrontendSetting::defaultValues(),
                ];

                if (! Schema::hasTable('frontend_settings')) {
                    $view->with('frontendSettings', $defaults);

                    return;
                }

                $keyed = FrontendSetting::getCachedKeyed();

                $get = function (string $key) use ($keyed, $defaults) {
                    $setting = $keyed->get($key);

                    return $setting ? $setting->localizedValue() : $defaults[$key];
                };

                $frontendSettings = [
                    'site_address' => $get('site_address'),
                    'site_phone' => $get('site_phone'),
                    'site_email' => $get('site_email'),
                    'site_logo_path' => $get('site_logo_path'),
                    'site_favicon_path' => $get('site_favicon_path'),
                    'footer_brand_tagline' => $get('footer_brand_tagline'),
                    'footer_brand_description' => $get('footer_brand_description'),
                    'footer_updates_title' => $get('footer_updates_title'),
                    'footer_updates_subtitle' => $get('footer_updates_subtitle'),
                    'footer_contact_title' => $get('footer_contact_title'),
                    'footer_phone_label' => $get('footer_phone_label'),
                    'footer_email_label' => $get('footer_email_label'),
                    'footer_location_label' => $get('footer_location_label'),
                    'footer_copyright' => $get('footer_copyright'),
                    'footer_facebook_url' => $get('footer_facebook_url'),
                    'footer_linkedin_url' => $get('footer_linkedin_url'),
                    'footer_youtube_url' => $get('footer_youtube_url'),
                ];

                $view->with('frontendSettings', $frontendSettings);
            }
        );
    }
}
