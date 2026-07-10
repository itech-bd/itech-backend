<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 leading-tight">Frontend Editor</h2>
            <p class="mt-1 text-sm text-slate-500">Edit frontend page sections (BN + EN).</p>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @php
        $isHeaderTab = ($tab ?? 'pages') === 'header';
        $isFooterTab = ($tab ?? 'pages') === 'footer';
        $footerDefaultsEn = \App\Models\FrontendSetting::defaultValues('en');
        $footerDefaultsBn = \App\Models\FrontendSetting::defaultValues('bn');
        $footerFieldValue = function (string $key, string $locale) use ($settings, $footerDefaultsEn, $footerDefaultsBn) {
            $column = $locale === 'bn' ? 'value_bn' : 'value_en';
            $fallbacks = $locale === 'bn' ? $footerDefaultsBn : $footerDefaultsEn;
            $setting = $settings->get($key);

            return $setting?->{$column} ?? ($fallbacks[$key] ?? '');
        };
    @endphp

    <div class="mb-6 flex flex-wrap items-center gap-2">
        @foreach($pages as $page)
            @php
                $isActive = ! $isHeaderTab && $selectedPage->id === $page->id;
                $base = 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold transition';
                $active = 'bg-indigo-600 text-white';
                $inactive = 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50';
            @endphp

            <a href="/admin/frontend-editor?page={{ $page->slug }}"
               class="{{ $base }} {{ $isActive ? $active : $inactive }}">
                {{ ucfirst($page->slug) }}
            </a>
        @endforeach

        @php
            $base = 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold transition';
            $active = 'bg-indigo-600 text-white';
            $inactive = 'bg-white text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50';
        @endphp
        <a href="/admin/frontend-editor?tab=header"
           class="{{ $base }} {{ $isHeaderTab ? $active : $inactive }}">
            Header Settings
        </a>
        <a href="/admin/frontend-editor?tab=footer"
           class="{{ $base }} {{ $isFooterTab ? $active : $inactive }}">
            Footer Settings
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6">
        @if($isHeaderTab)
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Header Settings</h3>
                    <p class="mt-1 text-sm text-slate-500">Update top header address, phone, email and logo.</p>
                </div>

                @if (!\Illuminate\Support\Facades\Schema::hasTable('frontend_settings'))
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        Frontend settings table not found. Run migrations first.
                    </div>
                @else
                    <form method="POST"
                          action="{{ route('admin.frontend-editor.header-settings.update') }}"
                          enctype="multipart/form-data"
                          class="grid grid-cols-1 gap-6">
                        @csrf
                        @method('PATCH')

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Address (EN)</label>
                                <input
                                    name="site_address_en"
                                    value="{{ old('site_address_en', optional($settings->get('site_address'))->value_en) }}"
                                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    required
                                />
                                @error('site_address_en')
                                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Address (BN)</label>
                                <input
                                    name="site_address_bn"
                                    value="{{ old('site_address_bn', optional($settings->get('site_address'))->value_bn) }}"
                                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    required
                                />
                                @error('site_address_bn')
                                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Phone</label>
                                <input
                                    name="site_phone"
                                    value="{{ old('site_phone', optional($settings->get('site_phone'))->value_en) }}"
                                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    required
                                />
                                @error('site_phone')
                                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Email</label>
                                <input
                                    type="email"
                                    name="site_email"
                                    value="{{ old('site_email', optional($settings->get('site_email'))->value_en) }}"
                                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    required
                                />
                                @error('site_email')
                                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Logo Upload</label>
                            @php
                                $logoPath = optional($settings->get('site_logo_path'))->value_en
                                    ?: optional($settings->get('site_logo_path'))->value_bn;
                            @endphp

                            @if ($logoPath)
                                <div class="mt-3 flex items-center gap-4">
                                    <img
                                        src="{{ asset('storage/' . $logoPath) }}"
                                        alt="Site logo"
                                        class="h-12 w-auto rounded bg-slate-50 ring-1 ring-slate-200"
                                    />
                                    <div class="text-xs text-slate-500">Current logo</div>
                                </div>
                            @endif

                            <input
                                type="file"
                                name="site_logo"
                                accept="image/*"
                                class="mt-3 block w-full text-sm text-slate-700"
                            />
                            @error('site_logo')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror

                            <p class="mt-2 text-xs text-slate-500">
                                Stored in <span class="font-mono">storage/app/public/logo</span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Favicon Upload</label>
                            @php
                                $faviconPath = optional($settings->get('site_favicon_path'))->value_en
                                    ?: optional($settings->get('site_favicon_path'))->value_bn;
                            @endphp

                            @if ($faviconPath)
                                <div class="mt-3 flex items-center gap-4">
                                    <img
                                        src="{{ asset('storage/' . $faviconPath) }}"
                                        alt="Site favicon"
                                        class="h-10 w-10 rounded bg-slate-50 ring-1 ring-slate-200 object-contain"
                                    />
                                    <div class="text-xs text-slate-500">Current favicon</div>
                                </div>
                            @endif

                            <input
                                type="file"
                                name="site_favicon"
                                accept=".ico,image/png,image/jpeg,image/webp,image/svg+xml"
                                class="mt-3 block w-full text-sm text-slate-700"
                            />
                            @error('site_favicon')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror

                            <p class="mt-2 text-xs text-slate-500">
                                Stored in <span class="font-mono">storage/app/public/favicon</span>
                            </p>
                        </div>

                        <div class="flex items-center justify-end">
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                            >
                                Save Settings
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        @elseif($isFooterTab)
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Footer Settings</h3>
                    <p class="mt-1 text-sm text-slate-500">Update footer copy, contact labels, social links, and copyright text.</p>
                </div>

                @if (!\Illuminate\Support\Facades\Schema::hasTable('frontend_settings'))
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        Frontend settings table not found. Run migrations first.
                    </div>
                @else
                    <form method="POST"
                          action="{{ route('admin.frontend-editor.footer-settings.update') }}"
                          class="grid grid-cols-1 gap-6">
                        @csrf
                        @method('PATCH')

                        <div class="rounded-xl border border-slate-200 p-5">
                            <h4 class="text-base font-semibold text-slate-900">Brand Copy</h4>
                            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Brand Tagline (EN)</label>
                                    <input name="footer_brand_tagline_en" value="{{ old('footer_brand_tagline_en', $footerFieldValue('footer_brand_tagline', 'en')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                                    @error('footer_brand_tagline_en')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Brand Tagline (BN)</label>
                                    <input name="footer_brand_tagline_bn" value="{{ old('footer_brand_tagline_bn', $footerFieldValue('footer_brand_tagline', 'bn')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                                    @error('footer_brand_tagline_bn')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Brand Description (EN)</label>
                                    <textarea name="footer_brand_description_en" rows="4" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>{{ old('footer_brand_description_en', $footerFieldValue('footer_brand_description', 'en')) }}</textarea>
                                    @error('footer_brand_description_en')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Brand Description (BN)</label>
                                    <textarea name="footer_brand_description_bn" rows="4" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>{{ old('footer_brand_description_bn', $footerFieldValue('footer_brand_description', 'bn')) }}</textarea>
                                    @error('footer_brand_description_bn')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-5">
                            <h4 class="text-base font-semibold text-slate-900">Updates Card</h4>
                            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Updates Title (EN)</label>
                                    <input name="footer_updates_title_en" value="{{ old('footer_updates_title_en', $footerFieldValue('footer_updates_title', 'en')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                                    @error('footer_updates_title_en')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Updates Title (BN)</label>
                                    <input name="footer_updates_title_bn" value="{{ old('footer_updates_title_bn', $footerFieldValue('footer_updates_title', 'bn')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                                    @error('footer_updates_title_bn')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Updates Subtitle (EN)</label>
                                    <textarea name="footer_updates_subtitle_en" rows="3" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>{{ old('footer_updates_subtitle_en', $footerFieldValue('footer_updates_subtitle', 'en')) }}</textarea>
                                    @error('footer_updates_subtitle_en')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Updates Subtitle (BN)</label>
                                    <textarea name="footer_updates_subtitle_bn" rows="3" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>{{ old('footer_updates_subtitle_bn', $footerFieldValue('footer_updates_subtitle', 'bn')) }}</textarea>
                                    @error('footer_updates_subtitle_bn')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-5">
                            <h4 class="text-base font-semibold text-slate-900">Contact Area</h4>
                            <div class="mt-4 grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Contact Title (EN)</label>
                                    <input name="footer_contact_title_en" value="{{ old('footer_contact_title_en', $footerFieldValue('footer_contact_title', 'en')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                                    @error('footer_contact_title_en')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Contact Title (BN)</label>
                                    <input name="footer_contact_title_bn" value="{{ old('footer_contact_title_bn', $footerFieldValue('footer_contact_title', 'bn')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                                    @error('footer_contact_title_bn')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Phone Label (EN)</label>
                                    <input name="footer_phone_label_en" value="{{ old('footer_phone_label_en', $footerFieldValue('footer_phone_label', 'en')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                                    @error('footer_phone_label_en')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Phone Label (BN)</label>
                                    <input name="footer_phone_label_bn" value="{{ old('footer_phone_label_bn', $footerFieldValue('footer_phone_label', 'bn')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                                    @error('footer_phone_label_bn')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Email Label (EN)</label>
                                    <input name="footer_email_label_en" value="{{ old('footer_email_label_en', $footerFieldValue('footer_email_label', 'en')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                                    @error('footer_email_label_en')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Email Label (BN)</label>
                                    <input name="footer_email_label_bn" value="{{ old('footer_email_label_bn', $footerFieldValue('footer_email_label', 'bn')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                                    @error('footer_email_label_bn')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Location Label (EN)</label>
                                    <input name="footer_location_label_en" value="{{ old('footer_location_label_en', $footerFieldValue('footer_location_label', 'en')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                                    @error('footer_location_label_en')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Location Label (BN)</label>
                                    <input name="footer_location_label_bn" value="{{ old('footer_location_label_bn', $footerFieldValue('footer_location_label', 'bn')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                                    @error('footer_location_label_bn')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-slate-500">Phone, email, and address values still come from Header Settings so they stay in sync across the site.</p>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-5">
                            <h4 class="text-base font-semibold text-slate-900">Social Links & Copyright</h4>
                            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Facebook URL</label>
                                    <input type="url" name="footer_facebook_url" value="{{ old('footer_facebook_url', $footerFieldValue('footer_facebook_url', 'en')) }}" placeholder="https://facebook.com/your-page" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    @error('footer_facebook_url')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">LinkedIn URL</label>
                                    <input type="url" name="footer_linkedin_url" value="{{ old('footer_linkedin_url', $footerFieldValue('footer_linkedin_url', 'en')) }}" placeholder="https://linkedin.com/company/your-page" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    @error('footer_linkedin_url')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">YouTube URL</label>
                                    <input type="url" name="footer_youtube_url" value="{{ old('footer_youtube_url', $footerFieldValue('footer_youtube_url', 'en')) }}" placeholder="https://youtube.com/@your-channel" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    @error('footer_youtube_url')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Copyright (EN)</label>
                                    <input name="footer_copyright_en" value="{{ old('footer_copyright_en', $footerFieldValue('footer_copyright', 'en')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                                    @error('footer_copyright_en')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Copyright (BN)</label>
                                    <input name="footer_copyright_bn" value="{{ old('footer_copyright_bn', $footerFieldValue('footer_copyright', 'bn')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                                    @error('footer_copyright_bn')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                            >
                                Save Footer Settings
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        @else
            @php
                $homeHeroKeys = [
                    'hero_primary',
                    'hero_emphasis',
                    'hero_paragraph',
                    'hero_cta_primary',
                    'hero_side_heading',

                    'home_about_title',
                    'home_about_subtitle',
                    'home_about_card_1',
                    'home_about_card_2',
                    'home_about_card_3',
                        'home_skill_tracks_title',
                        'home_skill_tracks_subtitle',
                        'home_skill_tracks_cta',
                ];

                $sectionsByKey = $sections->keyBy('section_key');

                $reasonSections = $sections
                    ->filter(fn ($section) => str_starts_with($section->section_key, 'hero_different_reason_'))
                    ->sortBy(function ($section) {
                        if (preg_match('/^hero_different_reason_(\d+)$/', $section->section_key, $m)) {
                            return (int) $m[1];
                        }

                        return 9999;
                    })
                    ->values();

                $otherSections = $sections->reject(function ($section) use ($homeHeroKeys) {
                    return in_array($section->section_key, $homeHeroKeys, true)
                        || str_starts_with($section->section_key, 'hero_different_reason_')
                        || str_starts_with($section->section_key, 'home_skill_track_');
                });

                    $skillTrackSections = $sections
                        ->filter(fn ($section) => str_starts_with($section->section_key, 'home_skill_track_'))
                        ->sortBy(function ($section) {
                            if (preg_match('/^home_skill_track_(\d+)$/', $section->section_key, $m)) {
                                return (int) $m[1];
                            }

                            return 9999;
                        })
                        ->values();

                    $existingSkillTrackIndexes = $skillTrackSections->toBase()
                        ->map(function ($section) {
                            if (preg_match('/^home_skill_track_(\d+)$/', $section->section_key, $m)) {
                                return (int) $m[1];
                            }

                            return null;
                        })
                        ->filter()
                        ->unique()
                        ->sort()
                        ->values();

                    $skillTrackIndexes = $existingSkillTrackIndexes;
                    for ($i = 1; $i <= 5; $i++) {
                        if (! $skillTrackIndexes->contains($i)) {
                            $skillTrackIndexes->push($i);
                        }
                    }
                    $skillTrackIndexes = $skillTrackIndexes->sort()->values();
                    $nextSkillTrackIndex = (int) ($skillTrackIndexes->max() ?? 0) + 1;

                $existingReasonIndexes = $reasonSections->toBase()
                    ->map(function ($section) {
                        if (preg_match('/^hero_different_reason_(\d+)$/', $section->section_key, $m)) {
                            return (int) $m[1];
                        }

                        return null;
                    })
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values();

                $reasonIndexes = $existingReasonIndexes;
                for ($i = 1; $i <= 4; $i++) {
                    if (! $reasonIndexes->contains($i)) {
                        $reasonIndexes->push($i);
                    }
                }
                $reasonIndexes = $reasonIndexes->sort()->values();
                $nextReasonIndex = (int) ($reasonIndexes->max() ?? 0) + 1;
            @endphp

            @if($selectedPage->slug === 'home')
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-slate-900">Home Hero</h3>
                        <p class="mt-1 text-sm text-slate-500">Edit all hero fields in one save.</p>
                    </div>

                    <form method="POST"
                          action="{{ route('admin.frontend-editor.sections.bulk-update', $selectedPage) }}"
                          class="space-y-6">
                        @csrf
                        @method('PATCH')

                        <style>
                            /* Fallback: ensure Skill Track Delete button is visible even if Tailwind CSS wasn't rebuilt. */
                            .delete-skill-track {
                                background-color: #e11d48 !important; /* rose-600 */
                                border: 1px solid #e11d48 !important;
                                color: #ffffff !important;
                            }

                            .delete-skill-track:hover {
                                background-color: #be123c !important; /* rose-700 */
                                border-color: #be123c !important;
                            }

                            .delete-skill-track:disabled {
                                opacity: 0.7 !important;
                                cursor: not-allowed !important;
                            }
                        </style>

                        {{-- hero_primary --}}
                        @php $heroPrimary = $sectionsByKey->get('hero_primary'); @endphp
                        <div class="rounded-xl border border-slate-200 p-5">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm text-slate-500">Section Key</div>
                                    <div class="text-base font-semibold text-slate-900">hero_primary</div>
                                </div>
                                <select name="sections[hero_primary][status]"
                                        class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="active" @selected(old('sections.hero_primary.status', optional($heroPrimary)->status ?? 'active') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('sections.hero_primary.status', optional($heroPrimary)->status) === 'inactive')>Inactive</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Title (BN)</label>
                                    <input name="sections[hero_primary][title_bn]" value="{{ old('sections.hero_primary.title_bn', optional($heroPrimary)->title_bn) }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Title (EN)</label>
                                    <input name="sections[hero_primary][title_en]" value="{{ old('sections.hero_primary.title_en', optional($heroPrimary)->title_en) }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                            </div>
                        </div>

                        {{-- hero_emphasis --}}
                        @php $heroEmphasis = $sectionsByKey->get('hero_emphasis'); @endphp
                        <div class="rounded-xl border border-slate-200 p-5">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm text-slate-500">Section Key</div>
                                    <div class="text-base font-semibold text-slate-900">hero_emphasis</div>
                                </div>
                                <select name="sections[hero_emphasis][status]"
                                        class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="active" @selected(old('sections.hero_emphasis.status', optional($heroEmphasis)->status ?? 'active') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('sections.hero_emphasis.status', optional($heroEmphasis)->status) === 'inactive')>Inactive</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Title (BN)</label>
                                    <input name="sections[hero_emphasis][title_bn]" value="{{ old('sections.hero_emphasis.title_bn', optional($heroEmphasis)->title_bn) }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Title (EN)</label>
                                    <input name="sections[hero_emphasis][title_en]" value="{{ old('sections.hero_emphasis.title_en', optional($heroEmphasis)->title_en) }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                            </div>
                        </div>

                        {{-- hero_paragraph --}}
                        @php $heroParagraph = $sectionsByKey->get('hero_paragraph'); @endphp
                        <div class="rounded-xl border border-slate-200 p-5">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm text-slate-500">Section Key</div>
                                    <div class="text-base font-semibold text-slate-900">hero_paragraph</div>
                                </div>
                                <select name="sections[hero_paragraph][status]"
                                        class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="active" @selected(old('sections.hero_paragraph.status', optional($heroParagraph)->status ?? 'active') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('sections.hero_paragraph.status', optional($heroParagraph)->status) === 'inactive')>Inactive</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Content (BN)</label>
                                    <textarea name="sections[hero_paragraph][content_bn]" rows="5"
                                              class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.hero_paragraph.content_bn', optional($heroParagraph)->content_bn) }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Content (EN)</label>
                                    <textarea name="sections[hero_paragraph][content_en]" rows="5"
                                              class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.hero_paragraph.content_en', optional($heroParagraph)->content_en) }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- hero_cta_primary --}}
                        @php $heroCtaPrimary = $sectionsByKey->get('hero_cta_primary'); @endphp
                        <div class="rounded-xl border border-slate-200 p-5">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm text-slate-500">Section Key</div>
                                    <div class="text-base font-semibold text-slate-900">hero_cta_primary</div>
                                </div>
                                <select name="sections[hero_cta_primary][status]"
                                        class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="active" @selected(old('sections.hero_cta_primary.status', optional($heroCtaPrimary)->status ?? 'active') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('sections.hero_cta_primary.status', optional($heroCtaPrimary)->status) === 'inactive')>Inactive</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Button Text (BN)</label>
                                    <input name="sections[hero_cta_primary][button_text_bn]" value="{{ old('sections.hero_cta_primary.button_text_bn', optional($heroCtaPrimary)->button_text_bn) }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Button Text (EN)</label>
                                    <input name="sections[hero_cta_primary][button_text_en]" value="{{ old('sections.hero_cta_primary.button_text_en', optional($heroCtaPrimary)->button_text_en) }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Button Link</label>
                                    <input name="sections[hero_cta_primary][button_link]" value="{{ old('sections.hero_cta_primary.button_link', optional($heroCtaPrimary)->button_link) }}"
                                           placeholder="e.g. /courses"
                                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                            </div>
                        </div>

                        {{-- hero_side_heading --}}
                        @php $heroSideHeading = $sectionsByKey->get('hero_side_heading'); @endphp
                        <div class="rounded-xl border border-slate-200 p-5">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm text-slate-500">Section Key</div>
                                    <div class="text-base font-semibold text-slate-900">hero_side_heading</div>
                                </div>
                                <select name="sections[hero_side_heading][status]"
                                        class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="active" @selected(old('sections.hero_side_heading.status', optional($heroSideHeading)->status ?? 'active') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('sections.hero_side_heading.status', optional($heroSideHeading)->status) === 'inactive')>Inactive</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Title (BN)</label>
                                    <input name="sections[hero_side_heading][title_bn]" value="{{ old('sections.hero_side_heading.title_bn', optional($heroSideHeading)->title_bn) }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Title (EN)</label>
                                    <input name="sections[hero_side_heading][title_en]" value="{{ old('sections.hero_side_heading.title_en', optional($heroSideHeading)->title_en) }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                            </div>

                            <div class="mt-3 text-xs text-slate-500">
                                Used on the home hero side panel heading (frontend falls back to translations if empty).
                            </div>
                        </div>

                        {{-- hero_different_reason_* (dynamic) --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">Hero Different Reasons</div>
                                <div class="mt-1 text-xs text-slate-500">Use content lines: first line = subtitle, rest = description.</div>
                            </div>
                            <button type="button"
                                    id="addHeroReason"
                                    data-next-index="{{ $nextReasonIndex }}"
                                    class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                                Add Reason
                            </button>
                        </div>

                        <div id="heroReasonsContainer" class="space-y-6">
                            @foreach($reasonIndexes as $i)
                                @php $reasonKey = 'hero_different_reason_' . $i; @endphp
                                @php $reasonSection = $sectionsByKey->get($reasonKey); @endphp

                                <div class="hero-reason-block rounded-xl border border-slate-200 p-5" data-index="{{ $i }}" data-existing="{{ $reasonSection ? 1 : 0 }}">
                                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <div class="text-sm text-slate-500">Section Key</div>
                                            <div class="text-base font-semibold text-slate-900">{{ $reasonKey }}</div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <select name="sections[{{ $reasonKey }}][status]"
                                                    class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="active" @selected(old('sections.' . $reasonKey . '.status', optional($reasonSection)->status ?? 'active') === 'active')>Active</option>
                                                <option value="inactive" @selected(old('sections.' . $reasonKey . '.status', optional($reasonSection)->status) === 'inactive')>Inactive</option>
                                            </select>
                                            <button type="button"
                                                    class="remove-hero-reason inline-flex items-center rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">
                                                Deactivate
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Title (BN)</label>
                                            <input name="sections[{{ $reasonKey }}][title_bn]" value="{{ old('sections.' . $reasonKey . '.title_bn', optional($reasonSection)->title_bn) }}"
                                                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Title (EN)</label>
                                            <input name="sections[{{ $reasonKey }}][title_en]" value="{{ old('sections.' . $reasonKey . '.title_en', optional($reasonSection)->title_en) }}"
                                                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Content (BN)</label>
                                            <textarea name="sections[{{ $reasonKey }}][content_bn]" rows="4"
                                                      class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.' . $reasonKey . '.content_bn', optional($reasonSection)->content_bn) }}</textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Content (EN)</label>
                                            <textarea name="sections[{{ $reasonKey }}][content_en]" rows="4"
                                                      class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.' . $reasonKey . '.content_en', optional($reasonSection)->content_en) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <template id="heroReasonTemplate">
                            <div class="hero-reason-block rounded-xl border border-slate-200 p-5" data-index="__INDEX__" data-existing="0">
                                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <div class="text-sm text-slate-500">Section Key</div>
                                        <div class="text-base font-semibold text-slate-900">hero_different_reason___INDEX__</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <select name="sections[hero_different_reason___INDEX__][status]"
                                                class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="active" selected>Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                        <button type="button"
                                                class="remove-hero-reason inline-flex items-center rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">
                                            Remove
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Title (BN)</label>
                                        <input name="sections[hero_different_reason___INDEX__][title_bn]"
                                               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Title (EN)</label>
                                        <input name="sections[hero_different_reason___INDEX__][title_en]"
                                               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Content (BN)</label>
                                        <textarea name="sections[hero_different_reason___INDEX__][content_bn]" rows="4"
                                                  class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Content (EN)</label>
                                        <textarea name="sections[hero_different_reason___INDEX__][content_en]" rows="4"
                                                  class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <script>
                            (function () {
                                var addBtn = document.getElementById('addHeroReason');
                                var container = document.getElementById('heroReasonsContainer');
                                var template = document.getElementById('heroReasonTemplate');

                                if (!addBtn || !container || !template) {
                                    return;
                                }

                                function bindRemove(root) {
                                    var buttons = root.querySelectorAll('.remove-hero-reason');
                                    buttons.forEach(function (btn) {
                                        btn.addEventListener('click', function () {
                                            var block = btn.closest('.hero-reason-block');
                                            if (!block) {
                                                return;
                                            }

                                            var isExisting = block.getAttribute('data-existing') === '1';

                                            if (!isExisting) {
                                                block.remove();
                                                return;
                                            }

                                            var statusSelect = block.querySelector('select[name$="[status]"]');
                                            if (statusSelect) {
                                                statusSelect.value = 'inactive';
                                            }

                                            block.classList.add('opacity-60');
                                            btn.textContent = 'Deactivated';
                                            btn.disabled = true;
                                            btn.classList.add('cursor-not-allowed');
                                        });
                                    });
                                }

                                bindRemove(container);

                                addBtn.addEventListener('click', function () {
                                    var nextIndex = parseInt(addBtn.getAttribute('data-next-index') || '1', 10);
                                    if (!Number.isFinite(nextIndex) || nextIndex < 1) {
                                        nextIndex = 1;
                                    }

                                    var html = template.innerHTML.split('__INDEX__').join(String(nextIndex));
                                    var wrapper = document.createElement('div');
                                    wrapper.innerHTML = html;
                                    var node = wrapper.firstElementChild;
                                    if (!node) {
                                        return;
                                    }

                                    container.appendChild(node);
                                    bindRemove(node);

                                    addBtn.setAttribute('data-next-index', String(nextIndex + 1));
                                });
                            })();
                        </script>

                        <div class="flex items-center justify-end">
                            <button type="submit"
                                    class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                Save Home Hero
                            </button>
                        </div>
                    </form>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-slate-900">Home About (Why Choose iTechBD)</h3>
                        <p class="mt-1 text-sm text-slate-500">This controls the “কেন iTechBD বেছে নেবেন” section on the home page.</p>
                    </div>

                    @php
                        $homeAboutTitleBnFallback = __('frontend.home_about_title', [], 'bn');
                        $homeAboutTitleEnFallback = __('frontend.home_about_title', [], 'en');

                        $homeAboutSubtitleBnFallback = __('frontend.home_about_subtitle', [], 'bn');
                        $homeAboutSubtitleEnFallback = __('frontend.home_about_subtitle', [], 'en');
                    @endphp

                    <form method="POST"
                          action="{{ route('admin.frontend-editor.sections.bulk-update', $selectedPage) }}"
                          class="space-y-6">
                        @csrf
                        @method('PATCH')

                        {{-- home_about_title --}}
                        @php $homeAboutTitle = $sectionsByKey->get('home_about_title'); @endphp
                        <div class="rounded-xl border border-slate-200 p-5">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm text-slate-500">Section Key</div>
                                    <div class="text-base font-semibold text-slate-900">home_about_title</div>
                                </div>
                                <select name="sections[home_about_title][status]"
                                        class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="active" @selected(old('sections.home_about_title.status', optional($homeAboutTitle)->status ?? 'active') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('sections.home_about_title.status', optional($homeAboutTitle)->status) === 'inactive')>Inactive</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Title (BN)</label>
                                    <input name="sections[home_about_title][title_bn]" value="{{ old('sections.home_about_title.title_bn', optional($homeAboutTitle)->title_bn ?: $homeAboutTitleBnFallback) }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Title (EN)</label>
                                    <input name="sections[home_about_title][title_en]" value="{{ old('sections.home_about_title.title_en', optional($homeAboutTitle)->title_en ?: $homeAboutTitleEnFallback) }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                            </div>
                        </div>

                        {{-- home_about_subtitle --}}
                        @php $homeAboutSubtitle = $sectionsByKey->get('home_about_subtitle'); @endphp
                        <div class="rounded-xl border border-slate-200 p-5">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm text-slate-500">Section Key</div>
                                    <div class="text-base font-semibold text-slate-900">home_about_subtitle</div>
                                </div>
                                <select name="sections[home_about_subtitle][status]"
                                        class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="active" @selected(old('sections.home_about_subtitle.status', optional($homeAboutSubtitle)->status ?? 'active') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('sections.home_about_subtitle.status', optional($homeAboutSubtitle)->status) === 'inactive')>Inactive</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Content (BN)</label>
                                    <textarea name="sections[home_about_subtitle][content_bn]" rows="4"
                                              class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.home_about_subtitle.content_bn', optional($homeAboutSubtitle)->content_bn ?: $homeAboutSubtitleBnFallback) }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Content (EN)</label>
                                    <textarea name="sections[home_about_subtitle][content_en]" rows="4"
                                              class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.home_about_subtitle.content_en', optional($homeAboutSubtitle)->content_en ?: $homeAboutSubtitleEnFallback) }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- home_about_card_1..3 --}}
                        @php $aboutCardKeys = ['home_about_card_1', 'home_about_card_2', 'home_about_card_3']; @endphp
                        @foreach($aboutCardKeys as $cardKey)
                            @php $cardSection = $sectionsByKey->get($cardKey); @endphp
                            @php
                                $cardIndex = null;
                                if (preg_match('/^home_about_card_(\d+)$/', $cardKey, $m)) {
                                    $cardIndex = (int) $m[1];
                                }
                                $cardTitleKey = $cardIndex ? ('frontend.home_about_card_' . $cardIndex . '_title') : null;
                                $cardDescKey = $cardIndex ? ('frontend.home_about_card_' . $cardIndex . '_desc') : null;

                                $cardTitleBnFallback = $cardTitleKey ? __($cardTitleKey, [], 'bn') : '';
                                $cardTitleEnFallback = $cardTitleKey ? __($cardTitleKey, [], 'en') : '';
                                $cardDescBnFallback = $cardDescKey ? __($cardDescKey, [], 'bn') : '';
                                $cardDescEnFallback = $cardDescKey ? __($cardDescKey, [], 'en') : '';
                            @endphp
                            <div class="rounded-xl border border-slate-200 p-5">
                                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <div class="text-sm text-slate-500">Section Key</div>
                                        <div class="text-base font-semibold text-slate-900">{{ $cardKey }}</div>
                                    </div>
                                    <select name="sections[{{ $cardKey }}][status]"
                                            class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="active" @selected(old('sections.' . $cardKey . '.status', optional($cardSection)->status ?? 'active') === 'active')>Active</option>
                                        <option value="inactive" @selected(old('sections.' . $cardKey . '.status', optional($cardSection)->status) === 'inactive')>Inactive</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Title (BN)</label>
                                        <input name="sections[{{ $cardKey }}][title_bn]" value="{{ old('sections.' . $cardKey . '.title_bn', optional($cardSection)->title_bn ?: $cardTitleBnFallback) }}"
                                               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Title (EN)</label>
                                        <input name="sections[{{ $cardKey }}][title_en]" value="{{ old('sections.' . $cardKey . '.title_en', optional($cardSection)->title_en ?: $cardTitleEnFallback) }}"
                                               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Content (BN)</label>
                                        <textarea name="sections[{{ $cardKey }}][content_bn]" rows="4"
                                                  class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.' . $cardKey . '.content_bn', optional($cardSection)->content_bn ?: $cardDescBnFallback) }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Content (EN)</label>
                                        <textarea name="sections[{{ $cardKey }}][content_en]" rows="4"
                                                  class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.' . $cardKey . '.content_en', optional($cardSection)->content_en ?: $cardDescEnFallback) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="flex items-center justify-end">
                            <button type="submit"
                                    class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                Save Home About
                            </button>
                        </div>
                    </form>
                </div>

                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-slate-900">Home Skill Tracks</h3>
                        <p class="mt-1 text-sm text-slate-500">Controls the “আমাদের স্কিল ট্র্যাকসমূহ” section on the home page.</p>
                    </div>

                    @php
                        $skillTracksTitleBnFallback = __('frontend.home_skill_tracks_title', [], 'bn');
                        $skillTracksTitleEnFallback = __('frontend.home_skill_tracks_title', [], 'en');

                        $skillTracksSubtitleBnFallback = __('frontend.home_skill_tracks_subtitle', [], 'bn');
                        $skillTracksSubtitleEnFallback = __('frontend.home_skill_tracks_subtitle', [], 'en');

                        $skillTracksDefaultItems = [
                            1 => [
                                'title' => 'Web Development',
                                'content' => "Front-end + back-end fundamentals with real projects.\nHTML, CSS, TailwindCSS, JavaScript\nResponsive UI + animations + components\nAPIs, database basics, deployment basics",
                            ],
                            2 => [
                                'title' => 'SEO (Search Engine Optimization)',
                                'content' => "Technical SEO + content + analytics.\nOn-page, off-page, technical SEO\nKeyword research + content planning\nAnalytics basics + reporting",
                            ],
                            3 => [
                                'title' => '.NET Development',
                                'content' => "C# + ASP.NET Core for modern applications.\nC# fundamentals + OOP\nASP.NET Core APIs + auth basics\nDatabase + EF Core basics",
                            ],
                            4 => [
                                'title' => 'Graphics Design',
                                'content' => "Branding + marketing visuals + portfolio.\nPhotoshop / Illustrator fundamentals\nBranding, typography, layouts\nPortfolio + client workflow",
                            ],
                            5 => [
                                'title' => 'Extra Topics (Optional)',
                                'content' => "UI/UX, Git, communication and teamwork.\nUI/UX basics (Figma)\nGit basics + teamwork\nClient communication",
                            ],
                        ];

                        $skillTracksDefaultIcons = [
                            1 => 'fa-solid fa-code',
                            2 => 'fa-solid fa-magnifying-glass',
                            3 => 'fa-brands fa-microsoft',
                            4 => 'fa-solid fa-palette',
                            5 => 'fa-solid fa-star',
                        ];

                        // WordPress-like picker: show ALL free Font Awesome icons (loaded via AJAX).
                    @endphp

                    <form method="POST"
                          action="{{ route('admin.frontend-editor.sections.bulk-update', $selectedPage) }}"
                          class="space-y-6">
                        @csrf
                        @method('PATCH')

                        {{-- home_skill_tracks_title --}}
                        @php $skillTracksTitle = $sectionsByKey->get('home_skill_tracks_title'); @endphp
                        <div class="rounded-xl border border-slate-200 p-5">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm text-slate-500">Section Key</div>
                                    <div class="text-base font-semibold text-slate-900">home_skill_tracks_title</div>
                                </div>
                                <select name="sections[home_skill_tracks_title][status]"
                                        class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="active" @selected(old('sections.home_skill_tracks_title.status', optional($skillTracksTitle)->status ?? 'active') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('sections.home_skill_tracks_title.status', optional($skillTracksTitle)->status) === 'inactive')>Inactive</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Title (BN)</label>
                                    <input name="sections[home_skill_tracks_title][title_bn]" value="{{ old('sections.home_skill_tracks_title.title_bn', optional($skillTracksTitle)->title_bn ?: $skillTracksTitleBnFallback) }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Title (EN)</label>
                                    <input name="sections[home_skill_tracks_title][title_en]" value="{{ old('sections.home_skill_tracks_title.title_en', optional($skillTracksTitle)->title_en ?: $skillTracksTitleEnFallback) }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                            </div>
                        </div>

                        {{-- home_skill_tracks_subtitle --}}
                        @php $skillTracksSubtitle = $sectionsByKey->get('home_skill_tracks_subtitle'); @endphp
                        <div class="rounded-xl border border-slate-200 p-5">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm text-slate-500">Section Key</div>
                                    <div class="text-base font-semibold text-slate-900">home_skill_tracks_subtitle</div>
                                </div>
                                <select name="sections[home_skill_tracks_subtitle][status]"
                                        class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="active" @selected(old('sections.home_skill_tracks_subtitle.status', optional($skillTracksSubtitle)->status ?? 'active') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('sections.home_skill_tracks_subtitle.status', optional($skillTracksSubtitle)->status) === 'inactive')>Inactive</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Content (BN)</label>
                                    <textarea name="sections[home_skill_tracks_subtitle][content_bn]" rows="3"
                                              class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.home_skill_tracks_subtitle.content_bn', optional($skillTracksSubtitle)->content_bn ?: $skillTracksSubtitleBnFallback) }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Content (EN)</label>
                                    <textarea name="sections[home_skill_tracks_subtitle][content_en]" rows="3"
                                              class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.home_skill_tracks_subtitle.content_en', optional($skillTracksSubtitle)->content_en ?: $skillTracksSubtitleEnFallback) }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- home_skill_tracks_cta --}}
                        @php $skillTracksCta = $sectionsByKey->get('home_skill_tracks_cta'); @endphp
                        <div class="rounded-xl border border-slate-200 p-5">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm text-slate-500">Section Key</div>
                                    <div class="text-base font-semibold text-slate-900">home_skill_tracks_cta</div>
                                </div>
                                <select name="sections[home_skill_tracks_cta][status]"
                                        class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="active" @selected(old('sections.home_skill_tracks_cta.status', optional($skillTracksCta)->status ?? 'active') === 'active')>Active</option>
                                    <option value="inactive" @selected(old('sections.home_skill_tracks_cta.status', optional($skillTracksCta)->status) === 'inactive')>Inactive</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Button Text (BN)</label>
                                    <input name="sections[home_skill_tracks_cta][button_text_bn]" value="{{ old('sections.home_skill_tracks_cta.button_text_bn', optional($skillTracksCta)->button_text_bn ?: 'How we help you get hired →') }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Button Text (EN)</label>
                                    <input name="sections[home_skill_tracks_cta][button_text_en]" value="{{ old('sections.home_skill_tracks_cta.button_text_en', optional($skillTracksCta)->button_text_en ?: 'How we help you get hired →') }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Button Link</label>
                                    <input name="sections[home_skill_tracks_cta][button_link]" value="{{ old('sections.home_skill_tracks_cta.button_link', optional($skillTracksCta)->button_link ?: '#outcomes') }}"
                                           class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                            </div>
                        </div>

                        {{-- home_skill_track_* (dynamic) --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">Skill Track Items</div>
                                <div class="mt-1 text-xs text-slate-500">Content format: first line = short description, next lines = bullet points.</div>
                            </div>
                            <button type="button"
                                    id="addSkillTrack"
                                    data-next-index="{{ $nextSkillTrackIndex }}"
                                    class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                                Add Track
                            </button>
                        </div>

                        <div id="skillTracksContainer" class="space-y-6">
                            @foreach($skillTrackIndexes as $i)
                                @php $trackKey = 'home_skill_track_' . $i; @endphp
                                @php $trackSection = $sectionsByKey->get($trackKey); @endphp
                                @php
                                    $defaults = $skillTracksDefaultItems[$i] ?? ['title' => '', 'content' => ''];
                                    $fallbackTitle = (string) ($defaults['title'] ?? '');
                                    $fallbackContent = (string) ($defaults['content'] ?? '');
                                    $fallbackIcon = (string) ($skillTracksDefaultIcons[$i] ?? 'fa-solid fa-star');
                                @endphp

                                <div class="skill-track-block rounded-xl border border-slate-200 p-5" data-index="{{ $i }}" data-existing="{{ $trackSection ? 1 : 0 }}">
                                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <div class="text-sm text-slate-500">Section Key</div>
                                            <div class="text-base font-semibold text-slate-900">{{ $trackKey }}</div>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <select name="sections[{{ $trackKey }}][status]"
                                                    class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="active" @selected(old('sections.' . $trackKey . '.status', optional($trackSection)->status ?? 'active') === 'active')>Active</option>
                                                <option value="inactive" @selected(old('sections.' . $trackKey . '.status', optional($trackSection)->status) === 'inactive')>Inactive</option>
                                            </select>
                                            <button type="button"
                                                    class="remove-skill-track inline-flex items-center rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">
                                                Deactivate
                                            </button>

                                            @if($trackSection)
                                                <button
                                                    type="button"
                                                    class="delete-skill-track inline-flex items-center rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-500"
                                                    data-delete-url="{{ route('admin.frontend-editor.sections.destroy', $trackSection) }}"
                                                >
                                                    Delete
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                        <div class="lg:col-span-2">
                                            <label class="block text-sm font-medium text-slate-700">Icon</label>

                                            @php
                                                $iconValue = (string) old('sections.' . $trackKey . '.icon', optional($trackSection)->icon ?: $fallbackIcon);

                                                $previewIconValue = trim($iconValue);
                                                $legacyToFa = [
                                                    'code' => 'fa-solid fa-code',
                                                    'search' => 'fa-solid fa-magnifying-glass',
                                                    'dotnet' => 'fa-brands fa-microsoft',
                                                    'design' => 'fa-solid fa-palette',
                                                    'sparkles' => 'fa-solid fa-star',
                                                    'rocket' => 'fa-solid fa-rocket',
                                                    'chart' => 'fa-solid fa-chart-line',
                                                    'shield' => 'fa-solid fa-shield-halved',
                                                ];

                                                if (array_key_exists($previewIconValue, $legacyToFa)) {
                                                    $previewIconValue = $legacyToFa[$previewIconValue];
                                                }

                                                if ($previewIconValue === '' || !preg_match('/^fa-(solid|regular|brands)\s+fa-[a-z0-9-]+$/', $previewIconValue)) {
                                                    $previewIconValue = 'fa-solid fa-star';
                                                }
                                            @endphp

                                            <div class="mt-1 flex flex-wrap items-center gap-3">
                                                <div class="grid h-10 w-10 place-items-center rounded-xl bg-slate-50 text-slate-700 ring-1 ring-slate-200" aria-hidden="true">
                                                    <i class="{{ $previewIconValue }}"></i>
                                                </div>

                                                <input
                                                    name="sections[{{ $trackKey }}][icon]"
                                                    value="{{ $iconValue }}"
                                                    placeholder="fa-solid fa-code"
                                                    class="frontend-icon-input block w-full flex-1 min-w-[240px] rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                />

                                                <button
                                                    type="button"
                                                    class="open-icon-picker inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800"
                                                >
                                                    Choose Icon
                                                </button>

                                                <button
                                                    type="button"
                                                    class="clear-icon-picker inline-flex items-center rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200"
                                                >
                                                    Clear
                                                </button>
                                            </div>

                                            <div class="mt-1 text-xs text-slate-500">
                                                Tip: use the picker, or type a Font Awesome class like <span class="font-semibold">fa-solid fa-code</span>.
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Title (BN)</label>
                                            <input name="sections[{{ $trackKey }}][title_bn]" value="{{ old('sections.' . $trackKey . '.title_bn', optional($trackSection)->title_bn ?: $fallbackTitle) }}"
                                                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Title (EN)</label>
                                            <input name="sections[{{ $trackKey }}][title_en]" value="{{ old('sections.' . $trackKey . '.title_en', optional($trackSection)->title_en ?: $fallbackTitle) }}"
                                                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Content (BN)</label>
                                            <textarea name="sections[{{ $trackKey }}][content_bn]" rows="5"
                                                      class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.' . $trackKey . '.content_bn', optional($trackSection)->content_bn ?: $fallbackContent) }}</textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Content (EN)</label>
                                            <textarea name="sections[{{ $trackKey }}][content_en]" rows="5"
                                                      class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.' . $trackKey . '.content_en', optional($trackSection)->content_en ?: $fallbackContent) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <template id="skillTrackTemplate">
                            <div class="skill-track-block rounded-xl border border-slate-200 p-5" data-index="__INDEX__" data-existing="0">
                                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <div class="text-sm text-slate-500">Section Key</div>
                                        <div class="text-base font-semibold text-slate-900">home_skill_track___INDEX__</div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <select name="sections[home_skill_track___INDEX__][status]"
                                                class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="active" selected>Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                        <button type="button"
                                                class="remove-skill-track inline-flex items-center rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">
                                            Remove
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                    <div class="lg:col-span-2">
                                        <label class="block text-sm font-medium text-slate-700">Icon</label>

                                        <div class="mt-1 flex flex-wrap items-center gap-3">
                                            <div class="grid h-10 w-10 place-items-center rounded-xl bg-slate-50 text-slate-700 ring-1 ring-slate-200" aria-hidden="true">
                                                <i class="fa-solid fa-star"></i>
                                            </div>

                                            <input
                                                name="sections[home_skill_track___INDEX__][icon]"
                                                value="fa-solid fa-star"
                                                placeholder="fa-solid fa-code"
                                                class="frontend-icon-input block w-full flex-1 min-w-[240px] rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />

                                            <button
                                                type="button"
                                                class="open-icon-picker inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800"
                                            >
                                                Choose Icon
                                            </button>

                                            <button
                                                type="button"
                                                class="clear-icon-picker inline-flex items-center rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200"
                                            >
                                                Clear
                                            </button>
                                        </div>

                                        <div class="mt-1 text-xs text-slate-500">
                                            Tip: use the picker, or type a Font Awesome class like <span class="font-semibold">fa-solid fa-code</span>.
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Title (BN)</label>
                                        <input name="sections[home_skill_track___INDEX__][title_bn]"
                                               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Title (EN)</label>
                                        <input name="sections[home_skill_track___INDEX__][title_en]"
                                               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Content (BN)</label>
                                        <textarea name="sections[home_skill_track___INDEX__][content_bn]" rows="5"
                                                  class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Content (EN)</label>
                                        <textarea name="sections[home_skill_track___INDEX__][content_en]" rows="5"
                                                  class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Delete Skill Track Modal --}}
                        <div id="skillTrackDeleteModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
                            <style>
                                /* Fallback styles for the delete modal (works even if Tailwind isn't rebuilt). */
                                #skillTrackDeleteModal { display: none; }
                                #skillTrackDeleteModal.is-open { display: block; }
                                #skillTrackDeleteModal .stm-backdrop {
                                    position: absolute; inset: 0;
                                    background: rgba(15, 23, 42, 0.45);
                                }
                                #skillTrackDeleteModal .stm-panel {
                                    position: relative;
                                    margin: 10vh auto 0;
                                    width: min(520px, 92vw);
                                    background: #fff;
                                    border: 1px solid #e2e8f0;
                                    border-radius: 14px;
                                    box-shadow: 0 20px 60px rgba(2, 6, 23, 0.22);
                                    overflow: hidden;
                                }
                                #skillTrackDeleteModal .stm-head {
                                    padding: 16px 18px;
                                    border-bottom: 1px solid #e2e8f0;
                                    display: flex;
                                    align-items: center;
                                    justify-content: space-between;
                                    gap: 12px;
                                }
                                #skillTrackDeleteModal .stm-title {
                                    font-size: 16px;
                                    font-weight: 700;
                                    color: #0f172a;
                                }
                                #skillTrackDeleteModal .stm-close {
                                    border: 1px solid #e2e8f0;
                                    background: #fff;
                                    border-radius: 10px;
                                    padding: 6px 10px;
                                    font-size: 14px;
                                    cursor: pointer;
                                }
                                #skillTrackDeleteModal .stm-body { padding: 16px 18px; }
                                #skillTrackDeleteModal .stm-desc { color: #334155; font-size: 14px; line-height: 1.45; }
                                #skillTrackDeleteModal .stm-error {
                                    display: none;
                                    margin-top: 12px;
                                    padding: 10px 12px;
                                    border: 1px solid #fecaca;
                                    background: #fef2f2;
                                    color: #991b1b;
                                    border-radius: 10px;
                                    font-size: 13px;
                                }
                                #skillTrackDeleteModal .stm-error.is-visible { display: block; }
                                #skillTrackDeleteModal .stm-foot {
                                    padding: 14px 18px;
                                    border-top: 1px solid #e2e8f0;
                                    display: flex;
                                    justify-content: flex-end;
                                    gap: 10px;
                                    flex-wrap: wrap;
                                }
                                #skillTrackDeleteModal .stm-btn {
                                    border-radius: 10px;
                                    padding: 10px 14px;
                                    font-size: 13px;
                                    font-weight: 700;
                                    cursor: pointer;
                                    border: 1px solid transparent;
                                }
                                #skillTrackDeleteModal .stm-btn-cancel {
                                    background: #fff;
                                    border-color: #e2e8f0;
                                    color: #0f172a;
                                }
                                #skillTrackDeleteModal .stm-btn-danger {
                                    background: #e11d48;
                                    border-color: #e11d48;
                                    color: #fff;
                                }
                                #skillTrackDeleteModal .stm-btn:disabled { opacity: 0.75; cursor: not-allowed; }
                            </style>

                            <div class="stm-backdrop" data-skill-track-delete-close="1"></div>

                            <div class="stm-panel" role="dialog" aria-modal="true" aria-labelledby="skillTrackDeleteTitle">
                                <div class="stm-head">
                                    <div id="skillTrackDeleteTitle" class="stm-title">Delete skill track</div>
                                    <button type="button" class="stm-close" data-skill-track-delete-close="1">Close</button>
                                </div>

                                <div class="stm-body">
                                    <p class="stm-desc" id="skillTrackDeleteText">
                                        Are you sure you want to delete this skill track? This cannot be undone.
                                    </p>
                                    <div class="stm-error" id="skillTrackDeleteError">Could not delete this track. Please try again.</div>
                                </div>

                                <div class="stm-foot">
                                    <button type="button" class="stm-btn stm-btn-cancel" id="skillTrackDeleteCancel">Cancel</button>
                                    <button type="button" class="stm-btn stm-btn-danger" id="skillTrackDeleteConfirm">Delete</button>
                                </div>
                            </div>
                        </div>

                        {{-- Add Skill Track Modal --}}
                        <div id="skillTrackAddModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
                            <style>
                                /* Fallback modal styles (works even if Tailwind isn't rebuilt). */
                                #skillTrackAddModal .fe-modal-backdrop {
                                    position: absolute;
                                    inset: 0;
                                    background: rgba(15, 23, 42, 0.45);
                                    -webkit-backdrop-filter: blur(2px);
                                    backdrop-filter: blur(2px);
                                }

                                #skillTrackAddModal .fe-modal-panel {
                                    max-height: calc(100vh - 80px);
                                    overflow: hidden;
                                }

                                #skillTrackAddModal .fe-modal-body {
                                    max-height: calc(100vh - 190px);
                                    overflow: auto;
                                }
                            </style>

                            <div class="fe-modal-backdrop absolute inset-0 bg-slate-900/40"></div>
                            <div class="absolute inset-x-0 top-10 mx-auto w-full max-w-3xl px-4">
                                <div class="fe-modal-panel overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-200">
                                    <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-5 py-4">
                                        <div>
                                            <div class="text-base font-semibold text-slate-900">Add Skill Track</div>
                                            <div class="mt-0.5 text-xs text-slate-500">This will create a new section key: <span id="skillTrackAddKey" class="font-semibold">home_skill_track_</span></div>
                                        </div>
                                        <button type="button" id="skillTrackAddClose" class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">Close</button>
                                    </div>

                                    <div class="fe-modal-body px-5 py-4">
                                        <div class="skill-track-block rounded-xl border border-slate-200 p-5" data-existing="0">
                                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                                <div>
                                                    <div class="text-sm text-slate-500">Status</div>
                                                </div>
                                                <select id="skillTrackAddStatus" class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    <option value="active" selected>Active</option>
                                                    <option value="inactive">Inactive</option>
                                                </select>
                                            </div>

                                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                                <div class="lg:col-span-2">
                                                    <label class="block text-sm font-medium text-slate-700">Icon</label>
                                                    <div class="mt-1 flex flex-wrap items-center gap-3">
                                                        <div class="grid h-10 w-10 place-items-center rounded-xl bg-slate-50 text-slate-700 ring-1 ring-slate-200" aria-hidden="true">
                                                            <i id="skillTrackAddIconPreview" class="fa-solid fa-star"></i>
                                                        </div>

                                                        <input id="skillTrackAddIcon" value="fa-solid fa-star" placeholder="fa-solid fa-code"
                                                               class="frontend-icon-input block w-full flex-1 min-w-[240px] rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />

                                                        <button type="button" class="open-icon-picker inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">Choose Icon</button>
                                                        <button type="button" class="clear-icon-picker inline-flex items-center rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">Clear</button>
                                                    </div>
                                                    <div class="mt-1 text-xs text-slate-500">Tip: use the picker, or type a Font Awesome class like <span class="font-semibold">fa-solid fa-code</span>.</div>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700">Title (BN)</label>
                                                    <input id="skillTrackAddTitleBn" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700">Title (EN)</label>
                                                    <input id="skillTrackAddTitleEn" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700">Content (BN)</label>
                                                    <textarea id="skillTrackAddContentBn" rows="5" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700">Content (EN)</label>
                                                    <textarea id="skillTrackAddContentEn" rows="5" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex items-center justify-end gap-2">
                                            <button type="button" id="skillTrackAddCancel" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">Cancel</button>
                                            <button type="button" id="skillTrackAddApply" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Add</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            (function () {
                                var addBtn = document.getElementById('addSkillTrack');
                                var container = document.getElementById('skillTracksContainer');
                                var template = document.getElementById('skillTrackTemplate');

                                var deleteModal = document.getElementById('skillTrackDeleteModal');
                                var deleteConfirmBtn = document.getElementById('skillTrackDeleteConfirm');
                                var deleteCancelBtn = document.getElementById('skillTrackDeleteCancel');
                                var deleteErrorEl = document.getElementById('skillTrackDeleteError');
                                var deleteTextEl = document.getElementById('skillTrackDeleteText');

                                var deletePending = {
                                    url: '',
                                    button: null,
                                    block: null,
                                };

                                var modal = document.getElementById('skillTrackAddModal');
                                var closeBtn = document.getElementById('skillTrackAddClose');
                                var cancelBtn = document.getElementById('skillTrackAddCancel');
                                var applyBtn = document.getElementById('skillTrackAddApply');
                                var keyLabel = document.getElementById('skillTrackAddKey');

                                var statusEl = document.getElementById('skillTrackAddStatus');
                                var iconEl = document.getElementById('skillTrackAddIcon');
                                var titleBnEl = document.getElementById('skillTrackAddTitleBn');
                                var titleEnEl = document.getElementById('skillTrackAddTitleEn');
                                var contentBnEl = document.getElementById('skillTrackAddContentBn');
                                var contentEnEl = document.getElementById('skillTrackAddContentEn');

                                if (!addBtn || !container || !template || !modal || !closeBtn || !cancelBtn || !applyBtn || !keyLabel) {
                                    return;
                                }

                                function openDeleteModal(url, btn) {
                                    if (!deleteModal || !deleteConfirmBtn || !deleteCancelBtn) {
                                        return;
                                    }

                                    deletePending.url = url || '';
                                    deletePending.button = btn || null;
                                    deletePending.block = btn ? btn.closest('.skill-track-block') : null;

                                    if (deleteErrorEl) {
                                        deleteErrorEl.classList.remove('is-visible');
                                    }

                                    // Add a nicer message including the section key.
                                    if (deleteTextEl) {
                                        var key = '';
                                        if (deletePending.block) {
                                            var keyNode = deletePending.block.querySelector('.text-base.font-semibold');
                                            if (keyNode) {
                                                key = String(keyNode.textContent || '').trim();
                                            }
                                        }

                                        deleteTextEl.textContent = key
                                            ? ('Delete "' + key + '"? This cannot be undone.')
                                            : 'Are you sure you want to delete this skill track? This cannot be undone.';
                                    }

                                    deleteModal.classList.remove('hidden');
                                    deleteModal.classList.add('is-open');
                                    deleteModal.setAttribute('aria-hidden', 'false');
                                }

                                function closeDeleteModal() {
                                    if (!deleteModal || !deleteConfirmBtn) {
                                        return;
                                    }

                                    deleteModal.classList.add('hidden');
                                    deleteModal.classList.remove('is-open');
                                    deleteModal.setAttribute('aria-hidden', 'true');

                                    // Reset state
                                    deleteConfirmBtn.disabled = false;
                                    deleteConfirmBtn.textContent = 'Delete';
                                    deletePending.url = '';
                                    deletePending.button = null;
                                    deletePending.block = null;

                                    if (deleteErrorEl) {
                                        deleteErrorEl.classList.remove('is-visible');
                                        deleteErrorEl.textContent = 'Could not delete this track. Please try again.';
                                    }
                                }

                                function showDeleteError(message) {
                                    if (!deleteErrorEl) {
                                        return;
                                    }

                                    deleteErrorEl.textContent = message || 'Could not delete this track. Please try again.';
                                    deleteErrorEl.classList.add('is-visible');
                                }

                                function bindRemove(root) {
                                    var buttons = root.querySelectorAll('.remove-skill-track');
                                    buttons.forEach(function (btn) {
                                        btn.addEventListener('click', function () {
                                            var block = btn.closest('.skill-track-block');
                                            if (!block) {
                                                return;
                                            }

                                            var isExisting = block.getAttribute('data-existing') === '1';

                                            if (!isExisting) {
                                                block.remove();
                                                return;
                                            }

                                            var statusSelect = block.querySelector('select[name$="[status]"]');
                                            if (statusSelect) {
                                                statusSelect.value = 'inactive';
                                            }

                                            block.classList.add('opacity-60');
                                            btn.textContent = 'Deactivated';
                                            btn.disabled = true;
                                            btn.classList.add('cursor-not-allowed');
                                        });
                                    });
                                }

                                function bindDelete(root) {
                                    var buttons = root.querySelectorAll('.delete-skill-track');
                                    buttons.forEach(function (btn) {
                                        btn.addEventListener('click', function () {
                                            var url = btn.getAttribute('data-delete-url') || '';
                                            if (!url) {
                                                return;
                                            }

                                            openDeleteModal(url, btn);
                                        });
                                    });
                                }

                                bindRemove(container);
                                bindDelete(container);

                                // Delete modal events
                                if (deleteModal && deleteConfirmBtn && deleteCancelBtn) {
                                    deleteModal.addEventListener('click', function (e) {
                                        var target = e.target;
                                        if (target && target.getAttribute && target.getAttribute('data-skill-track-delete-close') === '1') {
                                            closeDeleteModal();
                                        }
                                    });

                                    deleteCancelBtn.addEventListener('click', function () {
                                        closeDeleteModal();
                                    });

                                    document.addEventListener('keydown', function (e) {
                                        if (e.key === 'Escape' && !deleteModal.classList.contains('hidden')) {
                                            closeDeleteModal();
                                        }
                                    });

                                    deleteConfirmBtn.addEventListener('click', function () {
                                        var url = deletePending.url || '';
                                        if (!url) {
                                            closeDeleteModal();
                                            return;
                                        }

                                        var csrf = '';
                                        var meta = document.querySelector('meta[name="csrf-token"]');
                                        if (meta) {
                                            csrf = meta.getAttribute('content') || '';
                                        }

                                        deleteConfirmBtn.disabled = true;
                                        deleteConfirmBtn.textContent = 'Deleting...';

                                        fetch(url, {
                                            method: 'DELETE',
                                            headers: {
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': csrf,
                                                'X-Requested-With': 'XMLHttpRequest'
                                            }
                                        })
                                            .then(function (res) {
                                                if (!res.ok) {
                                                    return res.json()
                                                        .catch(function () { return {}; })
                                                        .then(function (payload) {
                                                            var msg = (payload && (payload.message || payload.error)) ? String(payload.message || payload.error) : 'Delete failed.';
                                                            throw new Error(msg);
                                                        });
                                                }

                                                return res.json().catch(function () { return {}; });
                                            })
                                            .then(function () {
                                                if (deletePending.block) {
                                                    deletePending.block.remove();
                                                }
                                                closeDeleteModal();
                                            })
                                            .catch(function (err) {
                                                showDeleteError(err && err.message ? String(err.message) : 'Could not delete this track. Please try again.');
                                                deleteConfirmBtn.disabled = false;
                                                deleteConfirmBtn.textContent = 'Delete';
                                            });
                                    });
                                }

                                function openModal(nextIndex) {
                                    modal.setAttribute('data-next-index', String(nextIndex));
                                    keyLabel.textContent = 'home_skill_track_' + String(nextIndex);

                                    if (statusEl) statusEl.value = 'active';
                                    if (iconEl) iconEl.value = 'fa-solid fa-star';
                                    if (titleBnEl) titleBnEl.value = '';
                                    if (titleEnEl) titleEnEl.value = '';
                                    if (contentBnEl) contentBnEl.value = '';
                                    if (contentEnEl) contentEnEl.value = '';

                                    // Update the preview icon inside the modal block (first <i> in that block).
                                    var previewEl = modal.querySelector('.skill-track-block i');
                                    if (previewEl) {
                                        previewEl.className = 'fa-solid fa-star';
                                    }

                                    modal.classList.remove('hidden');
                                    modal.setAttribute('aria-hidden', 'false');
                                }

                                function closeModal() {
                                    modal.classList.add('hidden');
                                    modal.setAttribute('aria-hidden', 'true');
                                    modal.removeAttribute('data-next-index');
                                }

                                function setValueIfFound(root, selector, value) {
                                    var el = root.querySelector(selector);
                                    if (!el) return;
                                    if (el.tagName === 'SELECT') {
                                        el.value = value;
                                    } else {
                                        el.value = value;
                                    }
                                }

                                function setTextareaIfFound(root, selector, value) {
                                    var el = root.querySelector(selector);
                                    if (!el) return;
                                    el.value = value;
                                }

                                function setIconInBlock(root, iconClass) {
                                    var input = root.querySelector('.frontend-icon-input');
                                    if (input) {
                                        input.value = iconClass;
                                    }
                                    var preview = root.querySelector('i');
                                    if (preview) {
                                        preview.className = iconClass;
                                    }
                                }

                                addBtn.addEventListener('click', function () {
                                    var nextIndex = parseInt(addBtn.getAttribute('data-next-index') || '1', 10);
                                    if (!Number.isFinite(nextIndex) || nextIndex < 1) {
                                        nextIndex = 1;
                                    }

                                    openModal(nextIndex);
                                });

                                closeBtn.addEventListener('click', closeModal);
                                cancelBtn.addEventListener('click', closeModal);
                                // Intentionally do NOT close on backdrop click.
                                // Users often click outside by accident while filling the form.
                                document.addEventListener('keydown', function (e) {
                                    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                                        closeModal();
                                    }
                                });

                                // Live preview when typing icon class in the modal.
                                if (iconEl) {
                                    iconEl.addEventListener('input', function () {
                                        var val = String(iconEl.value || '').trim();
                                        var previewEl = document.getElementById('skillTrackAddIconPreview');
                                        if (!previewEl) {
                                            return;
                                        }
                                        var ok = /^fa-(solid|regular|brands)\s+fa-[a-z0-9-]+$/.test(val);
                                        previewEl.className = ok ? val : 'fa-solid fa-star';
                                    });
                                }

                                applyBtn.addEventListener('click', function () {
                                    var nextIndex = parseInt(modal.getAttribute('data-next-index') || '0', 10);
                                    if (!Number.isFinite(nextIndex) || nextIndex < 1) {
                                        closeModal();
                                        return;
                                    }

                                    var iconVal = (iconEl && iconEl.value ? String(iconEl.value).trim() : '') || 'fa-solid fa-star';
                                    var statusVal = (statusEl && statusEl.value ? String(statusEl.value) : 'active') || 'active';
                                    var titleBn = titleBnEl ? String(titleBnEl.value || '') : '';
                                    var titleEn = titleEnEl ? String(titleEnEl.value || '') : '';
                                    var contentBn = contentBnEl ? String(contentBnEl.value || '') : '';
                                    var contentEn = contentEnEl ? String(contentEnEl.value || '') : '';

                                    var html = template.innerHTML.split('__INDEX__').join(String(nextIndex));
                                    var wrapper = document.createElement('div');
                                    wrapper.innerHTML = html;
                                    var node = wrapper.firstElementChild;
                                    if (!node) {
                                        closeModal();
                                        return;
                                    }

                                    // Fill values into the new block.
                                    setValueIfFound(node, 'select[name="sections[home_skill_track_' + nextIndex + '][status]"]', statusVal);
                                    setValueIfFound(node, 'input[name="sections[home_skill_track_' + nextIndex + '][title_bn]"]', titleBn);
                                    setValueIfFound(node, 'input[name="sections[home_skill_track_' + nextIndex + '][title_en]"]', titleEn);
                                    setTextareaIfFound(node, 'textarea[name="sections[home_skill_track_' + nextIndex + '][content_bn]"]', contentBn);
                                    setTextareaIfFound(node, 'textarea[name="sections[home_skill_track_' + nextIndex + '][content_en]"]', contentEn);
                                    setIconInBlock(node, iconVal);

                                    container.appendChild(node);
                                    bindRemove(node);
                                    bindDelete(node);

                                    addBtn.setAttribute('data-next-index', String(nextIndex + 1));
                                    closeModal();
                                });
                            })();
                        </script>

                        {{-- Icon Picker Modal --}}
                        <div id="faIconPickerModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
                            <style>
                                /* Fallback styles so the icon grid works even if Tailwind isn't rebuilt yet. */
                                #faIconPickerModal .fa-modal-panel {
                                    max-height: calc(100vh - 80px);
                                    overflow: hidden;
                                }

                                #faIconPickerModal .fa-modal-body {
                                    max-height: calc(100vh - 190px);
                                    overflow: auto;
                                }

                                #faIconPickerModal #faIconPickerGridWrap {
                                    max-height: 340px;
                                    overflow-y: auto;
                                    overflow-x: hidden;
                                    overscroll-behavior: contain;
                                }

                                @media (max-height: 740px) {
                                    #faIconPickerModal #faIconPickerGridWrap {
                                        max-height: 52vh;
                                    }
                                }

                                #faIconPickerModal #faIconPickerGrid {
                                    display: grid;
                                    grid-template-columns: repeat(4, minmax(0, 1fr));
                                    gap: 0.5rem;
                                    padding: 0.75rem;
                                }

                                @media (min-width: 640px) {
                                    #faIconPickerModal #faIconPickerGrid {
                                        grid-template-columns: repeat(6, minmax(0, 1fr));
                                    }
                                }

                                @media (min-width: 768px) {
                                    #faIconPickerModal #faIconPickerGrid {
                                        grid-template-columns: repeat(8, minmax(0, 1fr));
                                    }
                                }

                                #faIconPickerModal .fa-icon-choice {
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    height: 44px;
                                    width: 100%;
                                    border-radius: 0.75rem;
                                    border: 1px solid rgb(226 232 240);
                                    background: #fff;
                                    color: rgb(51 65 85);
                                    transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
                                }

                                #faIconPickerModal .fa-icon-choice:hover {
                                    background: rgb(248 250 252);
                                    color: rgb(15 23 42);
                                    border-color: rgb(203 213 225);
                                }

                                #faIconPickerModal .fa-icon-choice i {
                                    font-size: 18px;
                                    line-height: 1;
                                }
                            </style>
                            <div class="absolute inset-0 bg-slate-900/40"></div>
                            <div class="absolute inset-x-0 top-10 mx-auto w-full max-w-4xl px-4">
                                <div class="fa-modal-panel overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-200">
                                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                                        <div>
                                            <div class="text-base font-semibold text-slate-900">Choose an Icon</div>
                                            <div class="mt-0.5 text-xs text-slate-500">Search and click an icon. You can also type a class manually.</div>
                                        </div>
                                        <button type="button" id="faIconPickerClose" class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-200">Close</button>
                                    </div>

                                    <div class="fa-modal-body px-5 py-4">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="flex-1">
                                                <label class="block text-xs font-semibold text-slate-700">Search</label>
                                                <input id="faIconPickerSearch" placeholder="Search icons..." class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                            </div>
                                            <div class="sm:w-64">
                                                <label class="block text-xs font-semibold text-slate-700">Selected</label>
                                                <div class="mt-1 flex items-center gap-3 rounded-lg bg-slate-50 px-3 py-2 ring-1 ring-slate-200">
                                                    <div class="grid h-9 w-9 place-items-center rounded-xl bg-white text-slate-800 ring-1 ring-slate-200" aria-hidden="true">
                                                        <i id="faIconPickerPreview" class="fa-solid fa-star"></i>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div id="faIconPickerValue" class="truncate text-xs font-semibold text-slate-800">fa-solid fa-star</div>
                                                        <div class="text-[11px] text-slate-500">Will be saved to this track.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="faIconPickerGridWrap" class="mt-4 max-h-[340px] overflow-auto rounded-xl border border-slate-200">
                                            <div id="faIconPickerGrid" class="grid grid-cols-4 gap-2 p-3 sm:grid-cols-6 md:grid-cols-8">
                                                <div id="faIconPickerLoading" style="grid-column: 1 / -1; padding: 12px; color: rgb(100 116 139); font-size: 14px;">
                                                    Loading icons...
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex items-center justify-end gap-2">
                                            <button type="button" id="faIconPickerApply" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Apply</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            (function () {
                                var modal = document.getElementById('faIconPickerModal');
                                var closeBtn = document.getElementById('faIconPickerClose');
                                var applyBtn = document.getElementById('faIconPickerApply');
                                var search = document.getElementById('faIconPickerSearch');
                                var preview = document.getElementById('faIconPickerPreview');
                                var valueLabel = document.getElementById('faIconPickerValue');
                                var grid = document.getElementById('faIconPickerGrid');

                                if (!modal || !closeBtn || !applyBtn || !search || !preview || !valueLabel || !grid) {
                                    return;
                                }

                                var iconsEndpoint = "{{ route('admin.frontend-editor.fontawesome.icons') }}";
                                var iconsLoaded = false;
                                var allIcons = [];
                                var isLoadingIcons = false;

                                var activeInput = null;
                                var selectedIcon = 'fa-solid fa-star';

                                var legacyMap = {
                                    'code': 'fa-solid fa-code',
                                    'search': 'fa-solid fa-magnifying-glass',
                                    'dotnet': 'fa-brands fa-microsoft',
                                    'design': 'fa-solid fa-palette',
                                    'sparkles': 'fa-solid fa-star',
                                    'rocket': 'fa-solid fa-rocket',
                                    'chart': 'fa-solid fa-chart-line',
                                    'shield': 'fa-solid fa-shield-halved'
                                };

                                function normalizeIcon(val) {
                                    val = (val || '').trim();
                                    if (legacyMap[val]) {
                                        return legacyMap[val];
                                    }
                                    // Keep only the simple supported FA format: "fa-solid fa-xxx".
                                    var m = val.match(/^fa-(solid|regular|brands)\s+fa-[a-z0-9-]+$/);
                                    return m ? val : '';
                                }

                                function escapeHtml(str) {
                                    str = String(str || '');
                                    return str
                                        .replace(/&/g, '&amp;')
                                        .replace(/</g, '&lt;')
                                        .replace(/>/g, '&gt;')
                                        .replace(/"/g, '&quot;')
                                        .replace(/'/g, '&#039;');
                                }

                                function renderIconButtons(list) {
                                    grid.innerHTML = '';

                                    if (!Array.isArray(list) || list.length === 0) {
                                        var empty = document.createElement('div');
                                        empty.style.gridColumn = '1 / -1';
                                        empty.style.padding = '12px';
                                        empty.style.color = 'rgb(100 116 139)';
                                        empty.style.fontSize = '14px';
                                        empty.textContent = 'No icons found.';
                                        grid.appendChild(empty);
                                        return;
                                    }

                                    var frag = document.createDocumentFragment();
                                    list.forEach(function (it) {
                                        if (!it || typeof it !== 'object') {
                                            return;
                                        }
                                        var iconClass = (it.class || '').trim();
                                        var label = (it.label || '').trim();
                                        if (!iconClass) {
                                            return;
                                        }

                                        var btn = document.createElement('button');
                                        btn.type = 'button';
                                        btn.className = 'fa-icon-choice group flex flex-col items-center justify-center gap-1 rounded-xl bg-white p-2 text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500';
                                        btn.setAttribute('data-icon', iconClass);
                                        btn.setAttribute('data-label', (label || iconClass).toLowerCase());
                                        btn.title = label || iconClass;
                                        btn.innerHTML = '<i class="' + escapeHtml(iconClass) + '"></i><span class="sr-only">' + escapeHtml(label || iconClass) + '</span>';
                                        frag.appendChild(btn);
                                    });
                                    grid.appendChild(frag);
                                }

                                function ensureIconsLoaded() {
                                    if (iconsLoaded || isLoadingIcons) {
                                        return;
                                    }
                                    isLoadingIcons = true;

                                    // Keep existing loading placeholder if present.
                                    var loadingEl = document.getElementById('faIconPickerLoading');
                                    if (!loadingEl) {
                                        loadingEl = document.createElement('div');
                                        loadingEl.id = 'faIconPickerLoading';
                                        loadingEl.style.gridColumn = '1 / -1';
                                        loadingEl.style.padding = '12px';
                                        loadingEl.style.color = 'rgb(100 116 139)';
                                        loadingEl.style.fontSize = '14px';
                                        loadingEl.textContent = 'Loading icons...';
                                        grid.appendChild(loadingEl);
                                    }

                                    fetch(iconsEndpoint, { headers: { 'Accept': 'application/json' } })
                                        .then(function (res) {
                                            if (!res.ok) {
                                                throw new Error('Failed to load icons');
                                            }
                                            return res.json();
                                        })
                                        .then(function (json) {
                                            var list = (json && json.icons) ? json.icons : [];
                                            if (!Array.isArray(list)) {
                                                list = [];
                                            }
                                            allIcons = list;
                                            iconsLoaded = true;
                                            renderIconButtons(allIcons);
                                            filterGrid(search.value);
                                        })
                                        .catch(function () {
                                            grid.innerHTML = '';
                                            var err = document.createElement('div');
                                            err.style.gridColumn = '1 / -1';
                                            err.style.padding = '12px';
                                            err.style.color = 'rgb(100 116 139)';
                                            err.style.fontSize = '14px';
                                            err.textContent = 'Could not load the full icon list. You can still type an icon class manually (e.g., fa-solid fa-code).';
                                            grid.appendChild(err);
                                        })
                                        .finally(function () {
                                            isLoadingIcons = false;
                                        });
                                }

                                function setModalSelection(val) {
                                    selectedIcon = val || 'fa-solid fa-star';
                                    preview.className = selectedIcon;
                                    valueLabel.textContent = selectedIcon;
                                }

                                function openModal(forInput) {
                                    activeInput = forInput;
                                    ensureIconsLoaded();
                                    var current = normalizeIcon(activeInput && activeInput.value);
                                    setModalSelection(current || 'fa-solid fa-star');
                                    search.value = '';
                                    filterGrid('');
                                    modal.classList.remove('hidden');
                                    modal.setAttribute('aria-hidden', 'false');
                                }

                                function closeModal() {
                                    modal.classList.add('hidden');
                                    modal.setAttribute('aria-hidden', 'true');
                                    activeInput = null;
                                }

                                function filterGrid(q) {
                                    q = (q || '').trim().toLowerCase();
                                    var items = grid.querySelectorAll('.fa-icon-choice');
                                    items.forEach(function (btn) {
                                        var label = (btn.getAttribute('data-label') || '').toLowerCase();
                                        var icon = (btn.getAttribute('data-icon') || '').toLowerCase();
                                        var ok = q === '' || label.indexOf(q) !== -1 || icon.indexOf(q) !== -1;
                                        btn.style.display = ok ? '' : 'none';
                                    });

                                    // If nothing is loaded yet, trigger a load (so search doesn't feel broken).
                                    if (!iconsLoaded && !isLoadingIcons) {
                                        ensureIconsLoaded();
                                    }
                                }

                                document.addEventListener('click', function (e) {
                                    var openBtn = e.target.closest('.open-icon-picker');
                                    if (openBtn) {
                                        var block = openBtn.closest('.skill-track-block');
                                        if (!block) {
                                            return;
                                        }
                                        var input = block.querySelector('.frontend-icon-input');
                                        if (input) {
                                            openModal(input);
                                        }
                                        return;
                                    }

                                    var clearBtn = e.target.closest('.clear-icon-picker');
                                    if (clearBtn) {
                                        var block2 = clearBtn.closest('.skill-track-block');
                                        if (!block2) {
                                            return;
                                        }
                                        var input2 = block2.querySelector('.frontend-icon-input');
                                        if (input2) {
                                            input2.value = '';
                                            var previewEl = block2.querySelector('i');
                                            if (previewEl) {
                                                previewEl.className = 'fa-solid fa-star';
                                            }
                                        }
                                        return;
                                    }

                                    var choice = e.target.closest('.fa-icon-choice');
                                    if (choice) {
                                        var iconVal = choice.getAttribute('data-icon') || '';
                                        setModalSelection(iconVal);
                                    }
                                });

                                grid.addEventListener('click', function (e) {
                                    // handled by document click delegation
                                });

                                search.addEventListener('input', function () {
                                    filterGrid(search.value);
                                });

                                applyBtn.addEventListener('click', function () {
                                    if (!activeInput) {
                                        closeModal();
                                        return;
                                    }
                                    activeInput.value = selectedIcon;
                                    var block = activeInput.closest('.skill-track-block');
                                    if (block) {
                                        var previewEl = block.querySelector('i');
                                        if (previewEl) {
                                            previewEl.className = selectedIcon;
                                        }
                                    }
                                    closeModal();
                                });

                                closeBtn.addEventListener('click', closeModal);
                                modal.addEventListener('click', function (e) {
                                    if (e.target === modal || e.target.classList.contains('bg-slate-900/40')) {
                                        closeModal();
                                    }
                                });

                                document.addEventListener('keydown', function (e) {
                                    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                                        closeModal();
                                    }
                                });

                                // Live preview when typing manually.
                                document.addEventListener('input', function (e) {
                                    var input = e.target;
                                    if (!input || !input.classList || !input.classList.contains('frontend-icon-input')) {
                                        return;
                                    }
                                    var normalized = normalizeIcon(input.value) || normalizeIcon(input.value) || '';
                                    var block = input.closest('.skill-track-block');
                                    if (!block) {
                                        return;
                                    }
                                    var previewEl = block.querySelector('i');
                                    if (previewEl) {
                                        previewEl.className = normalized || 'fa-solid fa-star';
                                    }
                                });
                            })();
                        </script>

                        <div class="flex items-center justify-end">
                            <button type="submit"
                                    class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                Save Skill Tracks
                            </button>
                        </div>
                    </form>
                </div>
            @elseif($selectedPage->slug === 'about')
                @php
                    $aboutSingles = [
                        'hero' => [
                            'title_bn' => __('frontend.about_title', [], 'bn'),
                            'title_en' => __('frontend.about_title', [], 'en'),
                            'content_bn' => __('frontend.about_subtitle', [], 'bn'),
                            'content_en' => __('frontend.about_subtitle', [], 'en'),
                        ],
                        'about_intro' => [
                            'title_bn' => __('frontend.about_page_intro_title', [], 'bn'),
                            'title_en' => __('frontend.about_page_intro_title', [], 'en'),
                            'content_bn' => __('frontend.about_page_intro_body', [], 'bn'),
                            'content_en' => __('frontend.about_page_intro_body', [], 'en'),
                        ],
                        'about_mission' => [
                            'title_bn' => __('frontend.about_page_mission_title', [], 'bn'),
                            'title_en' => __('frontend.about_page_mission_title', [], 'en'),
                            'content_bn' => __('frontend.about_page_mission_body', [], 'bn'),
                            'content_en' => __('frontend.about_page_mission_body', [], 'en'),
                        ],
                        'about_vision' => [
                            'title_bn' => __('frontend.about_page_vision_title', [], 'bn'),
                            'title_en' => __('frontend.about_page_vision_title', [], 'en'),
                            'content_bn' => __('frontend.about_page_vision_body', [], 'bn'),
                            'content_en' => __('frontend.about_page_vision_body', [], 'en'),
                        ],
                        'about_stats_title' => [
                            'title_bn' => __('frontend.about_page_stats_title', [], 'bn'),
                            'title_en' => __('frontend.about_page_stats_title', [], 'en'),
                            'content_bn' => __('frontend.about_page_stats_subtitle', [], 'bn'),
                            'content_en' => __('frontend.about_page_stats_subtitle', [], 'en'),
                        ],
                        'about_cta' => [
                            'title_bn' => __('frontend.about_page_cta_title', [], 'bn'),
                            'title_en' => __('frontend.about_page_cta_title', [], 'en'),
                            'content_bn' => __('frontend.about_page_cta_body', [], 'bn'),
                            'content_en' => __('frontend.about_page_cta_body', [], 'en'),
                        ],
                    ];

                    $aboutValueKeys = ['about_value_1', 'about_value_2', 'about_value_3', 'about_value_4', 'about_value_5', 'about_value_6'];
                    $aboutStatKeys = ['about_stat_1', 'about_stat_2', 'about_stat_3', 'about_stat_4'];
                @endphp

                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-slate-900">About Page</h3>
                        <p class="mt-1 text-sm text-slate-500">Edit the public <span class="font-semibold">/about</span> page content (BN + EN).</p>
                    </div>

                    <form method="POST"
                          action="{{ route('admin.frontend-editor.sections.bulk-update', $selectedPage) }}"
                          class="space-y-6">
                        @csrf
                        @method('PATCH')

                        @foreach($aboutSingles as $key => $fallbacks)
                            @php $sec = $sectionsByKey->get($key); @endphp
                            <div class="rounded-xl border border-slate-200 p-5">
                                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <div class="text-sm text-slate-500">Section Key</div>
                                        <div class="text-base font-semibold text-slate-900">{{ $key }}</div>
                                    </div>
                                    <select name="sections[{{ $key }}][status]"
                                            class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="active" @selected(old('sections.' . $key . '.status', optional($sec)->status ?? 'active') === 'active')>Active</option>
                                        <option value="inactive" @selected(old('sections.' . $key . '.status', optional($sec)->status) === 'inactive')>Inactive</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Title (BN)</label>
                                        <input name="sections[{{ $key }}][title_bn]"
                                               value="{{ old('sections.' . $key . '.title_bn', optional($sec)->title_bn ?: ($fallbacks['title_bn'] ?? '')) }}"
                                               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Title (EN)</label>
                                        <input name="sections[{{ $key }}][title_en]"
                                               value="{{ old('sections.' . $key . '.title_en', optional($sec)->title_en ?: ($fallbacks['title_en'] ?? '')) }}"
                                               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Content (BN)</label>
                                        <textarea name="sections[{{ $key }}][content_bn]" rows="5"
                                                  class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.' . $key . '.content_bn', optional($sec)->content_bn ?: ($fallbacks['content_bn'] ?? '')) }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Content (EN)</label>
                                        <textarea name="sections[{{ $key }}][content_en]" rows="5"
                                                  class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.' . $key . '.content_en', optional($sec)->content_en ?: ($fallbacks['content_en'] ?? '')) }}</textarea>
                                    </div>

                                    @if($key === 'about_cta')
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Button Text (BN)</label>
                                            <input name="sections[{{ $key }}][button_text_bn]"
                                                   value="{{ old('sections.' . $key . '.button_text_bn', optional($sec)->button_text_bn ?: __('frontend.about_page_cta_button', [], 'bn')) }}"
                                                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Button Text (EN)</label>
                                            <input name="sections[{{ $key }}][button_text_en]"
                                                   value="{{ old('sections.' . $key . '.button_text_en', optional($sec)->button_text_en ?: __('frontend.about_page_cta_button', [], 'en')) }}"
                                                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        </div>
                                        <div class="lg:col-span-2">
                                            <label class="block text-sm font-medium text-slate-700">Button Link</label>
                                            <input name="sections[{{ $key }}][button_link]"
                                                   value="{{ old('sections.' . $key . '.button_link', optional($sec)->button_link ?: '/courses') }}"
                                                   placeholder="e.g. /courses"
                                                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <div class="rounded-xl border border-slate-200 p-5">
                            <div class="mb-4">
                                <h4 class="text-base font-semibold text-slate-900">Values / Highlights</h4>
                                <p class="mt-1 text-sm text-slate-500">These render as cards on the About page.</p>
                            </div>

                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                @foreach($aboutValueKeys as $i => $key)
                                    @php
                                        $sec = $sectionsByKey->get($key);
                                        $fallbackTitleBn = __('frontend.about_page_value_' . ($i + 1) . '_title', [], 'bn');
                                        $fallbackTitleEn = __('frontend.about_page_value_' . ($i + 1) . '_title', [], 'en');
                                        $fallbackDescBn = __('frontend.about_page_value_' . ($i + 1) . '_desc', [], 'bn');
                                        $fallbackDescEn = __('frontend.about_page_value_' . ($i + 1) . '_desc', [], 'en');
                                    @endphp
                                    <div class="rounded-xl border border-slate-200 p-5">
                                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                            <div>
                                                <div class="text-sm text-slate-500">Section Key</div>
                                                <div class="text-base font-semibold text-slate-900">{{ $key }}</div>
                                            </div>
                                            <select name="sections[{{ $key }}][status]"
                                                    class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="active" @selected(old('sections.' . $key . '.status', optional($sec)->status ?? 'active') === 'active')>Active</option>
                                                <option value="inactive" @selected(old('sections.' . $key . '.status', optional($sec)->status) === 'inactive')>Inactive</option>
                                            </select>
                                        </div>

                                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700">Title (BN)</label>
                                                <input name="sections[{{ $key }}][title_bn]"
                                                       value="{{ old('sections.' . $key . '.title_bn', optional($sec)->title_bn ?: $fallbackTitleBn) }}"
                                                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700">Title (EN)</label>
                                                <input name="sections[{{ $key }}][title_en]"
                                                       value="{{ old('sections.' . $key . '.title_en', optional($sec)->title_en ?: $fallbackTitleEn) }}"
                                                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700">Content (BN)</label>
                                                <textarea name="sections[{{ $key }}][content_bn]" rows="4"
                                                          class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.' . $key . '.content_bn', optional($sec)->content_bn ?: $fallbackDescBn) }}</textarea>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700">Content (EN)</label>
                                                <textarea name="sections[{{ $key }}][content_en]" rows="4"
                                                          class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.' . $key . '.content_en', optional($sec)->content_en ?: $fallbackDescEn) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-5">
                            <div class="mb-4">
                                <h4 class="text-base font-semibold text-slate-900">Stats</h4>
                                <p class="mt-1 text-sm text-slate-500">Each stat uses <span class="font-mono">title</span> as the big value and <span class="font-mono">content</span> as the label.</p>
                            </div>

                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                @foreach($aboutStatKeys as $i => $key)
                                    @php
                                        $sec = $sectionsByKey->get($key);
                                        $fallbackValueBn = __('frontend.about_page_stat_' . ($i + 1) . '_value', [], 'bn');
                                        $fallbackValueEn = __('frontend.about_page_stat_' . ($i + 1) . '_value', [], 'en');
                                        $fallbackLabelBn = __('frontend.about_page_stat_' . ($i + 1) . '_label', [], 'bn');
                                        $fallbackLabelEn = __('frontend.about_page_stat_' . ($i + 1) . '_label', [], 'en');
                                    @endphp
                                    <div class="rounded-xl border border-slate-200 p-5">
                                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                            <div>
                                                <div class="text-sm text-slate-500">Section Key</div>
                                                <div class="text-base font-semibold text-slate-900">{{ $key }}</div>
                                            </div>
                                            <select name="sections[{{ $key }}][status]"
                                                    class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="active" @selected(old('sections.' . $key . '.status', optional($sec)->status ?? 'active') === 'active')>Active</option>
                                                <option value="inactive" @selected(old('sections.' . $key . '.status', optional($sec)->status) === 'inactive')>Inactive</option>
                                            </select>
                                        </div>

                                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700">Value (BN)</label>
                                                <input name="sections[{{ $key }}][title_bn]"
                                                       value="{{ old('sections.' . $key . '.title_bn', optional($sec)->title_bn ?: $fallbackValueBn) }}"
                                                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700">Value (EN)</label>
                                                <input name="sections[{{ $key }}][title_en]"
                                                       value="{{ old('sections.' . $key . '.title_en', optional($sec)->title_en ?: $fallbackValueEn) }}"
                                                       class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700">Label (BN)</label>
                                                <textarea name="sections[{{ $key }}][content_bn]" rows="3"
                                                          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.' . $key . '.content_bn', optional($sec)->content_bn ?: $fallbackLabelBn) }}</textarea>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700">Label (EN)</label>
                                                <textarea name="sections[{{ $key }}][content_en]" rows="3"
                                                          class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('sections.' . $key . '.content_en', optional($sec)->content_en ?: $fallbackLabelEn) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="submit"
                                    class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                Save About Page
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Sections</h3>
                    <p class="mt-1 text-sm text-slate-500">Edit sections for <span class="font-semibold">{{ $selectedPage->slug }}</span>.</p>
                </div>

                @php
                    $sectionsForList = $selectedPage->slug === 'home' ? $otherSections : $sections;
                @endphp

                @if($sectionsForList->isEmpty())
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                        No sections found for this page.
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach($sectionsForList as $section)
                            @php
                                $isHeroSideHeading = $section->section_key === 'hero_side_heading';
                            @endphp
                            <div class="rounded-xl border border-slate-200 bg-white p-5">
                                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <div class="text-sm text-slate-500">Section Key</div>
                                        <div class="text-base font-semibold text-slate-900">{{ $section->section_key }}</div>
                                    </div>

                                    <div class="text-xs font-semibold">
                                        @if($section->status === 'active')
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700 ring-1 ring-emerald-100">Active</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-slate-700 ring-1 ring-slate-200">Inactive</span>
                                        @endif
                                    </div>
                                </div>

                                <form method="POST"
                                      action="{{ route('admin.frontend-editor.sections.update', $section) }}"
                                      enctype="multipart/form-data"
                                      class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                    @csrf
                                    @method('PATCH')

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Title (BN)</label>
                                        <input name="title_bn" value="{{ old('title_bn', $section->title_bn) }}"
                                               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Title (EN)</label>
                                        <input name="title_en" value="{{ old('title_en', $section->title_en) }}"
                                               class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                    </div>

                                    @if(! $isHeroSideHeading)
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Content (BN)</label>
                                            <textarea name="content_bn" rows="6"
                                                      class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('content_bn', $section->content_bn) }}</textarea>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Content (EN)</label>
                                            <textarea name="content_en" rows="6"
                                                      class="wysiwyg mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('content_en', $section->content_en) }}</textarea>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Image</label>
                                            <input type="file" name="image"
                                                   class="mt-1 block w-full text-sm text-slate-700 file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200" />

                                            @if($section->image_path)
                                                <div class="mt-3">
                                                    <div class="text-xs text-slate-500">Current</div>
                                                    <img
                                                        src="{{ asset('storage/' . $section->image_path) }}"
                                                        alt="{{ $section->section_key }}"
                                                        class="mt-1 h-20 w-auto rounded-lg border border-slate-200"
                                                    />
                                                </div>
                                            @endif
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Button Link</label>
                                            <input name="button_link" value="{{ old('button_link', $section->button_link) }}"
                                                   placeholder="e.g. /contact"
                                                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Button Text (BN)</label>
                                            <input name="button_text_bn" value="{{ old('button_text_bn', $section->button_text_bn) }}"
                                                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Button Text (EN)</label>
                                            <input name="button_text_en" value="{{ old('button_text_en', $section->button_text_en) }}"
                                                   class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                        </div>
                                    @endif

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Status</label>
                                        <select name="status"
                                                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="active" @selected(old('status', $section->status) === 'active')>Active</option>
                                            <option value="inactive" @selected(old('status', $section->status) === 'inactive')>Inactive</option>
                                        </select>
                                    </div>

                                    <div class="flex items-end justify-end lg:col-span-2">
                                        <button type="submit"
                                                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>

    <style>
        /* Panel accordion fallback (works even if Tailwind isn't rebuilt). */
        .fe-accordion-toggle {
            border: 1px solid rgb(226 232 240);
            background: rgb(248 250 252);
            color: rgb(51 65 85);
            border-radius: 0.5rem;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
        }

        .fe-accordion-toggle:hover {
            background: rgb(241 245 249);
        }
    </style>

    <script>
        (function () {
            var pageKey = @json(($tab ?? 'pages') === 'header' ? 'header' : (string) ($selectedPage->slug ?? 'unknown'));

            // Only collapse top-level editor panels (not each individual section card).
            var panels = document.querySelectorAll('div.rounded-xl.bg-white.p-6.shadow-sm.ring-1.ring-slate-200');
            if (!panels || panels.length === 0) {
                return;
            }

            function toStorageKey(title) {
                title = (title || '').toString().trim().toLowerCase();
                title = title.replace(/\s+/g, '-').replace(/[^a-z0-9\-]/g, '');
                if (!title) {
                    title = 'panel';
                }
                return 'fe:accordion:' + pageKey + ':' + title;
            }

            panels.forEach(function (panel, idx) {
                if (!panel || panel.getAttribute('data-fe-accordion') === '1') {
                    return;
                }

                var h3 = panel.querySelector('h3.text-lg.font-semibold.text-slate-900');
                if (!h3) {
                    return;
                }

                var title = (h3.textContent || '').trim() || ('Panel ' + (idx + 1));
                var header = h3.closest('div');
                if (!header || header.parentElement !== panel) {
                    // We only support the standard markup where the header is a direct child of panel.
                    return;
                }

                var storageKey = toStorageKey(title);

                // Wrap panel body (everything after header) so we can hide/show it.
                var body = document.createElement('div');
                body.className = 'fe-accordion-body';

                var node = header.nextSibling;
                while (node) {
                    var next = node.nextSibling;
                    body.appendChild(node);
                    node = next;
                }
                panel.appendChild(body);

                // Convert header into a flex row with a toggle button on the right.
                var left = document.createElement('div');
                while (header.firstChild) {
                    left.appendChild(header.firstChild);
                }

                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'fe-accordion-toggle';
                btn.setAttribute('aria-label', 'Toggle ' + title);
                btn.setAttribute('aria-expanded', 'true');

                header.style.display = 'flex';
                header.style.alignItems = 'flex-start';
                header.style.justifyContent = 'space-between';
                header.style.gap = '12px';

                header.appendChild(left);
                header.appendChild(btn);

                function setCollapsed(collapsed) {
                    if (collapsed) {
                        body.style.display = 'none';
                        btn.textContent = 'Expand';
                        btn.setAttribute('aria-expanded', 'false');
                    } else {
                        body.style.display = '';
                        btn.textContent = 'Minimize';
                        btn.setAttribute('aria-expanded', 'true');
                    }

                    try {
                        localStorage.setItem(storageKey, collapsed ? '1' : '0');
                    } catch (e) {
                        // ignore
                    }
                }

                var initialCollapsed = false;
                try {
                    initialCollapsed = localStorage.getItem(storageKey) === '1';
                } catch (e) {
                    initialCollapsed = false;
                }
                setCollapsed(initialCollapsed);

                btn.addEventListener('click', function () {
                    var isCollapsed = body.style.display === 'none';
                    setCollapsed(!isCollapsed);
                });

                panel.setAttribute('data-fe-accordion', '1');
            });
        })();
    </script>
</x-app-layout>
