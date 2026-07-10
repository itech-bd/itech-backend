<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Learning</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">My Courses</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">All courses you are enrolled in are organized here.</p>
            </div>
            <x-panel.action-link href="{{ route('courses') }}" tone="orange">
                <i class="fa-solid fa-magnifying-glass"></i>
                Browse More Courses
            </x-panel.action-link>
        </div>
    </x-slot>

    @if($courses->count())
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach($courses as $course)
                <article class="group overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-[#2E3192]/10">
                    <div class="relative h-44 overflow-hidden bg-gradient-to-br from-[#2E3192] via-[#23266f] to-[#151748]">
                        @if($course->thumbnail_url)
                            <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/55 to-transparent"></div>
                        @else
                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(244,123,32,.45),transparent_34%),radial-gradient(circle_at_bottom_left,rgba(229,54,44,.32),transparent_38%)]"></div>
                            <div class="absolute inset-0 grid place-items-center p-6 text-center">
                                <div>
                                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-white/15 text-white ring-1 ring-white/20">
                                        <i class="fa-solid fa-graduation-cap text-2xl"></i>
                                    </div>
                                    <div class="mt-3 text-sm font-extrabold text-white/90">iTechBD Course</div>
                                </div>
                            </div>
                        @endif
                        <div class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-xs font-extrabold text-[#2E3192] shadow-sm">
                            {{ $course->batches_count }} {{ \Illuminate\Support\Str::plural('Batch', $course->batches_count) }}
                        </div>
                    </div>

                    <div class="p-5">
                        <h3 class="line-clamp-2 text-lg font-extrabold leading-snug text-slate-950">{{ $course->title }}</h3>
                        <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-500">{{ \Illuminate\Support\Str::limit(strip_tags((string) $course->description), 145) }}</p>

                        <div class="mt-5 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 text-sm font-bold text-slate-500">
                                <span class="grid h-9 w-9 place-items-center rounded-xl bg-[#2E3192]/10 text-[#2E3192]"><i class="fa-solid fa-book-open"></i></span>
                                Enrolled
                            </div>
                            <x-panel.action-link href="{{ url('/dashboard/student/courses/'.$course->getRouteKey()) }}" tone="primary">
                                Open Course
                                <i class="fa-solid fa-arrow-right"></i>
                            </x-panel.action-link>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6 rounded-2xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200/70">
            {{ $courses->links() }}
        </div>
    @else
        <x-panel.empty-state title="No enrolled course found" message="After your enrollment is approved, your course cards will appear here." icon="fa-solid fa-graduation-cap">
            <x-panel.action-link href="{{ route('courses') }}" tone="orange">Explore Courses</x-panel.action-link>
        </x-panel.empty-state>
    @endif
</x-app-layout>
