<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Admin · Batch Management</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">Batches</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">All batches across courses with mentors, students and schedule count.</p>
            </div>

            @can('create', \Modules\Batch\Models\Batch::class)
                <x-panel.action-link href="{{ url('/dashboard/batches/create') }}" tone="orange">
                    <i class="fa-solid fa-plus"></i>
                    Add New Batch
                </x-panel.action-link>
            @endcan
        </div>
    </x-slot>

    @php
        $activeStatus = $activeStatus ?? (string) request()->query('status', 'all');
        $tabs = [
            'all' => ['label' => 'All', 'icon' => 'fa-solid fa-layer-group'],
            'upcoming' => ['label' => 'Upcoming', 'icon' => 'fa-regular fa-calendar-plus'],
            'running' => ['label' => 'Running', 'icon' => 'fa-solid fa-person-running'],
            'completed' => ['label' => 'Completed', 'icon' => 'fa-solid fa-circle-check'],
        ];
    @endphp

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    @endpush

    <div class="mb-5 flex flex-wrap items-center gap-2 rounded-3xl bg-white p-2 shadow-sm ring-1 ring-slate-200/70">
        @foreach ($tabs as $key => $tab)
            @php $isActive = $activeStatus === $key; @endphp
            <a href="{{ route('dashboard.batches.index', ['status' => $key]) }}"
                class="inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-sm font-extrabold transition {{ $isActive ? 'bg-[#2E3192] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-[#2E3192]' }}">
                <i class="{{ $tab['icon'] }}"></i>
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    <div class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-extrabold text-slate-950">Batch Records</h3>
                <p class="mt-1 text-sm text-slate-500">Use search to quickly find any course batch.</p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-2xl bg-[#F47B20]/10 px-3 py-2 text-sm font-bold text-[#C9570B]">
                <i class="fa-solid fa-filter"></i>
                {{ ucfirst($activeStatus) }} View
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="admin-batches-table" class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">SL</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Batch</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Course</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Mentors</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Students</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Classes</th>
                        <th class="px-4 py-3 text-right text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white"></tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script>
            $(function () {
                $('#admin-batches-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('dashboard.batches.index', ['status' => $activeStatus]) }}',
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'batch_display', name: 'name' },
                        { data: 'course_title', name: 'course_title', orderable: false },
                        { data: 'status', name: 'status' },
                        { data: 'mentors_count', name: 'mentors_count', searchable: false },
                        { data: 'students_count', name: 'students_count', searchable: false },
                        { data: 'class_schedules_count', name: 'class_schedules_count', searchable: false },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false },
                    ],
                    order: [[1, 'asc']],
                    pageLength: 10,
                    language: {
                        search: 'Search batches:',
                        lengthMenu: 'Show _MENU_ batches',
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
