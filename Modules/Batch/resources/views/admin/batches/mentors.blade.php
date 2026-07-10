<x-app-layout>
    @php($availableMentorSearchIndex = ($availableMentors ?? collect())
        ->map(fn ($m) => ['id' => $m->id, 'text' => strtolower($m->name . ' ' . $m->email)])
        ->values())
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
             x-data="{
                addOpen: false,
                query: '',
                minQuery: 1,
                mentors: @js($availableMentorSearchIndex),
                hasMatches() {
                    const q = this.query.trim().toLowerCase();
                    if (q.length < this.minQuery) return false;
                    return this.mentors.some((m) => (m.text || '').includes(q));
                }
             }"
             @keydown.escape.window="addOpen = false">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 leading-tight">Batch Mentors</h2>
                <p class="mt-1 text-sm text-slate-500">Batch: <span class="font-semibold">{{ $batch->name }}</span></p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard.batches.show', $batch) }}"
                   class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back</a>
                <button type="button"
                        @click="addOpen = true; query = ''; $nextTick(() => $refs.mentorSearch?.focus())"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    Add mentor
                </button>
                <a href="/dashboard/batches/{{ $batch->getRouteKey() }}/schedules" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">View Schedule</a>
            </div>

            <template x-if="addOpen">
                <div class="fixed inset-0 z-50">
                    <div class="absolute inset-0 bg-slate-900/60" @click="addOpen = false"></div>

                    <div class="relative mx-auto mt-16 w-full max-w-2xl px-4">
                        <div class="rounded-2xl bg-white shadow-xl ring-1 ring-slate-200">
                            <div class="flex items-start justify-between gap-3 border-b border-slate-200 p-5">
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">Add mentor</h3>
                                    <p class="mt-1 text-sm text-slate-500">Search and add a mentor to this batch.</p>
                                </div>
                                <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-50" @click="addOpen = false">
                                    <span class="sr-only">Close</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" /></svg>
                                </button>
                            </div>

                            <div class="p-5">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700">Search</label>
                                    <input type="text"
                                           x-ref="mentorSearch"
                                           x-model.debounce.250ms="query"
                                           placeholder="Type name or email..."
                                           class="mt-2 w-full rounded-xl border-slate-300" />
                                </div>

                                <div class="mt-4 rounded-xl ring-1 ring-slate-200">
                                    @php($list = $availableMentors ?? collect())
                                    @if($list->count() === 0)
                                        <div class="p-4 text-sm text-slate-600">No available mentors to add.</div>
                                    @else
                                        <div class="p-4 text-sm text-slate-600" x-show="query.trim().length < minQuery">
                                            Start typing to search mentors.
                                        </div>

                                        <div class="p-4 text-sm text-slate-600"
                                             x-show="query.trim().length >= minQuery && !hasMatches()">
                                            No matches found.
                                        </div>

                                        <div class="max-h-[380px] overflow-auto divide-y divide-slate-200"
                                             x-show="query.trim().length >= minQuery">
                                            @foreach($list as $mentor)
                                                <div class="p-4 flex items-center justify-between gap-3"
                                                     x-show="query.trim().length >= minQuery && ('{{ strtolower($mentor->name . ' ' . $mentor->email) }}').includes(query.trim().toLowerCase())">
                                                    <div class="min-w-0">
                                                        <div class="font-semibold text-slate-900 truncate">{{ $mentor->name }}</div>
                                                        <div class="text-sm text-slate-500 truncate">{{ $mentor->email }}</div>
                                                    </div>

                                                    <form method="POST" action="{{ route('dashboard.batches.mentors.add', $batch) }}">
                                                        @csrf
                                                        <input type="hidden" name="mentor_id" value="{{ $mentor->id }}" />
                                                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-500">Add</button>
                                                    </form>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-4 flex items-center justify-end">
                                    <button type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="addOpen = false">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </x-slot>

    {{-- success flash is handled by the global layout to avoid duplicate messages --}}

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-rose-50 p-4 text-sm text-rose-800 ring-1 ring-rose-100">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Assigned mentors</h3>
                <p class="mt-1 text-sm text-slate-500">These mentors are assigned to this batch.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">{{ isset($assignedMentors) ? $assignedMentors->count() : 0 }}</span>
        </div>

        @if(!isset($assignedMentors) || $assignedMentors->count() === 0)
            <div class="mt-4 rounded-xl border border-dashed border-slate-200 p-6 text-sm text-slate-600">
                No mentors assigned yet.
            </div>
        @else
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($assignedMentors as $mentor)
                    <div class="rounded-xl border border-slate-200 bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="font-semibold text-slate-900 truncate">{{ $mentor->name }}</div>
                                <div class="mt-1 text-sm text-slate-600 truncate">{{ $mentor->email }}</div>
                            </div>

                            <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-800 ring-1 ring-indigo-100">Mentor</span>
                        </div>

                        <div class="mt-4 flex items-center justify-end">
                            <form method="POST" action="{{ route('dashboard.batches.mentors.remove', [$batch, $mentor]) }}" onsubmit="return confirm('Remove this mentor from the batch?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-800 hover:bg-rose-100">Remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
