<section>
    @php
        $routePrefix = $routePrefix ?? 'profile';
        $routeParams = $routeParams ?? [];
        $showVerification = $showVerification ?? true;
    @endphp

    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    @if ($showVerification)
        <form id="send-verification" method="post" action="{{ route('verification.send', absolute: false) }}">
            @csrf
        </form>
    @endif

    <form method="post" action="{{ route($routePrefix.'.update', $routeParams) }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="profile_image" :value="__('Profile Image')" />

            <div class="mt-2 flex items-center gap-4">
                <x-avatar :user="$user" size="h-14 w-14" text="text-base" />
                <div class="min-w-0">
                    <div class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</div>
                    <div class="text-xs text-gray-500">JPG, PNG, WEBP (max 2MB)</div>
                </div>
            </div>

            <input
                id="profile_image"
                name="profile_image"
                type="file"
                accept="image/*"
                class="mt-3 block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-700"
            />
            <x-input-error class="mt-2" :messages="$errors->get('profile_image')" />

            @if($user->profile_image)
                <label class="mt-3 inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="remove_profile_image" value="1" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    <span>Remove current image</span>
                </label>
                <x-input-error class="mt-2" :messages="$errors->get('remove_profile_image')" />
            @endif
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($showVerification && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
