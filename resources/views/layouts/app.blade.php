<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-force-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'iTechBD Ltd') }}</title>
        @php
            $frontendSettings = $frontendSettings ?? [];
            $faviconPath = $frontendSettings['site_favicon_path'] ?? null;
            $faviconUrl = $faviconPath ? asset('storage/' . ltrim((string) $faviconPath, '/')) : asset('favicon.ico');
        @endphp
        <link rel="icon" href="{{ $faviconUrl }}" sizes="any">
        <link rel="shortcut icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
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
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        @if(Auth::check() && Auth::user()->hasRole('admin'))
            <meta name="wysiwyg-upload-url" content="{{ route('admin.wysiwyg.upload') }}">
            <meta name="tinymce-base-url" content="{{ asset('vendor/tinymce') }}">
            @if ($useHot)
                @vite(['resources/js/admin.js'])
            @elseif (is_array($manifest) && !empty($manifest['resources/js/admin.js']['file']))
                <script type="module" src="{{ asset('build/' . $manifest['resources/js/admin.js']['file']) }}"></script>
            @else
                @vite(['resources/js/admin.js'])
            @endif
        @endif

        @stack('styles')

        <style>
            [x-cloak] { display: none !important; }
            html { color-scheme: light; }
            body { background: #f8fafc; }
            .itech-scrollbar::-webkit-scrollbar { width: 8px; }
            .itech-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.22); border-radius: 999px; }
            .dataTables_wrapper { color: #334155; }
            .dataTables_wrapper .dataTables_filter input,
            .dataTables_wrapper .dataTables_length select {
                border: 1px solid #e2e8f0 !important;
                border-radius: .75rem !important;
                padding: .45rem .75rem !important;
                margin-left: .5rem !important;
                outline: none !important;
            }
            .dataTables_wrapper .dataTables_filter input:focus,
            .dataTables_wrapper .dataTables_length select:focus {
                border-color: #2E3192 !important;
                box-shadow: 0 0 0 3px rgba(46,49,146,.12) !important;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button.current,
            .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
                background: #2E3192 !important;
                border-color: #2E3192 !important;
                color: #fff !important;
                border-radius: .7rem !important;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
                background: #f1f5f9 !important;
                border-color: #e2e8f0 !important;
                color: #0f172a !important;
                border-radius: .7rem !important;
            }
            table.dataTable.no-footer { border-bottom: 0 !important; }
            table.dataTable thead th { border-bottom: 1px solid #e2e8f0 !important; }
        </style>
    </head>
    <body class="font-sans antialiased text-slate-900">
        @php
            $panelLabel = 'Student Panel';
            $panelTone = 'Learning Space';
            $user = auth()->user();

            if ($user && method_exists($user, 'hasRole')) {
                if ($user->hasRole('admin')) {
                    $panelLabel = 'Admin Panel';
                    $panelTone = 'Operations Center';
                } elseif ($user->hasRole('mentor')) {
                    $panelLabel = 'Mentor Panel';
                    $panelTone = 'Teaching Hub';
                }
            }

            $logoPath = $frontendSettings['site_logo_path'] ?? null;
            $logoUrl = $logoPath ? asset('storage/' . ltrim((string) $logoPath, '/')) : asset('brand/itechbd-logo.png');
        @endphp

        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(46,49,146,.10),transparent_35%),radial-gradient(circle_at_top_right,rgba(244,123,32,.12),transparent_32%),#f8fafc]">
            <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-50 lg:hidden" aria-hidden="true">
                <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="sidebarOpen = false"></div>
                <div class="absolute inset-y-0 left-0 w-[19rem] max-w-[85vw] overflow-y-auto bg-[#17194f] p-4 shadow-2xl itech-scrollbar" @click.stop>
                    <div class="flex items-center justify-between rounded-2xl bg-white/10 p-3 ring-1 ring-white/10">
                        <a href="/dashboard" class="flex min-w-0 items-center gap-3">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-white p-1 shadow-sm">
                                <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" class="max-h-9 max-w-full object-contain">
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-extrabold text-white">{{ config('app.name', 'iTechBD Ltd') }}</span>
                                <span class="block text-xs font-semibold text-white/65">{{ $panelLabel }}</span>
                            </span>
                        </a>
                        <button type="button" class="grid h-9 w-9 place-items-center rounded-xl text-white hover:bg-white/10" @click="sidebarOpen = false" aria-label="Close sidebar">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="mt-5">
                        @include('layouts.sidebar')
                    </div>
                </div>
            </div>

            <div class="flex min-h-screen">
                <aside class="hidden lg:sticky lg:top-0 lg:flex lg:h-screen lg:w-[19rem] lg:flex-col lg:self-start lg:bg-[#17194f] lg:text-white lg:shadow-2xl">
                    <div class="border-b border-white/10 p-5">
                        <a href="/dashboard" class="flex items-center gap-3 rounded-3xl bg-white p-3 shadow-sm">
                            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white">
                                <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" class="max-h-10 max-w-full object-contain">
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-extrabold text-[#2E3192]">{{ config('app.name', 'iTechBD Ltd') }}</span>
                                <span class="mt-0.5 inline-flex rounded-full bg-[#F47B20]/10 px-2 py-0.5 text-[11px] font-bold text-[#C9570B]">{{ $panelLabel }}</span>
                            </span>
                        </a>
                        <div class="mt-4 rounded-2xl bg-white/10 p-4 ring-1 ring-white/10">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-white/50">{{ $panelTone }}</p>
                            <p class="mt-1 text-sm font-semibold text-white">Welcome, {{ \Illuminate\Support\Str::limit(Auth::user()->name, 22) }}</p>
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4 itech-scrollbar">
                        @include('layouts.sidebar')
                    </div>
                    <div class="border-t border-white/10 p-4">
                        <a href="{{ route('home') }}" class="flex items-center justify-center gap-2 rounded-2xl bg-white/10 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/15">
                            <i class="fa-solid fa-globe"></i>
                            Visit Website
                        </a>
                    </div>
                </aside>

                <div class="flex min-w-0 flex-1 flex-col">
                    <header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/90 backdrop-blur-xl">
                        <div class="flex min-h-16 items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                            <div class="flex min-w-0 items-center gap-3">
                                <button type="button" class="grid h-11 w-11 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50 lg:hidden" @click="sidebarOpen = true" aria-label="Open sidebar">
                                    <i class="fa-solid fa-bars"></i>
                                </button>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-[#2E3192]/70">{{ $panelLabel }}</p>
                                    <p class="mt-1 truncate text-sm text-slate-500">{{ now()->format('l, d M Y') }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 sm:gap-3">
                                <a href="{{ route('home') }}" class="hidden rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm transition hover:border-[#2E3192]/30 hover:text-[#2E3192] sm:inline-flex">
                                    <i class="fa-solid fa-house mr-2"></i> Home
                                </a>
                                <x-dropdown align="right" width="56">
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-left shadow-sm transition hover:border-[#2E3192]/30 hover:shadow-md">
                                            <x-avatar :user="Auth::user()" />
                                            <span class="hidden min-w-0 sm:block">
                                                <span class="block max-w-40 truncate text-sm font-bold text-slate-900">{{ Auth::user()->name }}</span>
                                                <span class="block max-w-40 truncate text-xs text-slate-500">{{ Auth::user()->email }}</span>
                                            </span>
                                            <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <x-dropdown-link href="/profile">
                                            {{ __('Profile') }}
                                        </x-dropdown-link>

                                        <form method="POST" action="/logout">
                                            @csrf
                                            <x-dropdown-link href="/logout" onclick="event.preventDefault(); this.closest('form').submit();">
                                                {{ __('Log Out') }}
                                            </x-dropdown-link>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        </div>

                        @isset($header)
                            <div class="border-t border-slate-100 px-4 py-5 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        @endisset
                    </header>

                    <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                        @php
                            $globalSuccess = session('success');

                            if (! $globalSuccess && session('status') === 'password-updated') {
                                $globalSuccess = __('frontend.password_updated_success');
                            }
                        @endphp

                        @if ($globalSuccess)
                            <div class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
                                <i class="fa-solid fa-circle-check mt-0.5"></i>
                                <span>{{ $globalSuccess }}</span>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 shadow-sm">
                                <div class="font-bold">Please check the form again.</div>
                                <ul class="mt-2 list-inside list-disc space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (isset($slot))
                            {{ $slot }}
                        @else
                            @yield('content')
                        @endif
                    </main>
                </div>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
