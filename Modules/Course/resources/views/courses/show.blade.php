<x-app-layout>
    @php
        $thumb = $course->thumbnail;
        $thumbUrl = null;
        if (!empty($thumb)) {
            if (\Illuminate\Support\Str::startsWith($thumb, ['http://', 'https://'])) {
                $thumbUrl = $thumb;
            } else {
                $normalized = ltrim($thumb, '/');
                if (\Illuminate\Support\Str::startsWith($normalized, 'storage/')) {
                    $normalized = \Illuminate\Support\Str::after($normalized, 'storage/');
                }
                $thumbUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($normalized);
            }
        }
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                @if($thumbUrl)
                    <img src="{{ $thumbUrl }}" alt="{{ $course->title }}" class="h-12 w-12 rounded-xl object-cover ring-1 ring-slate-200 shrink-0">
                @endif
                <div class="min-w-0">
                    <h2 class="text-xl font-semibold text-slate-900 leading-tight truncate">{{ $course->title }}</h2>
                    <div class="mt-1 flex items-center gap-2">
                        @if($course->status === 'active')
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Inactive
                            </span>
                        @endif
                        <span class="text-xs text-slate-400">{{ $course->batches->count() }} {{ Str::plural('batch', $course->batches->count()) }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @can('editCourse')
                    <a href="/dashboard/courses/{{ $course->getRouteKey() }}/edit"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M13.5 3.5 16.5 6.5 7 16H4v-3L13.5 3.5Z"/></svg>
                        Edit
                    </a>
                @endcan
                @can('create', \Modules\Batch\Models\Batch::class)
                    <a href="/dashboard/batches/create/{{ $course->getRouteKey() }}"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z"/></svg>
                        Add Batch
                    </a>
                @endcan
                @can('viewAny', \Modules\Batch\Models\Batch::class)
                    <a href="/dashboard/courses/{{ $course->getRouteKey() }}/batches"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                        Manage Batches
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Main content --}}
        <div class="space-y-5 lg:col-span-2">

            {{-- Pricing --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- Online --}}
                <div class="rounded-2xl border border-sky-100 bg-gradient-to-br from-sky-50 to-white p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-100">
                            <svg class="h-4 w-4 text-sky-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a9 9 0 100 18A9 9 0 0010 1zm-.75 4a.75.75 0 011.5 0v.316a3.5 3.5 0 012.47 5.651.75.75 0 01-1.19-.913A2 2 0 1010 7.25a.75.75 0 010-1.5A3.5 3.5 0 0113.5 9.75a3.507 3.507 0 01-3 3.464V14a.75.75 0 01-1.5 0v-.786a3.5 3.5 0 01.25-6.964V5z" clip-rule="evenodd"/></svg>
                        </div>
                        <span class="text-sm font-semibold text-sky-700">Online</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-white p-3 ring-1 ring-sky-100">
                            <div class="text-xs text-slate-500">Old price</div>
                            <div class="mt-1 text-lg font-bold text-slate-900">
                                @if(!is_null($course->online_old_price))
                                    ৳ {{ number_format((float) $course->online_old_price, 2) }}
                                @else
                                    <span class="text-base text-slate-400">—</span>
                                @endif
                            </div>
                        </div>
                        <div class="rounded-xl bg-white p-3 ring-1 ring-sky-100">
                            <div class="text-xs text-slate-500">Discount price</div>
                            <div class="mt-1 text-lg font-bold text-emerald-600">
                                @if(!is_null($course->online_discount_price))
                                    ৳ {{ number_format((float) $course->online_discount_price, 2) }}
                                @else
                                    <span class="text-base text-slate-400">—</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Offline --}}
                <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100">
                            <svg class="h-4 w-4 text-amber-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z" clip-rule="evenodd"/></svg>
                        </div>
                        <span class="text-sm font-semibold text-amber-700">Offline (In-person)</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-white p-3 ring-1 ring-amber-100">
                            <div class="text-xs text-slate-500">Old price</div>
                            <div class="mt-1 text-lg font-bold text-slate-900">
                                @if(!is_null($course->offline_old_price))
                                    ৳ {{ number_format((float) $course->offline_old_price, 2) }}
                                @else
                                    <span class="text-base text-slate-400">—</span>
                                @endif
                            </div>
                        </div>
                        <div class="rounded-xl bg-white p-3 ring-1 ring-amber-100">
                            <div class="text-xs text-slate-500">Discount price</div>
                            <div class="mt-1 text-lg font-bold text-emerald-600">
                                @if(!is_null($course->offline_discount_price))
                                    ৳ {{ number_format((float) $course->offline_discount_price, 2) }}
                                @else
                                    <span class="text-base text-slate-400">—</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Thumbnail (if set) --}}
            @if($thumbUrl)
                <div class="overflow-hidden rounded-2xl ring-1 ring-slate-200 shadow-sm">
                    <img src="{{ $thumbUrl }}" alt="{{ $course->title }}" class="w-full max-h-72 object-cover">
                </div>
            @endif

            {{-- Description --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center gap-2 mb-4">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100">
                        <svg class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 0 1 2-2h4.586A2 2 0 0 1 12 2.586L15.414 6A2 2 0 0 1 16 7.414V16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4Zm2 6a1 1 0 0 1 1-1h6a1 1 0 1 1 0 2H7a1 1 0 0 1-1-1Zm1 3a1 1 0 1 0 0 2h4a1 1 0 1 0 0-2H7Z" clip-rule="evenodd"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-700">Course Description</h3>
                </div>
                <div class="prose prose-slate max-w-none text-sm leading-relaxed">{!! $course->description !!}</div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">
            {{-- Batches --}}
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50">
                            <svg class="h-4 w-4 text-indigo-600" viewBox="0 0 20 20" fill="currentColor"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zM6 8a2 2 0 11-4 0 2 2 0 014 0zM1.49 15.326a.78.78 0 01-.358-.442 3 3 0 014.308-3.516 6.484 6.484 0 00-1.905 3.959c-.023.222-.014.442.025.654a4.97 4.97 0 01-2.07-.655zM16.44 15.98a4.97 4.97 0 002.07-.654.78.78 0 00.357-.442 3 3 0 00-4.308-3.517 6.484 6.484 0 011.907 3.96 2.32 2.32 0 01-.026.654zM18 8a2 2 0 11-4 0 2 2 0 014 0zM5.304 16.19a.844.844 0 01-.277-.71 5 5 0 019.947 0 .843.843 0 01-.277.71A6.975 6.975 0 0110 18a6.974 6.974 0 01-4.696-1.81z"/></svg>
                        </div>
                        <span class="text-sm font-semibold text-slate-900">Batches</span>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">{{ $course->batches->count() }}</span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($course->batches as $batch)
                        <div class="px-5 py-4">
                            <div class="flex items-start justify-between gap-2">
                                <span class="font-semibold text-slate-900 text-sm">{{ $batch->name }}</span>
                                @php
                                    $statusColors = [
                                        'running'  => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                                        'upcoming' => 'bg-sky-50 text-sky-700 ring-sky-100',
                                        'ended'    => 'bg-slate-100 text-slate-600 ring-slate-200',
                                    ];
                                    $sc = $statusColors[$batch->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200';
                                @endphp
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold ring-1 {{ $sc }}">
                                    {{ ucfirst($batch->status) }}
                                </span>
                            </div>
                            <div class="mt-2 flex items-center gap-3 text-xs text-slate-500">
                                <span class="flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zM6 8a2 2 0 11-4 0 2 2 0 014 0zM1.49 15.326a.78.78 0 01-.358-.442 3 3 0 014.308-3.516 6.484 6.484 0 00-1.905 3.959c-.023.222-.014.442.025.654a4.97 4.97 0 01-2.07-.655zM16.44 15.98a4.97 4.97 0 002.07-.654.78.78 0 00.357-.442 3 3 0 00-4.308-3.517 6.484 6.484 0 011.907 3.96 2.32 2.32 0 01-.026.654zM18 8a2 2 0 11-4 0 2 2 0 014 0zM5.304 16.19a.844.844 0 01-.277-.71 5 5 0 019.947 0 .843.843 0 01-.277.71A6.975 6.975 0 0110 18a6.974 6.974 0 01-4.696-1.81z"/></svg>
                                    {{ $batch->students_count }} students
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v1h8v-1zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-1a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v1h-3zM4.75 12.094A5.973 5.973 0 004 15v1H1v-1a3 3 0 013.75-2.906z"/></svg>
                                    {{ $batch->mentors_count }} mentors
                                </span>
                            </div>
                            @can('view', $batch)
                                <a href="/dashboard/batches/{{ $batch->getRouteKey() }}"
                                   class="mt-3 inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 ring-1 ring-indigo-100">
                                    Open batch
                                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 10h12M10 4l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </a>
                            @endcan
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center px-5 py-10 text-center">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100">
                                <svg class="h-5 w-5 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zM6 8a2 2 0 11-4 0 2 2 0 014 0zM1.49 15.326a.78.78 0 01-.358-.442 3 3 0 014.308-3.516 6.484 6.484 0 00-1.905 3.959c-.023.222-.014.442.025.654a4.97 4.97 0 01-2.07-.655zM16.44 15.98a4.97 4.97 0 002.07-.654.78.78 0 00.357-.442 3 3 0 00-4.308-3.517 6.484 6.484 0 011.907 3.96 2.32 2.32 0 01-.026.654zM18 8a2 2 0 11-4 0 2 2 0 014 0zM5.304 16.19a.844.844 0 01-.277-.71 5 5 0 019.947 0 .843.843 0 01-.277.71A6.975 6.975 0 0110 18a6.974 6.974 0 01-4.696-1.81z"/></svg>
                            </div>
                            <p class="mt-2 text-sm font-medium text-slate-600">No batches yet</p>
                            @can('create', \Modules\Batch\Models\Batch::class)
                                <a href="/dashboard/batches/create/{{ $course->getRouteKey() }}"
                                   class="mt-3 inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                                    Add first batch
                                </a>
                            @endcan
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Course meta --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 space-y-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Course Info</div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">Slug</span>
                    <span class="font-mono text-xs text-slate-700 truncate max-w-[140px]">{{ $course->slug }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">Created</span>
                    <span class="text-slate-700">{{ optional($course->created_at)->format('d M, Y') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">Last updated</span>
                    <span class="text-slate-700">{{ optional($course->updated_at)->format('d M, Y') }}</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
