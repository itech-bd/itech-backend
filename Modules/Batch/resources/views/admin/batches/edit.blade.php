<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 leading-tight">Edit Batch</h2>
            <p class="mt-1 text-sm text-slate-500">Course: <span class="font-semibold">{{ $course->title }}</span></p>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('dashboard.batches.update', $batch) }}" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700">Name</label>
                <input name="name" value="{{ old('name', $batch->name) }}" class="mt-1 w-full rounded-lg border-slate-300" required />
                @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Start date</label>
                    <input type="date" name="start_date" value="{{ old('start_date', optional($batch->start_date)->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-300" required />
                    @error('start_date') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">End date</label>
                    <input type="date" name="end_date" value="{{ old('end_date', optional($batch->end_date)->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-300" required />
                    @error('end_date') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Class days</label>
                @php($days = ['Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday'])
                @php($selected = old('class_days', (array) $batch->class_days))
                <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach($days as $day)
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="class_days[]" value="{{ $day }}" class="rounded border-slate-300" @checked(in_array($day, $selected, true)) />
                            <span>{{ $day }}</span>
                        </label>
                    @endforeach
                </div>
                @error('class_days') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                @error('class_days.*') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Class time</label>
                <input name="class_time" value="{{ old('class_time', $batch->class_time) }}" class="mt-1 w-full rounded-lg border-slate-300" required />
                @error('class_time') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Live class link (optional)</label>
                <input name="live_class_link" value="{{ old('live_class_link', $batch->live_class_link) }}" class="mt-1 w-full rounded-lg border-slate-300" />
                @error('live_class_link') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Status</label>
                <select name="status" class="mt-1 w-full rounded-lg border-slate-300" required>
                    <option value="upcoming" @selected(old('status', $batch->status) === 'upcoming')>Upcoming</option>
                    <option value="running" @selected(old('status', $batch->status) === 'running')>Running</option>
                    <option value="completed" @selected(old('status', $batch->status) === 'completed')>Completed</option>
                </select>
                @error('status') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="/dashboard/batches/{{ $batch->getRouteKey() }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save</button>
            </div>
        </form>
    </div>
</x-app-layout>
