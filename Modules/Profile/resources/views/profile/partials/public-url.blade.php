<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Public URL') }}</h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Set a public URL to share your profile. Anyone with the link can view it without login.') }}
        </p>
    </header>

    @php
        $profile = $user->profile;
        $publicUrl = $profile?->public_url;
        $publicLink = $publicUrl ? route('profile.public.show', ['public_url' => $publicUrl]) : null;
    @endphp

    <div class="mt-4 rounded-md bg-gray-50 p-4">
        <div class="text-sm text-gray-700">
            <div class="font-medium">{{ __('Your public link') }}</div>
            @if ($publicLink)
                <div class="mt-1 break-all">
                    <a href="{{ $publicLink }}" class="text-indigo-600 hover:underline" target="_blank" rel="noreferrer">
                        {{ $publicLink }}
                    </a>
                </div>
            @else
                <div class="mt-1 text-gray-600">
                    {{ __('Not set yet. Choose a public URL below.') }}
                </div>
            @endif
        </div>
    </div>

    <form method="post" action="{{ route('profile.public-url.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="public_url" :value="__('Public URL')" />
            <x-text-input
                id="public_url"
                name="public_url"
                type="text"
                class="mt-1 block w-full"
                :value="old('public_url', $publicUrl)"
                placeholder="e.g. john-doe"
                autocomplete="off"
            />
            <p class="mt-1 text-xs text-gray-500">
                {{ __('Allowed: lowercase letters, numbers, and hyphens. Leave empty to disable.') }}
            </p>
            <x-input-error class="mt-2" :messages="$errors->get('public_url')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'public-url-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
