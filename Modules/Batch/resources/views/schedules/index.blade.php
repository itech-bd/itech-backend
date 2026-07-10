<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Batch Schedule</p>
                <h2 class="text-2xl font-extrabold tracking-tight text-slate-950">Class Schedule</h2>
                <p class="mt-1 text-sm text-slate-500">Batch: <span class="font-bold text-slate-800">{{ $batch->name }}</span></p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @can('addClassSchedule')
                    <a href="/dashboard/batches/{{ $batch->getRouteKey() }}/schedules/create" class="inline-flex items-center gap-2 rounded-2xl bg-[#2E3192] px-4 py-2.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-[#252879]">
                        <i class="fa-solid fa-plus"></i>
                        Add Class
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    @php
        $today = now()->startOfDay();
        $upcomingSchedules = $schedules->filter(fn ($schedule) => $schedule->class_date && $schedule->class_date->gte($today))->values();
        $completedSchedules = $schedules->filter(fn ($schedule) => $schedule->class_date && $schedule->class_date->lt($today))->values();
    @endphp

    @if (session('success'))
        <div class="mb-4 rounded-2xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-800 ring-1 ring-emerald-100">{{ session('success') }}</div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Total Classes</p>
            <div class="mt-3 text-3xl font-extrabold text-slate-950">{{ $schedules->count() }}</div>
            <p class="mt-2 text-sm text-slate-500">Published in this batch</p>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Upcoming</p>
            <div class="mt-3 text-3xl font-extrabold text-[#2E3192]">{{ $upcomingSchedules->count() }}</div>
            <p class="mt-2 text-sm text-slate-500">Classes still ahead</p>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Completed</p>
            <div class="mt-3 text-3xl font-extrabold text-slate-900">{{ $completedSchedules->count() }}</div>
            <p class="mt-2 text-sm text-slate-500">Sessions already finished</p>
        </div>
        <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Class Time</p>
            <div class="mt-3 text-lg font-extrabold text-slate-950">{{ $batch->class_time ?: 'Not set' }}</div>
            <p class="mt-2 text-sm text-slate-500">{{ implode(', ', (array) ($batch->class_days ?? [])) ?: 'Days not set' }}</p>
        </div>
    </div>

    <div class="mb-6 rounded-[32px] bg-gradient-to-br from-[#0f172a] via-[#1e1b4b] to-[#2E3192] p-6 text-white shadow-xl">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-extrabold uppercase tracking-[0.22em] text-white/90">
                    <i class="fa-regular fa-calendar"></i>
                    Next Live Session
                </div>

                @if($nextSchedule)
                    @php
                        $nextDate = $nextSchedule->class_date;
                        $relativeLabel = $nextDate?->isSameDay($today)
                            ? 'Today'
                            : ($nextDate?->isSameDay($today->copy()->addDay()) ? 'Tomorrow' : $nextDate?->diffForHumans($today, ['parts' => 1]));
                    @endphp
                    <h3 class="mt-4 text-2xl font-extrabold tracking-tight">{{ $nextSchedule->topic }}</h3>
                    <p class="mt-2 text-sm text-white/80">{{ $nextDate?->format('l, d M Y') }} @if($batch->class_time) | {{ $batch->class_time }} @endif</p>
                    <p class="mt-1 text-sm font-semibold text-[#F9B37B]">{{ $relativeLabel }}</p>
                @else
                    <h3 class="mt-4 text-2xl font-extrabold tracking-tight">No upcoming class yet</h3>
                    <p class="mt-2 text-sm text-white/80">Admin will see the next session here after publishing a class date.</p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @if($batch->live_class_link)
                    <a href="{{ $batch->live_class_link }}"
                       target="_blank"
                       rel="noreferrer"
                       class="inline-flex items-center gap-2 rounded-2xl bg-[#F47B20] px-5 py-3 text-sm font-extrabold text-white transition hover:bg-[#dc6c18]">
                        <i class="fa-solid fa-video"></i>
                        Join Live Class
                    </a>
                @else
                    <span class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-5 py-3 text-sm font-bold text-white/85">
                        <i class="fa-regular fa-circle-xmark"></i>
                        Live link not set
                    </span>
                @endif

                @can('updateLiveClassLink', $batch)
                    <a href="/dashboard/batches/{{ $batch->getRouteKey() }}/live-link"
                       class="inline-flex items-center gap-2 rounded-2xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-white/15">
                        <i class="fa-solid fa-pen"></i>
                        Edit Live Link
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="rounded-[32px] bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-extrabold text-slate-950">All Classes</h3>
                <p class="mt-1 text-sm text-slate-500">A cleaner timeline view of every scheduled class in this batch.</p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.18em] text-slate-600">
                <i class="fa-solid fa-layer-group"></i>
                {{ $schedules->count() }} Entries
            </div>
        </div>

        <div class="space-y-4">
            @forelse($schedules as $index => $classSchedule)
                @php
                    $scheduleDate = $classSchedule->class_date;
                    $isPast = $scheduleDate && $scheduleDate->lt($today);
                    $isToday = $scheduleDate && $scheduleDate->isSameDay($today);
                    $isTomorrow = $scheduleDate && $scheduleDate->isSameDay($today->copy()->addDay());
                    $statusLabel = $isToday ? 'Today' : ($isTomorrow ? 'Tomorrow' : ($isPast ? 'Completed' : 'Upcoming'));
                    $statusClasses = $isToday
                        ? 'bg-[#F47B20]/10 text-[#C9570B]'
                        : ($isTomorrow
                            ? 'bg-emerald-100 text-emerald-700'
                            : ($isPast ? 'bg-slate-200 text-slate-600' : 'bg-[#2E3192]/10 text-[#2E3192]'));
                @endphp
                <details class="group rounded-3xl border border-slate-200/80 bg-white p-4 transition hover:border-[#2E3192]/25 hover:shadow-md">
                    <summary class="list-none cursor-pointer">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex min-w-0 items-start gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl {{ $isPast ? 'bg-slate-100 text-slate-500' : 'bg-[#2E3192]/10 text-[#2E3192]' }} text-sm font-extrabold">
                                    {{ $index + 1 }}
                                </div>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="text-lg font-extrabold text-slate-950">{{ $classSchedule->topic }}</h4>
                                        <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $statusClasses }}">{{ $statusLabel }}</span>
                                    </div>

                                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500">
                                        <span class="inline-flex items-center gap-2">
                                            <i class="fa-regular fa-calendar"></i>
                                            {{ $scheduleDate?->format('d M Y') ?? 'Date not set' }}
                                        </span>
                                        <span class="inline-flex items-center gap-2">
                                            <i class="fa-regular fa-clock"></i>
                                            {{ $batch->class_time ?: 'Time not set' }}
                                        </span>
                                        <span class="inline-flex items-center gap-2">
                                            <i class="fa-regular fa-calendar-days"></i>
                                            {{ $scheduleDate?->format('l') ?? 'Day not set' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400 transition group-open:text-[#2E3192]">
                                    Expand
                                </span>
                                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 transition group-open:rotate-180 group-open:bg-[#2E3192]/10 group-open:text-[#2E3192]">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </span>
                            </div>
                        </div>
                    </summary>

                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <div class="grid grid-cols-1 gap-4 xl:grid-cols-[1fr_auto]">
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Live Class Link</div>
                                    <div class="mt-2 text-sm">
                                        @if($batch->live_class_link)
                                            <a href="{{ $batch->live_class_link }}" target="_blank" rel="noreferrer" class="inline-flex items-center gap-2 font-bold text-[#2E3192] hover:text-[#252879]">
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
                                            <a href="{{ $classSchedule->recorded_video_link }}" target="_blank" rel="noreferrer" class="inline-flex items-center gap-2 font-bold text-[#2E3192] hover:text-[#252879]">
                                                <i class="fa-solid fa-play"></i>
                                                Watch recording
                                            </a>
                                        @else
                                            <span class="text-slate-500">No recording uploaded yet.</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-start gap-2 xl:justify-end">
                                <a href="{{ route('dashboard.batches.schedules.show', [$batch, $classSchedule]) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-extrabold text-slate-700 transition hover:border-[#2E3192]/30 hover:text-[#2E3192]">
                                    <i class="fa-solid fa-eye"></i>
                                    Full View
                                </a>
                                @can('update', $classSchedule)
                                    <a href="{{ route('dashboard.batches.schedules.edit', [$batch, $classSchedule]) }}" class="inline-flex items-center gap-2 rounded-xl bg-amber-50 px-4 py-2.5 text-sm font-extrabold text-amber-800 transition hover:bg-amber-100">
                                        <i class="fa-solid fa-pen"></i>
                                        Edit
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </details>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-200">
                        <i class="fa-regular fa-calendar text-xl"></i>
                    </div>
                    <h4 class="mt-4 text-lg font-extrabold text-slate-900">No class schedule yet</h4>
                    <p class="mt-2 text-sm text-slate-500">Once classes are published, they will appear here in a cleaner timeline layout.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
