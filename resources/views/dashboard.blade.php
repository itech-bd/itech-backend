<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-[#2E3192]/10 px-3 py-1 text-xs font-extrabold uppercase tracking-[0.18em] text-[#2E3192]">
                    <i class="fa-solid fa-sparkles"></i>
                    Dashboard
                </div>
                <h2 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">
                    Welcome back, {{ Auth::user()->name }}
                </h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                    @if($isStudent)
                        Track your enrolled courses, upcoming classes, mentors, and payment updates from one clean workspace.
                    @elseif($isMentor)
                        Manage your assigned batches, class schedule, and student activity from your teaching hub.
                    @else
                        Monitor courses, batches, students, invoices, and daily operations from a single control center.
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach($quickLinks as $link)
                    <x-panel.action-link :href="$link['href']" tone="secondary">
                        <i class="{{ $link['icon'] }}"></i>
                        {{ $link['label'] }}
                    </x-panel.action-link>
                @endforeach
            </div>
        </div>
    </x-slot>

    @if($isStudent)
        <div class="mb-6 overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#2E3192] via-[#20236f] to-[#151748] p-6 text-white shadow-xl shadow-[#2E3192]/20 lg:p-8">
            <div class="grid gap-6 lg:grid-cols-[1.45fr_.55fr] lg:items-center">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-white/55">Student Learning Space</p>
                    <h3 class="mt-3 text-2xl font-extrabold leading-tight sm:text-4xl">Keep learning without confusion.</h3>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-white/75">
                        Your courses, batches, mentors, class schedule and invoices are organized so you can focus on learning instead of searching.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="/dashboard/student/courses" class="inline-flex items-center gap-2 rounded-2xl bg-[#F47B20] px-5 py-3 text-sm font-extrabold text-white shadow-lg shadow-black/10 transition hover:bg-[#e36d19]">
                            <i class="fa-solid fa-play"></i>
                            Continue Learning
                        </a>
                        <a href="/dashboard/student/batches" class="inline-flex items-center gap-2 rounded-2xl bg-white/10 px-5 py-3 text-sm font-extrabold text-white ring-1 ring-white/15 transition hover:bg-white/15">
                            <i class="fa-solid fa-calendar-check"></i>
                            View Schedule
                        </a>
                    </div>
                </div>
                <div class="rounded-[1.5rem] bg-white/10 p-5 ring-1 ring-white/15">
                    <div class="text-sm font-bold text-white/70">Learning Summary</div>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-white/10 p-4">
                            <div class="text-2xl font-extrabold">{{ $stats['courses'] ?? 0 }}</div>
                            <div class="mt-1 text-xs font-semibold text-white/60">Courses</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4">
                            <div class="text-2xl font-extrabold">{{ $stats['batches'] ?? 0 }}</div>
                            <div class="mt-1 text-xs font-semibold text-white/60">Batches</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($isAdmin)
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-panel.stat-card label="Total Courses" :value="$stats['courses'] ?? 0" hint="{{ ($stats['active_courses'] ?? 0).' active courses' }}" icon="fa-solid fa-book-open" tone="blue" />
            <x-panel.stat-card label="Total Batches" :value="$stats['batches'] ?? 0" hint="Running and archived batches" icon="fa-solid fa-calendar-days" tone="orange" />
            <x-panel.stat-card label="Students" :value="$stats['students'] ?? 0" hint="Registered student accounts" icon="fa-solid fa-user-graduate" tone="green" />
            <x-panel.stat-card label="Paid Revenue" value="BDT {{ number_format((float) ($stats['paid_revenue'] ?? 0), 0) }}" hint="{{ ($stats['pending_invoices'] ?? 0).' pending invoices' }}" icon="fa-solid fa-sack-dollar" tone="red" />
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[1.2fr_.8fr]">
            <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-950">Recent Invoices</h3>
                        <p class="mt-1 text-sm text-slate-500">Latest student orders and payment statuses.</p>
                    </div>
                    <a href="/dashboard/admin/invoices" class="text-sm font-bold text-[#2E3192] hover:underline">View all</a>
                </div>
                <div class="space-y-3">
                    @forelse($recentOrders as $order)
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <div class="min-w-0">
                                <div class="font-bold text-slate-950">#INV-{{ $order->id }} · {{ $order->user?->name ?? 'Unknown Student' }}</div>
                                <div class="mt-1 max-w-xl truncate text-sm text-slate-500">{{ $order->course?->title ?? 'Course unavailable' }}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <x-panel.status-badge :status="$order->status" />
                                <div class="text-sm font-extrabold text-slate-950">{{ $order->currency }} {{ number_format((float) $order->amount, 0) }}</div>
                            </div>
                        </div>
                    @empty
                        <x-panel.empty-state title="No invoices yet" message="Student invoice history will appear here." icon="fa-regular fa-file-lines" />
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-950">Latest Batches</h3>
                        <p class="mt-1 text-sm text-slate-500">Batch activity at a glance.</p>
                    </div>
                    <a href="/dashboard/batches" class="text-sm font-bold text-[#2E3192] hover:underline">Manage</a>
                </div>
                <div class="space-y-3">
                    @forelse($recentBatches as $batch)
                        <a href="/dashboard/batches/{{ $batch->getRouteKey() }}" class="block rounded-2xl border border-slate-100 bg-white p-4 transition hover:border-[#2E3192]/30 hover:shadow-md">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate font-bold text-slate-950">{{ $batch->name }}</div>
                                    <div class="mt-1 truncate text-sm text-slate-500">{{ $batch->course?->title }}</div>
                                </div>
                                <x-panel.status-badge :status="$batch->status" />
                            </div>
                            <div class="mt-3 grid grid-cols-3 gap-2 text-center text-xs font-bold text-slate-500">
                                <div class="rounded-xl bg-slate-50 px-2 py-2">{{ $batch->students_count }} Students</div>
                                <div class="rounded-xl bg-slate-50 px-2 py-2">{{ $batch->mentors_count }} Mentors</div>
                                <div class="rounded-xl bg-slate-50 px-2 py-2">{{ $batch->class_schedules_count }} Classes</div>
                            </div>
                        </a>
                    @empty
                        <x-panel.empty-state title="No batches found" message="Newly created batches will appear here." icon="fa-regular fa-calendar" />
                    @endforelse
                </div>
            </div>
        </div>
    @elseif($isStudent)
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-panel.stat-card label="My Courses" :value="$stats['courses'] ?? 0" hint="Courses you enrolled in" icon="fa-solid fa-graduation-cap" tone="blue" />
            <x-panel.stat-card label="My Batches" :value="$stats['batches'] ?? 0" hint="{{ ($stats['pending_batches'] ?? 0).' pending approval' }}" icon="fa-solid fa-layer-group" tone="orange" />
            <x-panel.stat-card label="Paid Invoices" :value="$stats['paid_invoices'] ?? 0" hint="Confirmed payments" icon="fa-solid fa-file-invoice-dollar" tone="green" />
            <x-panel.stat-card label="Total Paid" value="BDT {{ number_format((float) ($stats['paid_amount'] ?? 0), 0) }}" hint="Your learning investment" icon="fa-solid fa-wallet" tone="red" />
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[1.15fr_.85fr]">
            <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-950">Upcoming Classes</h3>
                        <p class="mt-1 text-sm text-slate-500">Your next approved batch classes.</p>
                    </div>
                    <a href="/dashboard/student/batches" class="text-sm font-bold text-[#2E3192] hover:underline">View batches</a>
                </div>
                <div class="space-y-3">
                    @forelse($upcomingSchedules as $schedule)
                        <div class="flex items-start gap-4 rounded-2xl border border-slate-100 bg-gradient-to-r from-slate-50 to-white p-4">
                            <div class="w-16 shrink-0 rounded-2xl bg-[#2E3192]/10 px-3 py-2 text-center text-[#2E3192]">
                                <div class="text-xs font-extrabold uppercase">{{ optional($schedule->class_date)->format('M') }}</div>
                                <div class="text-2xl font-extrabold leading-none">{{ optional($schedule->class_date)->format('d') }}</div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="font-extrabold text-slate-950">{{ $schedule->topic }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $schedule->batch?->name }} · {{ $schedule->batch?->course?->title }}</div>
                                @if($schedule->live_class_link)
                                    <a href="{{ $schedule->live_class_link }}" target="_blank" class="mt-3 inline-flex items-center gap-2 rounded-xl bg-[#F47B20] px-3 py-2 text-xs font-extrabold text-white hover:bg-[#d96816]">
                                        <i class="fa-solid fa-video"></i> Join Live Class
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <x-panel.empty-state title="No upcoming class" message="Upcoming class schedule will show here after your batch schedule is published." icon="fa-regular fa-calendar-check" />
                    @endforelse
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-950">My Batches</h3>
                            <p class="mt-1 text-sm text-slate-500">Recently enrolled batches.</p>
                        </div>
                        <a href="/dashboard/student/batches" class="text-sm font-bold text-[#2E3192] hover:underline">All</a>
                    </div>
                    <div class="space-y-3">
                        @forelse($recentBatches as $batch)
                            <a href="/dashboard/student/batches/{{ $batch->getRouteKey() }}" class="block rounded-2xl border border-slate-100 p-4 transition hover:border-[#2E3192]/30 hover:bg-slate-50">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="truncate font-bold text-slate-950">{{ $batch->name }}</div>
                                        <div class="mt-1 truncate text-sm text-slate-500">{{ $batch->course?->title }}</div>
                                    </div>
                                    <x-panel.status-badge :status="$batch->pivot->status ?? $batch->status" />
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold text-slate-500">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1">{{ $batch->class_schedules_count }} Classes</span>
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1">{{ $batch->mentors_count }} Mentors</span>
                                    @if($batch->class_time)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1">{{ $batch->class_time }}</span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <x-panel.empty-state title="No batch enrolled" message="Your enrolled batch list will appear here." icon="fa-regular fa-folder-open" />
                        @endforelse
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-950">Recent Invoices</h3>
                            <p class="mt-1 text-sm text-slate-500">Payment status overview.</p>
                        </div>
                        <a href="/dashboard/student/invoices" class="text-sm font-bold text-[#2E3192] hover:underline">All</a>
                    </div>
                    <div class="space-y-3">
                        @forelse($recentOrders as $order)
                            <a href="/dashboard/student/invoices/{{ $order->getRouteKey() }}" class="flex items-center justify-between gap-3 rounded-2xl border border-slate-100 p-4 transition hover:border-[#2E3192]/30 hover:bg-slate-50">
                                <div>
                                    <div class="font-bold text-slate-950">#INV-{{ $order->id }}</div>
                                    <div class="mt-1 text-sm text-slate-500">{{ $order->course?->title ?? 'Course' }}</div>
                                </div>
                                <div class="text-right">
                                    <x-panel.status-badge :status="$order->status" />
                                    <div class="mt-1 text-sm font-extrabold text-slate-950">{{ $order->currency }} {{ number_format((float) $order->amount, 0) }}</div>
                                </div>
                            </a>
                        @empty
                            <x-panel.empty-state title="No invoice found" message="Your invoices will appear after course enrollment." icon="fa-regular fa-file-lines" />
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @elseif($isMentor)
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-panel.stat-card label="My Batches" :value="$stats['batches'] ?? 0" hint="Assigned to you" icon="fa-solid fa-users-rectangle" tone="blue" />
            <x-panel.stat-card label="Students" :value="$stats['students'] ?? 0" hint="Approved learners" icon="fa-solid fa-user-graduate" tone="green" />
            <x-panel.stat-card label="Total Classes" :value="$stats['classes'] ?? 0" hint="Scheduled classes" icon="fa-solid fa-chalkboard" tone="orange" />
            <x-panel.stat-card label="Upcoming" :value="$stats['upcoming_classes'] ?? 0" hint="Classes from today" icon="fa-solid fa-calendar-check" tone="red" />
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[1.2fr_.8fr]">
            <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                <h3 class="text-lg font-extrabold text-slate-950">Upcoming Classes</h3>
                <div class="mt-4 space-y-3">
                    @forelse($upcomingSchedules as $schedule)
                        <div class="rounded-2xl border border-slate-100 p-4">
                            <div class="font-bold text-slate-950">{{ $schedule->topic }}</div>
                            <div class="mt-1 text-sm text-slate-500">{{ optional($schedule->class_date)->format('d M Y') }} · {{ $schedule->batch?->name }}</div>
                        </div>
                    @empty
                        <x-panel.empty-state title="No upcoming class" message="Assigned class schedule will appear here." icon="fa-regular fa-calendar" />
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                <h3 class="text-lg font-extrabold text-slate-950">Assigned Batches</h3>
                <div class="mt-4 space-y-3">
                    @forelse($recentBatches as $batch)
                        <a href="/dashboard/mentor/batches/{{ $batch->getRouteKey() }}" class="block rounded-2xl border border-slate-100 p-4 transition hover:border-[#2E3192]/30 hover:bg-slate-50">
                            <div class="font-bold text-slate-950">{{ $batch->name }}</div>
                            <div class="mt-1 text-sm text-slate-500">{{ $batch->course?->title }}</div>
                        </a>
                    @empty
                        <x-panel.empty-state title="No batch assigned" message="Your assigned batches will appear here." icon="fa-regular fa-folder-open" />
                    @endforelse
                </div>
            </div>
        </div>
    @else
        <div class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200/70">
            <h3 class="text-xl font-extrabold text-slate-950">You're logged in.</h3>
            <p class="mt-2 text-sm text-slate-500">Your dashboard access is ready. Please contact admin if your role-based menu is not visible.</p>
        </div>
    @endif
</x-app-layout>
