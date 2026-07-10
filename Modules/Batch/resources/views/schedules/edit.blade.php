<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 leading-tight">Edit Class</h2>
            <p class="mt-1 text-sm text-slate-500">Batch: <span class="font-semibold">{{ $batch->name }}</span></p>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('dashboard.batches.schedules.update', [$batch, $classSchedule]) }}" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-slate-700">Class date</label>
                <input type="date" name="class_date" value="{{ old('class_date', optional($classSchedule->class_date)->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-300" required />
                @error('class_date') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Topic</label>
                <input name="topic" value="{{ old('topic', $classSchedule->topic) }}" class="mt-1 w-full rounded-lg border-slate-300" required />
                @error('topic') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Recorded video link (optional)</label>
                <input name="recorded_video_link" value="{{ old('recorded_video_link', $classSchedule->recorded_video_link) }}" class="mt-1 w-full rounded-lg border-slate-300" />
                @error('recorded_video_link') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="/dashboard/batches/{{ $batch->getRouteKey() }}/schedules/{{ $classSchedule->getRouteKey() }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save</button>
            </div>
        </form>
    </div>
</x-app-layout>
