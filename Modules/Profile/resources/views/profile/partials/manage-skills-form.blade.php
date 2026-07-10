<section>
    @php
        $routePrefix = $routePrefix ?? 'profile';
        $routeParams = $routeParams ?? [];
    @endphp

    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Skills') }}</h2>
        <p class="mt-1 text-sm text-gray-600">Add skills and set your proficiency level.</p>
    </header>

    <form method="post" action="{{ route($routePrefix.'.skills.store', $routeParams) }}" class="mt-6 space-y-4">
        @csrf

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <x-input-label for="skill_name" :value="__('Skill Name')" />
                <x-text-input id="skill_name" name="skill_name" type="text" class="mt-1 block w-full" :value="old('skill_name')" placeholder="e.g., Laravel, Communication" required />
                <x-input-error class="mt-2" :messages="$errors->get('skill_name')" />
            </div>

            <div>
                <x-input-label for="proficiency_level" :value="__('Level')" />
                <select id="proficiency_level" name="proficiency_level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                    <option value="beginner" @selected(old('proficiency_level') === 'beginner')>Beginner</option>
                    <option value="intermediate" @selected(old('proficiency_level') === 'intermediate')>Intermediate</option>
                    <option value="expert" @selected(old('proficiency_level') === 'expert')>Expert</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('proficiency_level')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Add') }}</x-primary-button>
            @if (session('status') === 'skill-added')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">{{ __('Added.') }}</p>
            @endif
        </div>
    </form>

    <div class="mt-8 space-y-4">
        @forelse($user->skills->sortBy('name') as $skill)
            @php
                $level = $skill->pivot?->proficiency_level;
            @endphp

            <div class="rounded-lg border border-gray-200 p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-gray-900">{{ $skill->name }}</div>
                        <div class="mt-1 text-xs text-gray-500">Current level: {{ ucfirst($level) }}</div>
                    </div>

                    <form method="post" action="{{ route($routePrefix.'.skills.destroy', array_merge($routeParams, ['skill' => $skill])) }}" onsubmit="return confirm('Remove this skill?');">
                        @csrf
                        @method('delete')
                        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700">Remove</button>
                    </form>
                </div>

                <form method="post" action="{{ route($routePrefix.'.skills.update', array_merge($routeParams, ['skill' => $skill])) }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                    @csrf
                    @method('patch')

                    <div class="flex-1">
                        <x-input-label :for="'proficiency_'.$skill->id" :value="__('Update Level')" />
                        <select id="{{ 'proficiency_'.$skill->id }}" name="proficiency_level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            <option value="beginner" @selected($level === 'beginner')>Beginner</option>
                            <option value="intermediate" @selected($level === 'intermediate')>Intermediate</option>
                            <option value="expert" @selected($level === 'expert')>Expert</option>
                        </select>
                    </div>

                    <div>
                        <x-primary-button>{{ __('Update') }}</x-primary-button>
                    </div>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-600">No skills added yet.</p>
        @endforelse
    </div>
</section>
