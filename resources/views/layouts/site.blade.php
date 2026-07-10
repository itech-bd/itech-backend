<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'iTechBD Ltd'))</title>

    @php
        $faviconPath = $frontendSettings['site_favicon_path'] ?? null;
        $faviconCandidate = $faviconPath ? public_path('storage/' . ltrim((string) $faviconPath, '/')) : null;
        $faviconUrl = ($faviconCandidate && file_exists($faviconCandidate))
            ? asset('storage/' . ltrim((string) $faviconPath, '/'))
            : asset('favicon.ico');
    @endphp
    <link rel="icon" href="{{ $faviconUrl }}" sizes="any">
    <link rel="shortcut icon" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800,900" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer" />

    @php
        $manifestPath = public_path('build/manifest.json');
        $hotPath = public_path('hot');
        $useHot = false;

        if (file_exists($hotPath)) {
            $hotUrl = trim((string) @file_get_contents($hotPath));
            if ($hotUrl !== '') {
                $parts = parse_url($hotUrl);
                $host = $parts['host'] ?? null;
                $port = $parts['port'] ?? (($parts['scheme'] ?? '') === 'https' ? 443 : 80);
                if ($host && $port) {
                    $conn = @fsockopen($host, (int) $port, $errno, $errstr, 0.15);
                    if (is_resource($conn)) {
                        fclose($conn);
                        $useHot = true;
                    }
                }
            }
        }

        $manifest = null;
        if (! $useHot && file_exists($manifestPath)) {
            $manifest = json_decode((string) file_get_contents($manifestPath), true) ?: [];
        }
    @endphp

    @if ($useHot)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @elseif (is_array($manifest))
        @if (!empty($manifest['resources/css/app.css']['file']))
            <link rel="stylesheet" href="{{ asset('build/' . $manifest['resources/css/app.css']['file']) }}">
        @endif
        @if (!empty($manifest['resources/js/app.js']['file']))
            <script type="module" src="{{ asset('build/' . $manifest['resources/js/app.js']['file']) }}"></script>
        @endif
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    @stack('head')

    <style>
        :root {
            --itech-blue: #292b86;
            --itech-orange: #f15a24;
            --itech-red: #ed1c24;
            --itech-ink: #111827;
        }

        html {
            scroll-behavior: smooth;
            color-scheme: light;
        }

        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        [x-cloak] { display: none !important; }

        .brand-container {
            width: min(100% - 2rem, 80rem);
            margin-inline: auto;
        }

        .brand-grid {
            background-image:
                linear-gradient(rgba(41,43,134,.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(41,43,134,.06) 1px, transparent 1px);
            background-size: 24px 24px;
        }

        .reveal {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity 650ms ease, transform 650ms ease;
        }

        .reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .site-prose :where(h1,h2,h3) {
            color: #0f172a;
            font-weight: 800;
            line-height: 1.2;
            margin-top: 1.35rem;
            margin-bottom: .65rem;
        }

        .site-prose :where(h1) { font-size: 1.875rem; }
        .site-prose :where(h2) { font-size: 1.45rem; }
        .site-prose :where(h3) { font-size: 1.18rem; }
        .site-prose :where(p) { margin-top: .85rem; line-height: 1.85; color: #475569; }
        .site-prose :where(ul,ol) { margin-top: .85rem; margin-left: 1.25rem; color: #475569; line-height: 1.85; }
        .site-prose :where(ul) { list-style: disc; }
        .site-prose :where(ol) { list-style: decimal; }
        .site-prose :where(a) { color: var(--itech-blue); font-weight: 700; text-decoration: underline; }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            .reveal { opacity: 1; transform: none; transition: none; }
        }
    </style>

    @if (session('auth_modal'))
        <script>
            window.__authModalToOpen = @json(session('auth_modal'));
        </script>
    @endif
</head>
<body class="min-h-screen bg-[#fbfbff] text-slate-900 antialiased">
    @php
        $sitePhone = $frontendSettings['site_phone'] ?? '+880 10 0000 0000';
        $sitePhoneTel = 'tel:' . preg_replace('/[^\d+]/', '', (string) $sitePhone);
        $siteEmail = $frontendSettings['site_email'] ?? 'info@example.com';
        $siteEmailMailto = 'mailto:' . (string) $siteEmail;
        $siteAddress = $frontendSettings['site_address'] ?? 'Dhaka, Bangladesh';

        $logoPath = $frontendSettings['site_logo_path'] ?? null;
        $logoCandidate = $logoPath ? public_path('storage/' . ltrim((string) $logoPath, '/')) : null;
        $logoUrl = ($logoCandidate && file_exists($logoCandidate))
            ? asset('storage/' . ltrim((string) $logoPath, '/'))
            : (file_exists(public_path('brand/itechbd-logo.png')) ? asset('brand/itechbd-logo.png') : asset('brand/itechbd-logo.svg'));

        $navItems = [
            ['label' => __('frontend.home'), 'url' => route('home'), 'active' => request()->routeIs('home')],
            ['label' => __('frontend.about'), 'url' => route('about'), 'active' => request()->routeIs('about')],
            ['label' => __('frontend.courses'), 'url' => route('courses'), 'active' => request()->routeIs('courses*') || request()->routeIs('checkout.*')],
            [
                'label' => 'Solutions',
                'url' => route('solutions.software'),
                'active' => request()->routeIs('solutions.*'),
                'children' => [
                    ['label' => 'Software Solutions', 'url' => route('solutions.software'), 'active' => request()->routeIs('solutions.software')],
                    ['label' => 'IT Solutions', 'url' => route('solutions.it'), 'active' => request()->routeIs('solutions.it')],
                    ['label' => 'Web Hosting Solutions', 'url' => route('solutions.hosting'), 'active' => request()->routeIs('solutions.hosting')],
                ],
            ],
            ['label' => __('frontend.mentors'), 'url' => route('mentors'), 'active' => request()->routeIs('mentors*')],
            ['label' => __('frontend.reviews'), 'url' => route('reviews'), 'active' => request()->routeIs('reviews')],
            ['label' => __('frontend.news'), 'url' => route('news'), 'active' => request()->routeIs('news*')],
            ['label' => __('frontend.contact'), 'url' => url('/contact'), 'active' => request()->is('contact')],
        ];

        $footerFacebookUrl = $frontendSettings['footer_facebook_url'] ?? '#';
        $footerLinkedinUrl = $frontendSettings['footer_linkedin_url'] ?? '#';
        $footerYoutubeUrl = $frontendSettings['footer_youtube_url'] ?? '#';
        $footerBrandTagline = $frontendSettings['footer_brand_tagline'] ?? 'Training Institute';
        $footerBrandDescription = $frontendSettings['footer_brand_description'] ?? 'Develop software and professional skills with practical training, mentor support, and career-focused courses.';
        $footerContactTitle = $frontendSettings['footer_contact_title'] ?? 'Get in Touch';
        $footerPhoneLabel = $frontendSettings['footer_phone_label'] ?? 'Phone';
        $footerEmailLabel = $frontendSettings['footer_email_label'] ?? 'Email';
        $footerLocationLabel = $frontendSettings['footer_location_label'] ?? 'Address';
        $footerUsefulLinks = [
            ['label' => 'Course', 'url' => route('courses')],
            ['label' => 'Workshop', 'url' => route('courses')],
            ['label' => 'Event', 'url' => route('news')],
            ['label' => 'Archive', 'url' => route('news')],
            ['label' => 'Team', 'url' => route('mentors')],
            ['label' => 'About Us', 'url' => route('about')],
            ['label' => 'Our Vision & Mission', 'url' => route('about')],
            ['label' => 'Trainer', 'url' => route('mentors')],
            ['label' => 'Student Review', 'url' => route('reviews')],
            ['label' => 'Career', 'url' => route('about')],
            ['label' => 'FAQ', 'url' => url('/contact')],
            ['label' => 'Privacy & Policy', 'url' => route('privacy')],
            ['label' => 'Terms & Conditions', 'url' => route('terms')],
        ];
        $footerAccreditations = [
            ['title' => 'Accredited By', 'value' => config('app.name', 'iTechBD Ltd')],
            ['title' => 'A Concern Of', 'value' => 'bitBirds Solutions'],
            ['title' => 'Member Of', 'value' => 'BASIS Member'],
        ];
    @endphp

    <div class="fixed inset-0 -z-10 brand-grid bg-[#fbfbff]"></div>
    <div class="fixed inset-x-0 top-0 -z-10 h-[30rem] bg-gradient-to-b from-[#292b86]/10 via-[#f15a24]/5 to-transparent"></div>

    <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl">
        <div class="hidden border-b border-slate-100 bg-[#292b86] text-white lg:block">
            <div class="brand-container flex items-center justify-between py-2 text-xs font-medium">
                <div class="flex min-w-0 items-center gap-5">
                    <span class="inline-flex items-center gap-2 truncate"><i class="fa-solid fa-location-dot text-[#f15a24]"></i>{{ $siteAddress }}</span>
                </div>
                <div class="flex items-center gap-5">
                    <a href="{{ $sitePhoneTel }}" class="inline-flex items-center gap-2 hover:text-[#f15a24]"><i class="fa-solid fa-phone"></i>{{ $sitePhone }}</a>
                    <a href="{{ $siteEmailMailto }}" class="inline-flex items-center gap-2 hover:text-[#f15a24]"><i class="fa-solid fa-envelope"></i>{{ $siteEmail }}</a>
                    <a href="{{ route('language.switch', app()->getLocale() === 'bn' ? 'en' : 'bn') }}" class="rounded-full bg-white/10 px-3 py-1 font-bold hover:bg-white/20">
                        {{ app()->getLocale() === 'bn' ? 'English' : 'বাংলা' }}
                    </a>
                </div>
            </div>
        </div>

        <div class="brand-container flex h-20 items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                <img src="{{ $logoUrl }}" alt="{{ config('app.name', 'iTechBD Ltd') }}" class="h-12 w-auto max-w-[210px] object-contain sm:h-14">
            </a>

            <nav class="hidden items-center gap-1 lg:flex">
                @foreach($navItems as $item)
                    @if(!empty($item['children']))
                        <div class="group relative">
                            <a href="{{ $item['url'] }}" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-bold transition {{ $item['active'] ? 'bg-[#292b86] text-white shadow-lg shadow-[#292b86]/20' : 'text-slate-700 hover:bg-[#292b86]/5 hover:text-[#292b86]' }}">
                                {{ $item['label'] }}
                                <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </a>
                            <div class="invisible absolute left-0 top-full z-20 w-64 pt-3 opacity-0 transition duration-200 group-hover:visible group-hover:opacity-100">
                                <div class="translate-y-2 rounded-[1.4rem] border border-slate-200 bg-white p-2 shadow-2xl shadow-[#292b86]/10 transition duration-200 group-hover:translate-y-0">
                                @foreach($item['children'] as $child)
                                    <a href="{{ $child['url'] }}" class="block rounded-2xl px-4 py-3 text-sm font-bold transition {{ $child['active'] ? 'bg-[#292b86] text-white' : 'text-slate-700 hover:bg-[#292b86]/5 hover:text-[#292b86]' }}">
                                        {{ $child['label'] }}
                                    </a>
                                @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ $item['url'] }}" class="rounded-full px-4 py-2 text-sm font-bold transition {{ $item['active'] ? 'bg-[#292b86] text-white shadow-lg shadow-[#292b86]/20' : 'text-slate-700 hover:bg-[#292b86]/5 hover:text-[#292b86]' }}">
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="hidden items-center gap-3 lg:flex">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-full border border-[#292b86]/15 px-5 py-2.5 text-sm font-extrabold text-[#292b86] transition hover:bg-[#292b86] hover:text-white">
                        {{ __('frontend.dashboard') }}
                    </a>
                @else
                    <button type="button" data-auth-trigger="login" class="rounded-full border border-slate-200 px-5 py-2.5 text-sm font-extrabold text-slate-700 transition hover:border-[#292b86] hover:text-[#292b86]">
                        {{ __('frontend.login') }}
                    </button>
                    <button type="button" data-auth-trigger="register" class="rounded-full bg-[#f15a24] px-5 py-2.5 text-sm font-extrabold text-white shadow-lg shadow-[#f15a24]/20 transition hover:bg-[#ed1c24]">
                        {{ __('frontend.register') }}
                    </button>
                @endauth
            </div>

            <button type="button" id="siteMobileMenuButton" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 text-[#292b86] lg:hidden" aria-label="Open menu" aria-expanded="false">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <div id="siteMobileMenu" class="hidden border-t border-slate-100 bg-white lg:hidden">
            <div class="brand-container grid gap-2 py-4">
                @foreach($navItems as $item)
                    @if(!empty($item['children']))
                        <div class="rounded-2xl {{ $item['active'] ? 'bg-[#292b86]/5' : 'bg-slate-50' }} p-2">
                            <a href="{{ $item['url'] }}" class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-bold {{ $item['active'] ? 'bg-[#292b86] text-white' : 'text-slate-700' }}">
                                <span>{{ $item['label'] }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </a>
                            <div class="mt-2 grid gap-2 pl-3">
                                @foreach($item['children'] as $child)
                                    <a href="{{ $child['url'] }}" class="rounded-2xl px-4 py-3 text-sm font-bold {{ $child['active'] ? 'bg-[#292b86] text-white' : 'bg-white text-slate-700' }}">
                                        {{ $child['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $item['url'] }}" class="rounded-2xl px-4 py-3 text-sm font-bold {{ $item['active'] ? 'bg-[#292b86] text-white' : 'bg-slate-50 text-slate-700' }}">{{ $item['label'] }}</a>
                    @endif
                @endforeach

                <div class="mt-2 grid grid-cols-2 gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="col-span-2 rounded-2xl bg-[#292b86] px-4 py-3 text-center text-sm font-extrabold text-white">{{ __('frontend.dashboard') }}</a>
                    @else
                        <button type="button" data-auth-trigger="login" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-extrabold text-slate-700">{{ __('frontend.login') }}</button>
                        <button type="button" data-auth-trigger="register" class="rounded-2xl bg-[#f15a24] px-4 py-3 text-sm font-extrabold text-white">{{ __('frontend.register') }}</button>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    @yield('content')

    <footer class="mt-16 border-t border-slate-200 bg-white text-slate-900">
        <div class="brand-container py-14 lg:py-16">
            <div class="grid gap-12 lg:grid-cols-[1.05fr_1.35fr_.95fr] lg:items-start">
                <div>
                    <div class="flex items-center gap-4">
                        <img src="{{ $logoUrl }}" alt="{{ config('app.name', 'iTechBD Ltd') }}" class="h-16 w-auto object-contain">
                    </div>

                    <p class="mt-6 max-w-md text-base leading-9 text-slate-700">
                        {{ $footerBrandDescription }}
                    </p>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="{{ $footerFacebookUrl ?: '#' }}" target="_blank" rel="noopener noreferrer" class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-slate-900 transition hover:border-[#f15a24] hover:bg-[#f15a24] hover:text-white" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="{{ $footerLinkedinUrl ?: '#' }}" target="_blank" rel="noopener noreferrer" class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-slate-900 transition hover:border-[#f15a24] hover:bg-[#f15a24] hover:text-white" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="{{ $footerYoutubeUrl ?: '#' }}" target="_blank" rel="noopener noreferrer" class="grid h-11 w-11 place-items-center rounded-full border border-slate-300 text-slate-900 transition hover:border-[#f15a24] hover:bg-[#f15a24] hover:text-white" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>

                <div>
                    <h3 class="text-[2rem] font-black uppercase tracking-tight text-slate-950">Useful Links</h3>
                    <div class="mt-5 h-2 w-full max-w-[33rem] bg-[#ffbf1f]"></div>

                    <div class="mt-7 grid gap-x-8 gap-y-4 text-lg text-slate-800 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($footerUsefulLinks as $link)
                            <a href="{{ $link['url'] }}" class="transition hover:text-[#f15a24]">{{ $link['label'] }}</a>
                        @endforeach
                    </div>

                    <div class="mt-10 rounded-[1.6rem] border border-slate-200 bg-[linear-gradient(135deg,rgba(41,43,134,.03),rgba(241,90,36,.05))] px-5 py-6">
                        <div class="grid gap-5 sm:grid-cols-3">
                            @foreach($footerAccreditations as $item)
                                <div class="text-center sm:text-left">
                                    <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500">{{ $item['title'] }}</div>
                                    <div class="mt-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm font-extrabold text-slate-900 shadow-sm">
                                        {{ $item['value'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-[2rem] font-black uppercase tracking-tight text-slate-950">{{ $footerContactTitle }}</h3>

                    <div class="mt-6 grid gap-5 text-lg leading-8 text-slate-800">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot mt-2 text-[#292b86]"></i>
                            <div>
                                <div class="font-black text-slate-950">{{ $footerLocationLabel }}:</div>
                                <div>{{ $siteAddress }}</div>
                            </div>
                        </div>

                        <a href="{{ $sitePhoneTel }}" class="flex items-start gap-3 transition hover:text-[#f15a24]">
                            <i class="fa-solid fa-phone mt-2 text-[#292b86]"></i>
                            <div>
                                <div class="font-black text-slate-950">{{ $footerPhoneLabel }}:</div>
                                <div>{{ $sitePhone }}</div>
                            </div>
                        </a>

                        <a href="{{ $siteEmailMailto }}" class="flex items-start gap-3 transition hover:text-[#f15a24]">
                            <i class="fa-solid fa-envelope mt-2 text-[#292b86]"></i>
                            <div>
                                <div class="font-black text-slate-950">{{ $footerEmailLabel }}:</div>
                                <div>{{ $siteEmail }}</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex flex-col gap-4 border-t border-slate-200 pt-6 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'iTechBD Ltd') }}. {{ $frontendSettings['footer_copyright'] ?? 'All rights reserved.' }}</p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('privacy') }}" class="transition hover:text-[#f15a24]">Privacy</a>
                    <a href="{{ route('terms') }}" class="transition hover:text-[#f15a24]">Terms</a>
                </div>
            </div>
        </div>
    </footer>

    <footer class="hidden mt-16 bg-[#2d211d] text-white">
        <div class="brand-container py-12 lg:py-16">
            <div class="grid gap-10 lg:grid-cols-[1.25fr_.8fr_.8fr_1fr]">
                <div>
                    <img src="{{ $logoUrl }}" alt="{{ config('app.name', 'iTechBD Ltd') }}" class="h-14 w-auto rounded-xl bg-white px-3 py-2">
                    <p class="mt-5 max-w-sm text-sm leading-7 text-white/70">
                        {{ $frontendSettings['footer_brand_description'] ?? 'Develop software and professional skills with practical training, mentor support, and career-focused courses.' }}
                    </p>
                    <div class="mt-5 flex gap-3">
                        <a href="{{ $footerFacebookUrl ?: '#' }}" target="_blank" rel="noopener noreferrer" class="grid h-10 w-10 place-items-center rounded-full bg-white/10 text-white transition hover:bg-[#f15a24]" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="{{ $footerLinkedinUrl ?: '#' }}" target="_blank" rel="noopener noreferrer" class="grid h-10 w-10 place-items-center rounded-full bg-white/10 text-white transition hover:bg-[#f15a24]" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="{{ $footerYoutubeUrl ?: '#' }}" target="_blank" rel="noopener noreferrer" class="grid h-10 w-10 place-items-center rounded-full bg-white/10 text-white transition hover:bg-[#f15a24]" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-black uppercase tracking-[0.18em] text-white/90">Quick Links</h3>
                    <div class="mt-5 grid gap-3 text-sm text-white/70">
                        @foreach($navItems as $item)
                            <a href="{{ $item['url'] }}" class="hover:text-[#f15a24]">{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-black uppercase tracking-[0.18em] text-white/90">Learning</h3>
                    <div class="mt-5 grid gap-3 text-sm text-white/70">
                        <a href="{{ route('courses') }}" class="hover:text-[#f15a24]">Popular Courses</a>
                        <a href="{{ route('mentors') }}" class="hover:text-[#f15a24]">Expert Mentors</a>
                        <a href="{{ route('reviews') }}" class="hover:text-[#f15a24]">Student Reviews</a>
                        <a href="{{ route('news') }}" class="hover:text-[#f15a24]">News & Updates</a>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-black uppercase tracking-[0.18em] text-white/90">Contact</h3>
                    <div class="mt-5 grid gap-4 text-sm text-white/75">
                        <a href="{{ $sitePhoneTel }}" class="flex items-start gap-3 hover:text-[#f15a24]"><i class="fa-solid fa-phone mt-1 text-[#f15a24]"></i><span>{{ $sitePhone }}</span></a>
                        <a href="{{ $siteEmailMailto }}" class="flex items-start gap-3 hover:text-[#f15a24]"><i class="fa-solid fa-envelope mt-1 text-[#f15a24]"></i><span>{{ $siteEmail }}</span></a>
                        <div class="flex items-start gap-3"><i class="fa-solid fa-location-dot mt-1 text-[#f15a24]"></i><span>{{ $siteAddress }}</span></div>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3 border-t border-white/10 pt-6 text-xs text-white/60 sm:flex-row sm:items-center sm:justify-between">
                <p>© {{ date('Y') }} {{ config('app.name', 'iTechBD Ltd') }}. {{ $frontendSettings['footer_copyright'] ?? 'All rights reserved.' }}</p>
                <div class="flex gap-4">
                    <a href="{{ route('privacy') }}" class="hover:text-[#f15a24]">Privacy</a>
                    <a href="{{ route('terms') }}" class="hover:text-[#f15a24]">Terms</a>
                </div>
            </div>
        </div>
    </footer>

    @guest
        <x-auth.login-modal />
        <x-auth.register-modal />
        <x-auth.forgot-password-modal />
        <x-auth.reset-success-modal />
        @if (session('status') === 'verified' || session('status') === 'already-verified')
            <x-auth.verification-status-modal />
        @endif
    @endguest

    <script>
        (function () {
            document.documentElement.classList.remove('dark');
            document.documentElement.dataset.theme = 'light';
            document.documentElement.style.colorScheme = 'light';

            var menuButton = document.getElementById('siteMobileMenuButton');
            var menu = document.getElementById('siteMobileMenu');
            if (menuButton && menu) {
                menuButton.addEventListener('click', function () {
                    var isHidden = menu.classList.toggle('hidden');
                    menuButton.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
                });
            }

            var revealEls = Array.prototype.slice.call(document.querySelectorAll('.reveal'));
            if (!('IntersectionObserver' in window)) {
                revealEls.forEach(function (el) { el.classList.add('is-visible'); });
                return;
            }

            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

            revealEls.forEach(function (el) { observer.observe(el); });
        })();
    </script>

    @stack('scripts')
</body>
</html>
