<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        @php
            $faviconPath = $frontendSettings['site_favicon_path'] ?? null;
            $faviconUrl = $faviconPath ? asset('storage/' . ltrim((string) $faviconPath, '/')) : asset('favicon.ico');
        @endphp
        <link rel="icon" href="{{ $faviconUrl }}" sizes="any">
        <link rel="shortcut icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
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
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
