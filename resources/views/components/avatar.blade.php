@props([
    'user',
    'size' => 'h-8 w-8',
    'text' => 'text-xs',
    'class' => '',
])

@php
    $url = $user?->profile_image_url;
    $initials = $user?->initials ?? '';
    $name = $user?->name ?? 'User';
@endphp

@if ($url)
    <span class="relative inline-block align-middle {{ $class }}">
        <img
            src="{{ $url }}"
            alt="{{ $name }}"
            class="rounded-full object-cover {{ $size }}"
            loading="lazy"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';"
        />
        <span
            role="img"
            aria-label="{{ $name }}"
            style="display: none;"
            class="grid place-items-center rounded-full bg-indigo-600 text-white font-semibold {{ $size }} {{ $text }}"
        >
            {{ $initials }}
        </span>
    </span>
@else
    <span
        role="img"
        aria-label="{{ $name }}"
        class="grid place-items-center rounded-full bg-indigo-600 text-white font-semibold {{ $size }} {{ $text }} {{ $class }}"
    >
        {{ $initials }}
    </span>
@endif
