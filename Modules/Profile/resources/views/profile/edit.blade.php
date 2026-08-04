@push('styles')
    <style>
        .profile-page {
            --profile-primary: #2E3192;
            --profile-primary-dark: #17194f;
            --profile-accent: #F47B20;
            --profile-border: #dbe3ef;
            --profile-muted: #64748b;
        }

        .profile-page .profile-section {
            scroll-margin-top: 10.5rem;
        }

        @media (max-width: 1023px) {
            .profile-page .profile-section {
                scroll-margin-top: 9.25rem;
            }
        }

        .profile-page .profile-section > section > header {
            margin-bottom: 1.25rem;
            border-bottom: 1px solid #edf2f7;
            padding-bottom: 1rem;
        }

        .profile-page .profile-section > section > header h2 {
            color: #0f172a;
            font-size: 1rem;
            font-weight: 900;
            letter-spacing: 0;
        }

        .profile-page .profile-section > section > header p {
            margin-top: .35rem;
            max-width: 42rem;
            color: var(--profile-muted);
            font-size: .875rem;
            line-height: 1.55;
        }

        .profile-page .profile-section form {
            margin-top: 0 !important;
        }

        .profile-page .profile-section input:not([type="checkbox"]):not([type="file"]),
        .profile-page .profile-section select,
        .profile-page .profile-section textarea {
            min-height: 2.75rem;
            width: 100%;
            border-color: #cbd5e1;
            border-radius: .5rem;
            background-color: #fff;
            color: #0f172a;
            font-weight: 600;
            box-shadow: none;
        }

        .profile-page .profile-section textarea {
            min-height: 7rem;
        }

        .profile-page .profile-section input:not([type="checkbox"]):not([type="file"]):focus,
        .profile-page .profile-section select:focus,
        .profile-page .profile-section textarea:focus {
            border-color: var(--profile-primary);
            box-shadow: 0 0 0 3px rgba(46, 49, 146, .12);
        }

        .profile-page .profile-section input[type="file"] {
            border-radius: .5rem;
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
            padding: .75rem;
        }

        .profile-page .profile-section label {
            color: #334155;
            font-size: .8125rem;
            font-weight: 800;
        }

        .profile-page .profile-section button.bg-gray-800 {
            min-height: 2.65rem;
            border-radius: .5rem !important;
            background: var(--profile-primary) !important;
            padding: .65rem 1.1rem !important;
            letter-spacing: .04em !important;
            box-shadow: 0 10px 22px rgba(46, 49, 146, .18);
        }

        .profile-page .profile-section button.bg-gray-800:hover {
            background: var(--profile-primary-dark) !important;
        }

        .profile-page .profile-section button.bg-red-600 {
            border-radius: .5rem !important;
            box-shadow: 0 10px 22px rgba(220, 38, 38, .15);
        }

        .profile-page .profile-section .rounded-lg.border.border-gray-200.p-4 {
            border-color: #dbe3ef;
            background: linear-gradient(180deg, #fff, #f8fafc);
            box-shadow: 0 12px 28px rgba(15, 23, 42, .05);
        }

        .profile-page .profile-section .text-indigo-600 {
            color: var(--profile-primary) !important;
        }
    </style>
@endpush

<x-app-layout>
    @php
        $profile = $user->profile;
        $address = $user->address;
        $roleNames = method_exists($user, 'getRoleNames') ? $user->getRoleNames() : collect();
        $primaryRole = $roleNames->first() ?: 'Member';
        $publicUrl = $profile?->public_url;
        $publicLink = $publicUrl ? route('profile.public.show', ['public_url' => $publicUrl]) : null;
        $isMentor = $user && method_exists($user, 'hasRole') ? $user->hasRole('mentor') : false;

        $completionChecks = [
            filled($user->name),
            filled($user->email),
            filled($user->profile_image),
            filled($profile?->mobile_number),
            filled($profile?->bio),
            filled($publicUrl),
            filled($address?->city) && filled($address?->country),
            $user->educations->isNotEmpty(),
            $user->experiences->isNotEmpty(),
            $user->skills->isNotEmpty(),
        ];
        $completedCount = collect($completionChecks)->filter()->count();
        $completionPercent = (int) round(($completedCount / count($completionChecks)) * 100);

        $profileSections = [
            ['id' => 'public-link', 'label' => 'Public Link', 'icon' => 'fa-link'],
            ['id' => 'account', 'label' => 'Account', 'icon' => 'fa-user'],
            ['id' => 'details', 'label' => 'Details', 'icon' => 'fa-id-card'],
            ['id' => 'address', 'label' => 'Address', 'icon' => 'fa-location-dot'],
            ['id' => 'education', 'label' => 'Education', 'icon' => 'fa-graduation-cap'],
            ['id' => 'experience', 'label' => 'Experience', 'icon' => 'fa-briefcase'],
            ['id' => 'skills', 'label' => 'Skills', 'icon' => 'fa-screwdriver-wrench'],
            ['id' => 'security', 'label' => 'Security', 'icon' => 'fa-shield-halved'],
        ];
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Profile</p>
                <h2 class="mt-1 text-2xl font-black leading-tight text-slate-950">Manage your profile</h2>
            </div>
            @if($publicLink)
                <a href="{{ $publicLink }}" target="_blank" rel="noreferrer" class="inline-flex min-h-11 w-fit items-center justify-center gap-2 rounded-lg border border-[#2E3192]/20 bg-white px-4 py-2 text-sm font-extrabold text-[#2E3192] shadow-sm transition hover:border-[#2E3192]/40 hover:bg-[#2E3192]/5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                    View public profile
                </a>
            @endif
        </div>
    </x-slot>

    <div class="profile-page mx-auto max-w-7xl space-y-6">
        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="bg-[#17194f] px-5 py-6 text-white sm:px-7">
                <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                    <div class="flex min-w-0 items-center gap-4">
                        <x-avatar :user="$user" size="h-20 w-20" text="text-2xl" class="ring-4 ring-white/20" />
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="truncate text-2xl font-black leading-tight sm:text-3xl">{{ $user->name }}</h1>
                                <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-extrabold uppercase tracking-wide text-white/90 ring-1 ring-white/10">{{ ucfirst($primaryRole) }}</span>
                            </div>
                            <p class="mt-1 truncate text-sm font-semibold text-white/72">{{ $user->email }}</p>
                            <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold text-white/78">
                                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 ring-1 ring-white/10">
                                    <i class="fa-solid fa-envelope-circle-check"></i>
                                    {{ $user->email_verified_at ? 'Verified email' : 'Email not verified' }}
                                </span>
                                @if($profile?->mobile_number)
                                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 ring-1 ring-white/10">
                                        <i class="fa-solid fa-phone"></i>
                                        {{ $profile->mobile_number }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="w-full max-w-sm rounded-lg bg-white/10 p-4 ring-1 ring-white/10 md:w-72">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-extrabold uppercase tracking-[0.18em] text-white/62">Profile completion</span>
                            <span class="text-2xl font-black">{{ $completionPercent }}%</span>
                        </div>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/20">
                            <div class="h-full rounded-full bg-[#F47B20]" style="width: {{ $completionPercent }}%"></div>
                        </div>
                        <div class="mt-2 text-xs font-semibold text-white/70">{{ $completedCount }} of {{ count($completionChecks) }} items complete</div>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[18rem_minmax(0,1fr)]">
            <aside class="space-y-4 xl:sticky xl:top-24 xl:self-start">
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Sections</div>
                    <nav class="mt-4 space-y-1">
                        @foreach($profileSections as $section)
                            <a href="#{{ $section['id'] }}" class="flex min-h-10 items-center gap-3 rounded-lg px-3 text-sm font-extrabold text-slate-700 transition hover:bg-[#2E3192]/5 hover:text-[#2E3192]">
                                <span class="grid h-7 w-7 place-items-center rounded-lg bg-slate-100 text-[#2E3192]">
                                    <i class="fa-solid {{ $section['icon'] }} text-xs"></i>
                                </span>
                                {{ $section['label'] }}
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Summary</div>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="font-bold text-slate-500">Education</dt>
                            <dd class="font-black text-slate-950">{{ $user->educations->count() }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="font-bold text-slate-500">Experience</dt>
                            <dd class="font-black text-slate-950">{{ $user->experiences->count() }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="font-bold text-slate-500">Skills</dt>
                            <dd class="font-black text-slate-950">{{ $user->skills->count() }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="font-bold text-slate-500">Public link</dt>
                            <dd class="font-black {{ $publicLink ? 'text-emerald-600' : 'text-slate-400' }}">{{ $publicLink ? 'Active' : 'Off' }}</dd>
                        </div>
                    </dl>
                </div>
            </aside>

            <div class="space-y-6">
                <div id="public-link" class="profile-section rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    @include('profile.partials.public-url')
                </div>

                <div id="account" class="profile-section rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div id="details" class="profile-section rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    @include('profile.partials.update-profile-details-form')
                </div>

                <div id="address" class="profile-section rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    @include('profile.partials.update-address-form')
                </div>

                <div id="education" class="profile-section rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    @include('profile.partials.manage-educations-form')
                </div>

                <div id="experience" class="profile-section rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    @include('profile.partials.manage-experiences-form')
                </div>

                <div id="skills" class="profile-section rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    @include('profile.partials.manage-skills-form')
                </div>

                <div id="security" class="profile-section rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    @include('profile.partials.update-password-form')
                </div>

                @unless($isMentor)
                    <div class="profile-section rounded-lg border border-rose-200 bg-white p-5 shadow-sm sm:p-6">
                        @include('profile.partials.delete-user-form')
                    </div>
                @endunless
            </div>
        </div>
    </div>
</x-app-layout>
