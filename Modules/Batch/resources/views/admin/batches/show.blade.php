<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 leading-tight">{{ $batch->name }}</h2>
                <p class="mt-1 text-sm text-slate-500">Course: <span class="font-semibold">{{ $course->title }}</span></p>
            </div>

            <div class="flex items-center gap-2">
                <a href="/dashboard/batches" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back</a>
                <a href="/dashboard/batches/{{ $batch->getRouteKey() }}/schedules" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Schedule</a>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 p-4 text-sm text-emerald-800 ring-1 ring-emerald-100">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dates</div>
                    <div class="mt-1 text-sm text-slate-900">{{ $batch->start_date?->format('d M Y') }} - {{ $batch->end_date?->format('d M Y') }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</div>
                    <div class="mt-1 text-sm text-slate-900">{{ ucfirst($batch->status) }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Class days</div>
                    <div class="mt-1 text-sm text-slate-900">{{ implode(', ', (array) $batch->class_days) }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Class time</div>
                    <div class="mt-1 text-sm text-slate-900">{{ $batch->class_time }}</div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-2">
                @can('assignMentorsToBatch')
                    <a href="/dashboard/batches/{{ $batch->getRouteKey() }}/mentors" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Assign mentors</a>
                @endcan
                @can('assignStudentsToBatch')
                    <a href="/dashboard/batches/{{ $batch->getRouteKey() }}/students" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Assign students</a>
                @endcan
                @can('editBatch')
                    <a href="/dashboard/batches/{{ $batch->getRouteKey() }}/edit" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100">Change Status</a>
                @endcan
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="border-b border-slate-200 px-6 py-4">
                    <div class="text-sm font-semibold text-slate-900">Mentors</div>
                </div>
                <div class="divide-y divide-slate-200">
                    @forelse($batch->mentors as $mentor)
                        <div class="px-6 py-3">
                            <div class="text-sm font-semibold text-slate-900">{{ $mentor->name }}</div>
                            <div class="text-xs text-slate-500">{{ $mentor->email }}</div>
                        </div>
                    @empty
                        <div class="px-6 py-6 text-sm text-slate-500">No mentors assigned.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="border-b border-slate-200 px-6 py-4">
                    <div class="text-sm font-semibold text-slate-900">Students</div>
                </div>
                <div class="divide-y divide-slate-200">
                    @forelse($batch->students as $student)
                        <div class="px-6 py-3">
                            <div class="text-sm font-semibold text-slate-900">{{ $student->name }}</div>
                            <div class="text-xs text-slate-500">{{ $student->email }}</div>
                        </div>
                    @empty
                        <div class="px-6 py-6 text-sm text-slate-500">No students enrolled.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
