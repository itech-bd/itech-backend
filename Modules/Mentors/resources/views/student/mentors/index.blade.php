<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Support Team</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">My Mentors</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Mentors assigned to your approved batches are shown here.</p>
            </div>
            <x-panel.action-link href="{{ url('/dashboard/student/batches') }}" tone="secondary">
                <i class="fa-solid fa-layer-group"></i>
                My Batches
            </x-panel.action-link>
        </div>
    </x-slot>

    @if($mentors->count())
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach($mentors as $mentor)
                <article class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-[#2E3192]/10">
                    <div class="relative h-24 bg-gradient-to-br from-[#2E3192] via-[#252879] to-[#151748]">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(244,123,32,.45),transparent_38%)]"></div>
                        <div class="absolute -bottom-9 left-5">
                            @if($mentor->user)
                                <div class="rounded-3xl bg-white p-1 shadow-lg ring-1 ring-slate-200">
                                    <x-avatar :user="$mentor->user" size="h-16 w-16" text="text-lg" />
                                </div>
                            @else
                                <div class="grid h-20 w-20 place-items-center rounded-3xl bg-white p-1 shadow-lg ring-1 ring-slate-200">
                                    <div class="grid h-16 w-16 place-items-center rounded-2xl bg-[#2E3192]/10 text-[#2E3192]"><i class="fa-solid fa-user-tie text-2xl"></i></div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="p-5 pt-12">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="truncate text-lg font-extrabold text-slate-950">{{ $mentor->name }}</h3>
                                @if($mentor->topic)
                                    <p class="mt-1 text-sm font-semibold text-[#2E3192]">{{ $mentor->topic }}</p>
                                @endif
                            </div>
                            <x-panel.status-badge status="active" />
                        </div>

                        @if($mentor->bio)
                            <p class="mt-4 line-clamp-4 text-sm leading-6 text-slate-500">{{ \Illuminate\Support\Str::limit(strip_tags((string) $mentor->bio), 230) }}</p>
                        @else
                            <p class="mt-4 line-clamp-4 text-sm leading-6 text-slate-500">This mentor is assigned to support your batch learning and class progress.</p>
                        @endif

                        @if($mentor->user)
                            <div class="mt-5 rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-100">
                                <div class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-400">Contact</div>
                                <div class="mt-2 text-sm font-bold text-slate-950">{{ $mentor->user->name }}</div>
                                <div class="mt-1 break-all text-sm text-slate-500">{{ $mentor->user->email }}</div>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6 rounded-2xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200/70">
            {{ $mentors->links() }}
        </div>
    @else
        <x-panel.empty-state title="No mentor found yet" message="Mentors will appear here after you are added to an approved batch." icon="fa-solid fa-chalkboard-user">
            <x-panel.action-link href="{{ url('/dashboard/student/batches') }}" tone="primary">Check Batches</x-panel.action-link>
        </x-panel.empty-state>
    @endif
</x-app-layout>
