<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 leading-tight">Add Batch</h2>
            <p class="mt-1 text-sm text-slate-500">Course: <span class="font-semibold">{{ $course->title }}</span></p>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('dashboard.batches.store.course', $course) }}" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700">Name</label>
                <input name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-lg border-slate-300" required />
                @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Start date</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" class="mt-1 w-full rounded-lg border-slate-300" required />
                    @error('start_date') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">End date</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" class="mt-1 w-full rounded-lg border-slate-300" required />
                    @error('end_date') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Class days</label>
                @php($days = ['Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday'])
                <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach($days as $day)
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="class_days[]" value="{{ $day }}" class="rounded border-slate-300" @checked(in_array($day, old('class_days', []), true)) />
                            <span>{{ $day }}</span>
                        </label>
                    @endforeach
                </div>
                @error('class_days') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                @error('class_days.*') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Class time</label>
                <input name="class_time" value="{{ old('class_time') }}" placeholder="e.g. 8:00 PM" class="mt-1 w-full rounded-lg border-slate-300" required />
                @error('class_time') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Live class link (optional)</label>
                <input name="live_class_link" value="{{ old('live_class_link') }}" class="mt-1 w-full rounded-lg border-slate-300" />
                @error('live_class_link') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Status</label>
                <select name="status" class="mt-1 w-full rounded-lg border-slate-300" required>
                    <option value="upcoming" @selected(old('status', 'upcoming') === 'upcoming')>Upcoming</option>
                    <option value="running" @selected(old('status') === 'running')>Running</option>
                    <option value="completed" @selected(old('status') === 'completed')>Completed</option>
                </select>
                @error('status') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="/dashboard/batches" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create</button>
            </div>
        </form>
    </div>
</x-app-layout>
