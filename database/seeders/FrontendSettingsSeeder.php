<?php

namespace Database\Seeders;

use App\Models\FrontendSetting;
use Illuminate\Database\Seeder;

/**
 * Seed default frontend settings.
 *
 * @category Database
 * @package  Database\Seeders
 * @author   Unknown <unknown@example.invalid>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://laravel.com
 */
class FrontendSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $defaults = [
            'site_address' => [
                'value_en' => 'Dhaka, Bangladesh',
                'value_bn' => 'ঢাকা, বাংলাদেশ',
            ],
            'site_phone' => [
                'value_en' => '+880 10 0000 0000',
                'value_bn' => '+880 10 0000 0000',
            ],
            'site_email' => [
                'value_en' => 'info@example.com',
                'value_bn' => 'info@example.com',
            ],
            'site_logo_path' => [
                'value_en' => 'logo/itechbd-logo.svg',
                'value_bn' => 'logo/itechbd-logo.svg',
            ],
            'site_favicon_path' => [
                'value_en' => null,
                'value_bn' => null,
            ],
        ];

        foreach ($defaults as $key => $values) {
            $setting = FrontendSetting::query()->firstOrCreate(
                ['key' => $key],
                array_merge(['key' => $key], $values)
            );

            $needsUpdate = false;
            $updateValues = [];

            if (($setting->value_en === null || $setting->value_en === '')
                && isset($values['value_en'])
                && $values['value_en'] !== null
                && $values['value_en'] !== ''
            ) {
                $needsUpdate = true;
                $updateValues['value_en'] = $values['value_en'];
            }

            if (($setting->value_bn === null || $setting->value_bn === '')
                && isset($values['value_bn'])
                && $values['value_bn'] !== null
                && $values['value_bn'] !== ''
            ) {
                $needsUpdate = true;
                $updateValues['value_bn'] = $values['value_bn'];
            }

            if ($needsUpdate) {
                $setting->update($updateValues);
            }
        }

        FrontendSetting::forgetCache();
    }
}
