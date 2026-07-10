<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Admin &middot; Course Management</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">Courses</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Create, update and monitor all professional skill courses.</p>
            </div>

            @can('addCourse')
                <x-panel.action-link href="{{ url('/dashboard/courses/create') }}" tone="orange">
                    <i class="fa-solid fa-plus"></i>
                    Add Course
                </x-panel.action-link>
            @endcan
        </div>
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    @endpush

    <div class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-extrabold text-slate-950">Course List</h3>
                <p class="mt-1 text-sm text-slate-500">Search, filter and manage course records.</p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-2xl bg-[#2E3192]/10 px-3 py-2 text-sm font-bold text-[#2E3192]">
                <i class="fa-solid fa-database"></i>
                Dynamic DataTable
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="courses-table" class="min-w-full divide-y divide-slate-200 overflow-hidden rounded-2xl">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">SL</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Title</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Fee</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Batches</th>
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
                $('#courses-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('dashboard.courses.index') }}',
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'title', name: 'title' },
                        { data: 'fee', name: 'old_price', orderable: false, searchable: false },
                        { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                        { data: 'batches_count', name: 'batches_count', searchable: false },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false },
                    ],
                    order: [[1, 'asc']],
                    pageLength: 10,
                    language: {
                        search: 'Search courses:',
                        lengthMenu: 'Show _MENU_ courses',
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
