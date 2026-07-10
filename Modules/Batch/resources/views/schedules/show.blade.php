<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 leading-tight">{{ $classSchedule->topic }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $classSchedule->class_date?->format('d M Y') }} • Batch: <span class="font-semibold">{{ $batch->name }}</span></p>
            </div>

            <div class="flex items-center gap-2">
                <a href="/dashboard/batches/{{ $batch->getRouteKey() }}/schedules" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back</a>
                @can('editClassSchedule')
                    <a href="/dashboard/batches/{{ $batch->getRouteKey() }}/schedules/{{ $classSchedule->getRouteKey() }}/edit" class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100">Edit</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-4">
        <div>
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Live class link</div>
            <div class="mt-1 text-sm">
                @if($batch->live_class_link)
                    <a class="text-indigo-600 hover:text-indigo-500" href="{{ $batch->live_class_link }}" target="_blank" rel="noreferrer">{{ $batch->live_class_link }}</a>
                @else
                    <span class="text-slate-500">-</span>
                @endif
            </div>
        </div>

        <div>
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Recorded video link</div>
            <div class="mt-1 text-sm">
                @if($classSchedule->recorded_video_link)
                    <a class="text-indigo-600 hover:text-indigo-500" href="{{ $classSchedule->recorded_video_link }}" target="_blank" rel="noreferrer">{{ $classSchedule->recorded_video_link }}</a>
                @else
                    <span class="text-slate-500">-</span>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
