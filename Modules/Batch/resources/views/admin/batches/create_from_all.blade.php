<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 leading-tight">Add New Batch</h2>
                <p class="mt-1 text-sm text-slate-500">Select a course to create a batch under it.</p>
            </div>

            <a href="/dashboard/batches"
               class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Back
            </a>
        </div>
    </x-slot>

    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
        <form method="POST" action="{{ route('dashboard.batches.create.redirect') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-slate-700">Course</label>
                <select name="course_id"
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select a course...</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected((string) old('course_id') === (string) $course->id)>
                            {{ $course->title }}{{ $course->status ? ' (' . $course->status . ')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('course_id')
                    <div class="mt-1 text-sm text-rose-600">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-2">
                <a href="/dashboard/batches"
                   class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Cancel
                </a>
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Continue
                </button>
            </div>
        </form>

        @if ($courses->isEmpty())
            <div class="rounded-lg bg-slate-50 p-4 text-sm text-slate-600 ring-1 ring-slate-200">
                No courses found.
            </div>
        @endif
    </div>
</x-app-layout>
