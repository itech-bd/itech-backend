<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Classroom</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">My Batches</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Your approved and pending batches with mentors, class count and timing.</p>
            </div>
            <x-panel.action-link href="{{ url('/dashboard/student/courses') }}" tone="secondary">
                <i class="fa-solid fa-graduation-cap"></i>
                My Courses
            </x-panel.action-link>
        </div>
    </x-slot>

    @if($batches->count())
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 xl:grid-cols-3">
            @foreach($batches as $batch)
                <article class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-[#2E3192]/10">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="mb-3 inline-flex rounded-full bg-[#F47B20]/10 px-3 py-1 text-xs font-extrabold text-[#C9570B]">
                                {{ ucfirst($batch->pivot->batch_type ?? 'online') }} Batch
                            </div>
                            <h3 class="line-clamp-2 text-xl font-extrabold leading-snug text-slate-950">{{ $batch->name }}</h3>
                            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">{{ $batch->course?->title }}</p>
                        </div>
                        <x-panel.status-badge :status="$batch->pivot->status ?? $batch->status" />
                    </div>

                    <div class="mt-5 grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-2xl bg-slate-50 p-3 ring-1 ring-slate-100">
                            <div class="text-lg font-extrabold text-[#2E3192]">{{ $batch->mentors_count }}</div>
                            <div class="mt-1 text-[11px] font-bold uppercase tracking-wide text-slate-500">Mentors</div>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3 ring-1 ring-slate-100">
                            <div class="text-lg font-extrabold text-[#F47B20]">{{ $batch->class_schedules_count }}</div>
                            <div class="mt-1 text-[11px] font-bold uppercase tracking-wide text-slate-500">Classes</div>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3 ring-1 ring-slate-100">
                            <div class="text-lg font-extrabold text-[#E5362C]">{{ $batch->start_date ? $batch->start_date->format('d') : '—' }}</div>
                            <div class="mt-1 text-[11px] font-bold uppercase tracking-wide text-slate-500">Start</div>
                        </div>
                    </div>

                    <div class="mt-5 space-y-2 text-sm text-slate-600">
                        <div class="flex items-center gap-2"><i class="fa-regular fa-calendar text-[#2E3192]"></i> {{ $batch->start_date?->format('d M Y') ?? 'Start date not set' }} - {{ $batch->end_date?->format('d M Y') ?? 'End date not set' }}</div>
                        <div class="flex items-center gap-2"><i class="fa-regular fa-clock text-[#F47B20]"></i> {{ $batch->class_time ?: 'Class time not set' }}</div>
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-3">
                        @if(($batch->pivot->status ?? null) === 'pending')
                            <span class="text-xs font-bold text-amber-700">Waiting for admin approval</span>
                        @else
                            <span class="text-xs font-bold text-emerald-700">Ready for learning</span>
                        @endif
                        <x-panel.action-link href="{{ url('/dashboard/student/batches/'.$batch->getRouteKey()) }}" tone="primary">
                            Open
                        </x-panel.action-link>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6 rounded-2xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200/70">
            {{ $batches->links() }}
        </div>
    @else
        <x-panel.empty-state title="No enrolled batch found" message="Your assigned batch will appear here after enrollment." icon="fa-solid fa-layer-group">
            <x-panel.action-link href="{{ route('courses') }}" tone="orange">Find Courses</x-panel.action-link>
        </x-panel.empty-state>
    @endif
</x-app-layout>
