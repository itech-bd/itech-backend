<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 leading-tight">My Mentor Batches</h2>
            <p class="mt-1 text-sm text-slate-500">Batches assigned to you.</p>
        </div>
    </x-slot>

    @php
        /** @var string $activeStatus */
        $activeStatus = $activeStatus ?? (string) request()->query('status', 'upcoming');
        $tabs = [
            'upcoming' => 'Upcoming',
            'running' => 'Running',
            'completed' => 'Completed',
        ];
    @endphp

    <div class="mb-4">
        <nav class="inline-flex rounded-lg bg-slate-100 p-1 ring-1 ring-slate-200" aria-label="Batch status tabs">
            @foreach ($tabs as $key => $label)
                @php
                    $isActive = $activeStatus === $key;
                    $classes = $isActive
                        ? 'bg-white text-slate-900 shadow-sm'
                        : 'text-slate-600 hover:text-slate-900';
                @endphp
                <a href="{{ route('dashboard.mentor.batches.index', ['status' => $key]) }}"
                    class="{{ $classes }} rounded-md px-4 py-2 text-sm font-semibold">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>

    <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Batch</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Course</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Students</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Classes</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($batches as $batch)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ $batch->name }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ ucfirst($batch->status) }} • {{ $batch->class_time }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $batch->course?->title }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $batch->students_count }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $batch->class_schedules_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="/dashboard/mentor/batches/{{ $batch->getRouteKey() }}" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">No assigned batches.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-4 py-3">
            {{ $batches->links() }}
        </div>
    </div>
</x-app-layout>
