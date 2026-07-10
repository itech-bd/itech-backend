<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Batch Details</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">{{ $batch->name }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Course: <span class="font-bold text-slate-800">{{ $batch->course?->title }}</span></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-panel.action-link href="{{ url('/dashboard/student/batches') }}" tone="secondary">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back
                </x-panel.action-link>
                @if($batch->live_class_link)
                    <x-panel.action-link href="{{ $batch->live_class_link }}" tone="orange" target="_blank">
                        <i class="fa-solid fa-video"></i>
                        Join Live Class
                    </x-panel.action-link>
                @endif
            </div>
        </div>
    </x-slot>

    @php
        $today = now()->startOfDay();
        $schedules = $batch->classSchedules;
        $nextClass = $schedules->first(fn ($schedule) => $schedule->class_date && $schedule->class_date->gte($today));
        $completedClasses = $schedules->filter(fn ($schedule) => $schedule->class_date && $schedule->class_date->lt($today))->values();
        $upcomingClasses = $schedules->filter(fn ($schedule) => $schedule->class_date && $schedule->class_date->gte($today))->values();
        $remainingClasses = $nextClass ? $upcomingClasses->skip(1)->values() : $upcomingClasses;
    @endphp

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <x-panel.stat-card label="Total Classes" :value="$batch->classSchedules->count()" hint="Published routine" icon="fa-solid fa-chalkboard" tone="blue" />
        <x-panel.stat-card label="Mentors" :value="$batch->mentors->count()" hint="Assigned instructors" icon="fa-solid fa-chalkboard-user" tone="orange" />
        <x-panel.stat-card label="Class Time" value="{{ $batch->class_time ?: 'Not set' }}" hint="Regular batch timing" icon="fa-regular fa-clock" tone="green" />
    </div>

    <div class="mb-6 overflow-hidden rounded-[30px] bg-gradient-to-r from-[#2E3192] via-[#4b43c7] to-[#F47B20] p-[1px] shadow-lg shadow-[#2E3192]/10">
        <div class="flex flex-col gap-4 rounded-[29px] bg-white/95 px-5 py-5 backdrop-blur sm:px-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <div class="inline-flex items-center gap-2 rounded-full bg-[#F47B20]/10 px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] text-[#C9570B]">
                    <i class="fa-solid fa-star"></i>
                    Recommended
                </div>
                <h3 class="mt-3 text-xl font-extrabold text-slate-950">Need the complete routine?</h3>
                <p class="mt-1 text-sm text-slate-500">Open the full schedule page to see every class in one focused timeline view.</p>
            </div>

            <a href="{{ route('dashboard.batches.schedules.index', $batch) }}" class="inline-flex shrink-0 items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-[#2E3192] to-[#F47B20] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#2E3192]/20 transition hover:scale-[1.02] hover:shadow-xl hover:shadow-[#F47B20]/20">
                <i class="fa-solid fa-calendar-days"></i>
                Open Full Schedule
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.2fr_.8fr]">
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-extrabold text-slate-950">Class Schedule</h3>
                    <p class="mt-1 text-sm text-slate-500">Next class first, then upcoming and completed sessions in a clearer order.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-panel.status-badge :status="$batch->status" />
                    <a href="{{ route('dashboard.batches.schedules.index', $batch) }}" class="inline-flex items-center gap-2 rounded-xl bg-[#2E3192]/8 px-4 py-2 text-sm font-extrabold text-[#2E3192] transition hover:bg-[#2E3192]/12">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        Full Schedule
                    </a>
                </div>
            </div>

            <div class="space-y-6">
                @if($schedules->isEmpty())
                    <x-panel.empty-state title="No schedule yet" message="Class schedule will be visible here after admin publishes it." icon="fa-regular fa-calendar" />
                @else
                    @if($nextClass)
                        @php
                            $nextDate = $nextClass->class_date;
                            $nextLabel = $nextDate->isSameDay($today)
                                ? 'Today'
                                : ($nextDate->isSameDay($today->copy()->addDay()) ? 'Tomorrow' : $nextDate->diffForHumans($today, ['parts' => 1]));
                        @endphp
                        <details class="group rounded-3xl border border-[#F47B20]/20 bg-gradient-to-br from-[#fff7f1] via-white to-[#eef2ff] p-5" open>
                            <summary class="list-none cursor-pointer">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="min-w-0">
                                    <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-[#F47B20]/10 px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] text-[#C9570B]">
                                        <i class="fa-regular fa-clock"></i>
                                        Next Class
                                    </div>
                                    <h4 class="text-xl font-extrabold text-slate-950">{{ $nextClass->topic }}</h4>
                                    <p class="mt-2 text-sm font-bold text-slate-700">{{ $nextDate?->format('l, d M Y') }}</p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $nextLabel }}
                                        @if($batch->class_time)
                                            | {{ $batch->class_time }}
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400 transition group-open:text-[#C9570B]">
                                        Expand
                                    </span>
                                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-slate-500 transition group-open:rotate-180 group-open:bg-[#F47B20]/10 group-open:text-[#C9570B]">
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </span>
                                </div>
                            </div>
                            </summary>

                            <div class="mt-4 grid grid-cols-1 gap-3 border-t border-[#F47B20]/10 pt-4 md:grid-cols-2">
                                <div class="rounded-2xl bg-white/80 p-4">
                                    <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Live Class</div>
                                    <div class="mt-2 text-sm">
                                        @if($batch->live_class_link)
                                            <a href="{{ $batch->live_class_link }}" target="_blank" class="inline-flex items-center gap-2 font-bold text-[#C9570B] hover:text-[#a94b07]">
                                                <i class="fa-solid fa-video"></i>
                                                Join live class
                                            </a>
                                        @else
                                            <span class="text-slate-500">Live class link not set yet.</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-white/80 p-4">
                                    <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Recording</div>
                                    <div class="mt-2 text-sm">
                                        @if($nextClass->recorded_video_link)
                                            <a href="{{ $nextClass->recorded_video_link }}" target="_blank" class="inline-flex items-center gap-2 font-bold text-[#2E3192] hover:text-[#252879]">
                                                <i class="fa-solid fa-play"></i>
                                                Watch recording
                                            </a>
                                        @else
                                            <span class="text-slate-500">Recording not available yet.</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </details>
                    @endif

                    <div>
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h4 class="text-sm font-extrabold uppercase tracking-[0.18em] text-slate-500">Upcoming Classes</h4>
                            <span class="rounded-full bg-[#2E3192]/10 px-3 py-1 text-xs font-extrabold text-[#2E3192]">{{ $upcomingClasses->count() }}</span>
                        </div>

                        <div class="space-y-3">
                            @forelse($remainingClasses as $classSchedule)
                                @php
                                    $scheduleDate = $classSchedule->class_date;
                                    $isToday = $scheduleDate && $scheduleDate->isSameDay($today);
                                    $isTomorrow = $scheduleDate && $scheduleDate->isSameDay($today->copy()->addDay());
                                @endphp
                                <details class="group rounded-2xl border border-slate-100 bg-white p-4 transition hover:border-[#2E3192]/30 hover:shadow-md">
                                    <summary class="list-none cursor-pointer">
                                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="flex items-start gap-4">
                                                <div class="w-16 shrink-0 rounded-2xl bg-[#2E3192]/10 px-3 py-2 text-center text-[#2E3192]">
                                                    <div class="text-xs font-extrabold uppercase">{{ $scheduleDate?->format('M') ?? '-' }}</div>
                                                    <div class="text-2xl font-extrabold leading-none">{{ $scheduleDate?->format('d') ?? '-' }}</div>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <h4 class="font-extrabold text-slate-950">{{ $classSchedule->topic }}</h4>
                                                        @if($isToday)
                                                            <span class="rounded-full bg-[#F47B20]/10 px-2.5 py-1 text-xs font-extrabold text-[#C9570B]">Today</span>
                                                        @elseif($isTomorrow)
                                                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-extrabold text-emerald-700">Tomorrow</span>
                                                        @else
                                                            <span class="rounded-full bg-[#2E3192]/10 px-2.5 py-1 text-xs font-extrabold text-[#2E3192]">{{ $scheduleDate?->diffForHumans($today, ['parts' => 1]) }}</span>
                                                        @endif
                                                    </div>
                                                    <p class="mt-1 text-sm text-slate-500">
                                                        {{ $scheduleDate?->format('l, d M Y') }}
                                                        @if($batch->class_time)
                                                            | {{ $batch->class_time }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400 transition group-open:text-[#2E3192]">Expand</span>
                                                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 transition group-open:rotate-180 group-open:bg-[#2E3192]/10 group-open:text-[#2E3192]">
                                                    <i class="fa-solid fa-chevron-down"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </summary>

                                    <div class="mt-4 grid grid-cols-1 gap-3 border-t border-slate-100 pt-4 md:grid-cols-2">
                                        <div class="rounded-2xl bg-slate-50 p-4">
                                            <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Live Class</div>
                                            <div class="mt-2 text-sm">
                                                @if($batch->live_class_link)
                                                    <a href="{{ $batch->live_class_link }}" target="_blank" class="inline-flex items-center gap-2 font-bold text-[#F47B20] hover:text-[#d96816]">
                                                        <i class="fa-solid fa-video"></i>
                                                        Join live class
                                                    </a>
                                                @else
                                                    <span class="text-slate-500">Live class link not set yet.</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="rounded-2xl bg-slate-50 p-4">
                                            <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Recording</div>
                                            <div class="mt-2 text-sm">
                                                @if($classSchedule->recorded_video_link)
                                                    <a href="{{ $classSchedule->recorded_video_link }}" target="_blank" class="inline-flex items-center gap-2 font-bold text-[#2E3192] hover:text-[#252879]">
                                                        <i class="fa-solid fa-play"></i>
                                                        Watch recording
                                                    </a>
                                                @else
                                                    <span class="text-slate-500">Recording not available yet.</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </details>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-sm text-slate-500">
                                    No more upcoming classes right now.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h4 class="text-sm font-extrabold uppercase tracking-[0.18em] text-slate-500">Completed Classes</h4>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-extrabold text-slate-600">{{ $completedClasses->count() }}</span>
                        </div>

                        <div class="space-y-3">
                            @forelse($completedClasses->reverse()->values() as $classSchedule)
                                <details class="group rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <summary class="list-none cursor-pointer">
                                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="flex items-start gap-4">
                                                <div class="w-16 shrink-0 rounded-2xl bg-slate-200 px-3 py-2 text-center text-slate-600">
                                                    <div class="text-xs font-extrabold uppercase">{{ $classSchedule->class_date?->format('M') ?? '-' }}</div>
                                                    <div class="text-2xl font-extrabold leading-none">{{ $classSchedule->class_date?->format('d') ?? '-' }}</div>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <h4 class="font-extrabold text-slate-900">{{ $classSchedule->topic }}</h4>
                                                        <span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-extrabold text-slate-600">Completed</span>
                                                    </div>
                                                    <p class="mt-1 text-sm text-slate-500">
                                                        {{ $classSchedule->class_date?->format('l, d M Y') }}
                                                        @if($batch->class_time)
                                                            | {{ $batch->class_time }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400 transition group-open:text-slate-600">Expand</span>
                                                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-slate-500 transition group-open:rotate-180 group-open:bg-slate-200 group-open:text-slate-700">
                                                    <i class="fa-solid fa-chevron-down"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </summary>

                                    <div class="mt-4 grid grid-cols-1 gap-3 border-t border-slate-200 pt-4 md:grid-cols-2">
                                        <div class="rounded-2xl bg-white p-4">
                                            <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Live Class</div>
                                            <div class="mt-2 text-sm">
                                                @if($batch->live_class_link)
                                                    <a href="{{ $batch->live_class_link }}" target="_blank" class="inline-flex items-center gap-2 font-bold text-[#F47B20] hover:text-[#d96816]">
                                                        <i class="fa-solid fa-video"></i>
                                                        Open live class link
                                                    </a>
                                                @else
                                                    <span class="text-slate-500">Live class link not set yet.</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="rounded-2xl bg-white p-4">
                                            <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Recording</div>
                                            <div class="mt-2 text-sm">
                                                @if($classSchedule->recorded_video_link)
                                                    <a href="{{ $classSchedule->recorded_video_link }}" target="_blank" class="inline-flex items-center gap-2 font-bold text-[#2E3192] hover:text-[#252879]">
                                                        <i class="fa-solid fa-play"></i>
                                                        Watch recording
                                                    </a>
                                                @else
                                                    <span class="text-slate-500">Recording not available yet.</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </details>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-sm text-slate-500">
                                    Completed classes will appear here after sessions are done.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                <h3 class="text-lg font-extrabold text-slate-950">Batch Information</h3>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-start justify-between gap-4 rounded-2xl bg-slate-50 p-4">
                        <dt class="font-bold text-slate-500">Start Date</dt>
                        <dd class="text-right font-extrabold text-slate-950">{{ $batch->start_date?->format('d M Y') ?? '-' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4 rounded-2xl bg-slate-50 p-4">
                        <dt class="font-bold text-slate-500">End Date</dt>
                        <dd class="text-right font-extrabold text-slate-950">{{ $batch->end_date?->format('d M Y') ?? '-' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-4 rounded-2xl bg-slate-50 p-4">
                        <dt class="font-bold text-slate-500">Class Days</dt>
                        <dd class="text-right font-extrabold text-slate-950">{{ implode(', ', (array) ($batch->class_days ?? [])) ?: '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                <h3 class="text-lg font-extrabold text-slate-950">Assigned Mentors</h3>
                <div class="mt-4 space-y-3">
                    @forelse($batch->mentors as $mentor)
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-100 p-4">
                            <x-avatar :user="$mentor" />
                            <div class="min-w-0">
                                <div class="truncate text-sm font-extrabold text-slate-950">{{ $mentor->name }}</div>
                                <div class="truncate text-xs text-slate-500">{{ $mentor->email }}</div>
                            </div>
                        </div>
                    @empty
                        <x-panel.empty-state title="No mentors assigned" message="Mentor information will appear after assignment." icon="fa-solid fa-chalkboard-user" />
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
