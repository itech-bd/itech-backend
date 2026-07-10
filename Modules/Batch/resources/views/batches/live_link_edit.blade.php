<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 leading-tight">Edit Live Class Link</h2>
            <p class="mt-1 text-sm text-slate-500">Batch: <span class="font-semibold">{{ $batch->name }}</span></p>
        </div>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('dashboard.batches.live_link.update', $batch) }}" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700">Live class link (optional)</label>
                <p class="mt-1 text-xs text-slate-500">This link is shared for all classes in this batch. Students can see it.</p>
                <input name="live_class_link" value="{{ old('live_class_link', $batch->live_class_link) }}" class="mt-2 w-full rounded-lg border-slate-300" placeholder="https://meet.google.com/..." />
                @error('live_class_link') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="/dashboard/batches/{{ $batch->getRouteKey() }}/schedules" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save</button>
            </div>
        </form>
    </div>
</x-app-layout>
