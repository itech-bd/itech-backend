<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 leading-tight">{{ $batch->name }}</h2>
                <p class="mt-1 text-sm text-slate-500">Course: <span class="font-semibold">{{ $batch->course?->title }}</span></p>
            </div>
            <div class="flex items-center gap-2">
                <a href="/dashboard/mentor/batches" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back</a>
                <a href="/dashboard/batches/{{ $batch->getRouteKey() }}/schedules" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Schedule</a>
            </div>
        </div>
    </x-slot>

    <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-200 px-6 py-4">
            <div class="text-sm font-semibold text-slate-900">Upcoming classes</div>
        </div>
        <div class="divide-y divide-slate-200">
            @forelse($batch->classSchedules as $classSchedule)
                <div class="px-6 py-4 flex items-center justify-between gap-4">
                    <div>
                        <div class="font-semibold text-slate-900">{{ $classSchedule->topic }}</div>
                        <div class="mt-1 text-xs text-slate-500">{{ $classSchedule->class_date?->format('d M Y') }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="/dashboard/batches/{{ $batch->getRouteKey() }}/schedules/{{ $classSchedule->getRouteKey() }}" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">View</a>
                        @can('editClassSchedule')
                            <a href="/dashboard/batches/{{ $batch->getRouteKey() }}/schedules/{{ $classSchedule->getRouteKey() }}/edit" class="rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100">Edit</a>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-sm text-slate-500">No schedule yet.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
