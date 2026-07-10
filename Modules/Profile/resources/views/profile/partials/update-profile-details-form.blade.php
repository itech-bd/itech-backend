<section>
    @php
        $routePrefix = $routePrefix ?? 'profile';
        $routeParams = $routeParams ?? [];
    @endphp

    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Personal Details') }}</h2>
        <p class="mt-1 text-sm text-gray-600">Update your personal profile details.</p>
    </header>

    <form method="post" action="{{ route($routePrefix.'.details.update', $routeParams) }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        @php
            $profile = $user->profile;
        @endphp

        <div>
            <x-input-label for="gender" :value="__('Gender')" />
            <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">{{ __('Select') }}</option>
                <option value="male" @selected(old('gender', $profile?->gender) === 'male')>Male</option>
                <option value="female" @selected(old('gender', $profile?->gender) === 'female')>Female</option>
                <option value="other" @selected(old('gender', $profile?->gender) === 'other')>Other</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('gender')" />
        </div>

        <div>
            <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
            <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth', optional($profile?->date_of_birth)->format('Y-m-d'))" />
            <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
        </div>

        <div>
            <x-input-label for="mobile_number" :value="__('Mobile Number')" />
            <x-text-input id="mobile_number" name="mobile_number" type="text" class="mt-1 block w-full" :value="old('mobile_number', $profile?->mobile_number)" />
            <x-input-error class="mt-2" :messages="$errors->get('mobile_number')" />
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="father_name" :value="__('Father Name')" />
                <x-text-input id="father_name" name="father_name" type="text" class="mt-1 block w-full" :value="old('father_name', $profile?->father_name)" />
                <x-input-error class="mt-2" :messages="$errors->get('father_name')" />
            </div>
            <div>
                <x-input-label for="father_mobile" :value="__('Father Mobile')" />
                <x-text-input id="father_mobile" name="father_mobile" type="text" class="mt-1 block w-full" :value="old('father_mobile', $profile?->father_mobile)" />
                <x-input-error class="mt-2" :messages="$errors->get('father_mobile')" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="mother_name" :value="__('Mother Name')" />
                <x-text-input id="mother_name" name="mother_name" type="text" class="mt-1 block w-full" :value="old('mother_name', $profile?->mother_name)" />
                <x-input-error class="mt-2" :messages="$errors->get('mother_name')" />
            </div>
            <div>
                <x-input-label for="mother_mobile" :value="__('Mother Mobile')" />
                <x-text-input id="mother_mobile" name="mother_mobile" type="text" class="mt-1 block w-full" :value="old('mother_mobile', $profile?->mother_mobile)" />
                <x-input-error class="mt-2" :messages="$errors->get('mother_mobile')" />
            </div>
        </div>

        <div>
            <x-input-label for="bio" :value="__('Bio')" />
            <textarea id="bio" name="bio" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('bio', $profile?->bio) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-details-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
