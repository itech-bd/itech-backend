<section>
    @php
        $routePrefix = $routePrefix ?? 'profile';
        $routeParams = $routeParams ?? [];
    @endphp

    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Education') }}</h2>
        <p class="mt-1 text-sm text-gray-600">Add and manage your education history.</p>
    </header>

    <form method="post" action="{{ route($routePrefix.'.educations.store', $routeParams) }}" class="mt-6 space-y-4">
        @csrf

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="degree_name" :value="__('Degree Name')" />
                <x-text-input id="degree_name" name="degree_name" type="text" class="mt-1 block w-full" :value="old('degree_name')" required />
                <x-input-error class="mt-2" :messages="$errors->get('degree_name')" />
            </div>

            <div>
                <x-input-label for="institute_name" :value="__('Institute Name')" />
                <x-text-input id="institute_name" name="institute_name" type="text" class="mt-1 block w-full" :value="old('institute_name')" required />
                <x-input-error class="mt-2" :messages="$errors->get('institute_name')" />
            </div>
        </div>

        <div>
            <x-input-label for="board_or_university" :value="__('Board/University')" />
            <x-text-input id="board_or_university" name="board_or_university" type="text" class="mt-1 block w-full" :value="old('board_or_university')" />
            <x-input-error class="mt-2" :messages="$errors->get('board_or_university')" />
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <x-input-label for="start_year" :value="__('Start Year')" />
                <x-text-input id="start_year" name="start_year" type="number" class="mt-1 block w-full" :value="old('start_year')" />
                <x-input-error class="mt-2" :messages="$errors->get('start_year')" />
            </div>

            <div>
                <x-input-label for="end_year" :value="__('End Year')" />
                <x-text-input id="end_year" name="end_year" type="number" class="mt-1 block w-full" :value="old('end_year')" />
                <x-input-error class="mt-2" :messages="$errors->get('end_year')" />
            </div>

            <div>
                <x-input-label for="result_or_grade" :value="__('Result/Grade')" />
                <x-text-input id="result_or_grade" name="result_or_grade" type="text" class="mt-1 block w-full" :value="old('result_or_grade')" />
                <x-input-error class="mt-2" :messages="$errors->get('result_or_grade')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Add') }}</x-primary-button>
            @if (session('status') === 'education-added')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">{{ __('Added.') }}</p>
            @endif
        </div>
    </form>

    @php
        $educations = $user->educations->sortByDesc('start_year');
    @endphp

    <div class="mt-8 space-y-4">
        @forelse($educations as $education)
            <div class="rounded-lg border border-gray-200 p-4">
                <div class="flex items-start justify-end">
                    <form method="post" action="{{ route($routePrefix.'.educations.destroy', array_merge($routeParams, ['education' => $education])) }}" onsubmit="return confirm('Delete this education?');">
                        @csrf
                        @method('delete')
                        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700">Delete</button>
                    </form>
                </div>

                <form method="post" action="{{ route($routePrefix.'.educations.update', array_merge($routeParams, ['education' => $education])) }}" class="mt-3 space-y-3">
                    @csrf
                    @method('patch')

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label :for="'degree_name_'.$education->id" :value="__('Degree Name')" />
                            <x-text-input :id="'degree_name_'.$education->id" name="degree_name" type="text" class="mt-1 block w-full" :value="old('degree_name', $education->degree_name)" required />
                        </div>
                        <div>
                            <x-input-label :for="'institute_name_'.$education->id" :value="__('Institute Name')" />
                            <x-text-input :id="'institute_name_'.$education->id" name="institute_name" type="text" class="mt-1 block w-full" :value="old('institute_name', $education->institute_name)" required />
                        </div>
                    </div>

                    <div>
                        <x-input-label :for="'board_or_university_'.$education->id" :value="__('Board/University')" />
                        <x-text-input :id="'board_or_university_'.$education->id" name="board_or_university" type="text" class="mt-1 block w-full" :value="old('board_or_university', $education->board_or_university)" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <x-input-label :for="'start_year_'.$education->id" :value="__('Start Year')" />
                            <x-text-input :id="'start_year_'.$education->id" name="start_year" type="number" class="mt-1 block w-full" :value="old('start_year', $education->start_year)" />
                        </div>
                        <div>
                            <x-input-label :for="'end_year_'.$education->id" :value="__('End Year')" />
                            <x-text-input :id="'end_year_'.$education->id" name="end_year" type="number" class="mt-1 block w-full" :value="old('end_year', $education->end_year)" />
                        </div>
                        <div>
                            <x-input-label :for="'result_or_grade_'.$education->id" :value="__('Result/Grade')" />
                            <x-text-input :id="'result_or_grade_'.$education->id" name="result_or_grade" type="text" class="mt-1 block w-full" :value="old('result_or_grade', $education->result_or_grade)" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <x-primary-button>{{ __('Update') }}</x-primary-button>
                            @if (session('status') === 'education-updated')
                                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">{{ __('Updated.') }}</p>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-600">No education added yet.</p>
        @endforelse
    </div>
</section>
