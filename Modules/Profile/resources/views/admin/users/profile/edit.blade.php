<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit User Profile') }}
            </h2>
            <div class="text-sm text-gray-600">
                <span class="font-medium text-gray-900">{{ $user->name }}</span>
                <span class="text-gray-400">•</span>
                <span>{{ $user->email }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form', [
                        'routePrefix' => 'admin.users.profile',
                        'routeParams' => ['user' => $user],
                        'showVerification' => false,
                    ])
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-4xl">
                    @include('profile.partials.update-profile-details-form', [
                        'routePrefix' => 'admin.users.profile',
                        'routeParams' => ['user' => $user],
                    ])
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-4xl">
                    @include('profile.partials.update-address-form', [
                        'routePrefix' => 'admin.users.profile',
                        'routeParams' => ['user' => $user],
                    ])
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-5xl">
                    @include('profile.partials.manage-educations-form', [
                        'routePrefix' => 'admin.users.profile',
                        'routeParams' => ['user' => $user],
                    ])
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-5xl">
                    @include('profile.partials.manage-experiences-form', [
                        'routePrefix' => 'admin.users.profile',
                        'routeParams' => ['user' => $user],
                    ])
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-4xl">
                    @include('profile.partials.manage-skills-form', [
                        'routePrefix' => 'admin.users.profile',
                        'routeParams' => ['user' => $user],
                    ])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
