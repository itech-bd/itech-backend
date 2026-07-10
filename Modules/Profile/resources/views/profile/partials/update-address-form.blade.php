<section>
    @php
        $routePrefix = $routePrefix ?? 'profile';
        $routeParams = $routeParams ?? [];
    @endphp

    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Current Address') }}</h2>
        <p class="mt-1 text-sm text-gray-600">Maintain your current address (one per user).</p>
    </header>

    <form method="post" action="{{ route($routePrefix.'.address.update', $routeParams) }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        @php
            $address = $user->address;
        @endphp

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="house_number" :value="__('House Number')" />
                <x-text-input id="house_number" name="house_number" type="text" class="mt-1 block w-full" :value="old('house_number', $address?->house_number)" />
                <x-input-error class="mt-2" :messages="$errors->get('house_number')" />
            </div>

            <div>
                <x-input-label for="street" :value="__('Street')" />
                <x-text-input id="street" name="street" type="text" class="mt-1 block w-full" :value="old('street', $address?->street)" />
                <x-input-error class="mt-2" :messages="$errors->get('street')" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="city" :value="__('City')" />
                <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $address?->city)" required />
                <x-input-error class="mt-2" :messages="$errors->get('city')" />
            </div>

            <div>
                <x-input-label for="post_office" :value="__('Post Office')" />
                <x-text-input id="post_office" name="post_office" type="text" class="mt-1 block w-full" :value="old('post_office', $address?->post_office)" />
                <x-input-error class="mt-2" :messages="$errors->get('post_office')" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="zip_code" :value="__('ZIP Code')" />
                <x-text-input id="zip_code" name="zip_code" type="text" class="mt-1 block w-full" :value="old('zip_code', $address?->zip_code)" />
                <x-input-error class="mt-2" :messages="$errors->get('zip_code')" />
            </div>

            <div>
                <x-input-label for="country" :value="__('Country')" />
                <x-text-input id="country" name="country" type="text" class="mt-1 block w-full" :value="old('country', $address?->country ?? 'Bangladesh')" required />
                <x-input-error class="mt-2" :messages="$errors->get('country')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'address-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
