<section>
    @php
        $routePrefix = $routePrefix ?? 'profile';
        $routeParams = $routeParams ?? [];
    @endphp

    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Experience') }}</h2>
        <p class="mt-1 text-sm text-gray-600">Add and manage your work experience.</p>
    </header>

    <form method="post" action="{{ route($routePrefix.'.experiences.store', $routeParams) }}" class="mt-6 space-y-4">
        @csrf

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="company_name" :value="__('Company Name')" />
                <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name')" required />
                <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
            </div>

            <div>
                <x-input-label for="job_title" :value="__('Job Title')" />
                <x-text-input id="job_title" name="job_title" type="text" class="mt-1 block w-full" :value="old('job_title')" required />
                <x-input-error class="mt-2" :messages="$errors->get('job_title')" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="start_date" :value="__('Start Date')" />
                <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date')" required />
                <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
            </div>

            <div>
                <x-input-label for="end_date" :value="__('End Date')" />
                <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date')" />
                <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
            </div>
        </div>

        <div>
            <x-input-label for="description" :value="__('Description')" />
            <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('description')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Add') }}</x-primary-button>
            @if (session('status') === 'experience-added')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">{{ __('Added.') }}</p>
            @endif
        </div>
    </form>

    @php
        $experiences = $user->experiences->sortByDesc('start_date');
    @endphp

    <div class="mt-8 space-y-4">
        @forelse($experiences as $experience)
            <div class="rounded-lg border border-gray-200 p-4">
                <div class="flex items-start justify-end">
                    <form method="post" action="{{ route($routePrefix.'.experiences.destroy', array_merge($routeParams, ['experience' => $experience])) }}" onsubmit="return confirm('Delete this experience?');">
                        @csrf
                        @method('delete')
                        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700">Delete</button>
                    </form>
                </div>

                <form method="post" action="{{ route($routePrefix.'.experiences.update', array_merge($routeParams, ['experience' => $experience])) }}" class="mt-3 space-y-3">
                    @csrf
                    @method('patch')

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label :for="'company_name_'.$experience->id" :value="__('Company Name')" />
                            <x-text-input :id="'company_name_'.$experience->id" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $experience->company_name)" required />
                        </div>
                        <div>
                            <x-input-label :for="'job_title_'.$experience->id" :value="__('Job Title')" />
                            <x-text-input :id="'job_title_'.$experience->id" name="job_title" type="text" class="mt-1 block w-full" :value="old('job_title', $experience->job_title)" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label :for="'start_date_'.$experience->id" :value="__('Start Date')" />
                            <x-text-input :id="'start_date_'.$experience->id" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date', optional($experience->start_date)->format('Y-m-d'))" required />
                        </div>
                        <div>
                            <x-input-label :for="'end_date_'.$experience->id" :value="__('End Date')" />
                            <x-text-input :id="'end_date_'.$experience->id" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date', optional($experience->end_date)->format('Y-m-d'))" />
                        </div>
                    </div>

                    <div>
                        <x-input-label :for="'description_'.$experience->id" :value="__('Description')" />
                        <textarea id="{{ 'description_'.$experience->id }}" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $experience->description) }}</textarea>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <x-primary-button>{{ __('Update') }}</x-primary-button>
                            @if (session('status') === 'experience-updated')
                                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">{{ __('Updated.') }}</p>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-600">No experience added yet.</p>
        @endforelse
    </div>
</section>
