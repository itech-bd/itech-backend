<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 leading-tight">Mentor Details</h2>
                <p class="mt-1 text-sm text-slate-500">View mentor profile information.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="/dashboard/mentors" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back</a>
                @can('update', $mentor)
                    <a href="/dashboard/mentors/{{ $mentor->getRouteKey() }}/edit" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500">Edit</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl space-y-4">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Name</div>
            <div class="mt-1 text-lg font-semibold text-slate-900">{{ $mentor->name }}</div>

            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Topic</div>
                    <div class="mt-1 text-sm text-slate-800">{{ $mentor->topic ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</div>
                    <div class="mt-1 text-sm text-slate-800">{{ $mentor->is_active ? 'Active' : 'Hidden' }}</div>
                </div>
            </div>

            <div class="mt-5">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bio</div>
                @php
                    $mentorBioText = trim((string) ($mentor->bio ?? ''));
                    if ($mentorBioText !== '') {
                        $mentorBioText = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $mentorBioText) ?? $mentorBioText;
                        $mentorBioText = preg_replace('/<\s*\/\s*p\s*>/i', "\n\n", $mentorBioText) ?? $mentorBioText;
                        $mentorBioText = trim(strip_tags($mentorBioText));
                    }
                @endphp
                <div class="mt-1 whitespace-pre-line text-sm text-slate-800">{!! nl2br(e($mentorBioText !== '' ? $mentorBioText : '-')) !!}</div>
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Linked User</div>
            @if($mentor->user)
                <div class="mt-2 text-sm text-slate-800">
                    <div class="font-semibold">{{ $mentor->user->name }}</div>
                    <div class="text-slate-500">{{ $mentor->user->email }}</div>
                </div>
            @else
                <div class="mt-2 text-sm text-slate-500">Not linked</div>
            @endif
        </div>
    </div>
</x-app-layout>
