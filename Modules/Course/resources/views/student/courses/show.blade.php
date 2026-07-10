<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Course Workspace</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">{{ $course->title }}</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">View your batches, class routine and course details from this page.</p>
            </div>
            <x-panel.action-link href="{{ url('/dashboard/student/courses') }}" tone="secondary">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Courses
            </x-panel.action-link>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[.85fr_1.15fr]">
        <div class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200/70">
            <div class="relative h-64 bg-gradient-to-br from-[#2E3192] via-[#20236f] to-[#151748]">
                @if($course->thumbnail_url)
                    <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="h-full w-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/65 to-transparent"></div>
                @else
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(244,123,32,.45),transparent_35%),radial-gradient(circle_at_bottom_left,rgba(229,54,44,.30),transparent_40%)]"></div>
                @endif
                <div class="absolute inset-x-0 bottom-0 p-6 text-white">
                    <div class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-extrabold ring-1 ring-white/20">Enrolled Course</div>
                    <h3 class="mt-3 text-2xl font-extrabold leading-tight">{{ $course->title }}</h3>
                </div>
            </div>
            <div class="p-6">
                <div class="prose prose-sm max-w-none prose-headings:text-slate-950 prose-a:text-[#2E3192] prose-strong:text-slate-900">
                    {!! $course->description !!}
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-950">Your Batches</h3>
                        <p class="mt-1 text-sm text-slate-500">Open a batch to see class schedule, mentors and class links.</p>
                    </div>
                    <span class="rounded-full bg-[#2E3192]/10 px-3 py-1 text-xs font-extrabold text-[#2E3192]">{{ $course->batches->count() }} total</span>
                </div>

                <div class="space-y-3">
                    @forelse($course->batches as $batch)
                        <a href="/dashboard/student/batches/{{ $batch->getRouteKey() }}" class="block rounded-2xl border border-slate-100 bg-gradient-to-r from-slate-50 to-white p-4 transition hover:border-[#2E3192]/30 hover:shadow-md">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="font-extrabold text-slate-950">{{ $batch->name }}</h4>
                                        <x-panel.status-badge :status="$batch->status" />
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-2 text-xs font-bold text-slate-500">
                                        @if($batch->class_time)
                                            <span class="rounded-full bg-white px-2.5 py-1 ring-1 ring-slate-200"><i class="fa-regular fa-clock mr-1"></i>{{ $batch->class_time }}</span>
                                        @endif
                                        <span class="rounded-full bg-white px-2.5 py-1 ring-1 ring-slate-200"><i class="fa-solid fa-users mr-1"></i>{{ $batch->students_count }} Students</span>
                                        <span class="rounded-full bg-white px-2.5 py-1 ring-1 ring-slate-200"><i class="fa-solid fa-chalkboard-user mr-1"></i>{{ $batch->mentors_count }} Mentors</span>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-2 rounded-xl bg-[#2E3192] px-4 py-2 text-sm font-extrabold text-white">
                                    Open Batch <i class="fa-solid fa-arrow-right"></i>
                                </span>
                            </div>
                        </a>
                    @empty
                        <x-panel.empty-state title="No enrolled batches" message="Your course batch will appear here after assignment." icon="fa-regular fa-folder-open" />
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
