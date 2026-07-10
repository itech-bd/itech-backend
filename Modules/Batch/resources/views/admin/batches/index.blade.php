<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Admin · Course Batches</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">Batches</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Manage batches for: <span class="font-extrabold text-slate-800">{{ $course->title }}</span></p>
            </div>

            @can('create', \Modules\Batch\Models\Batch::class)
                <x-panel.action-link href="{{ url('/dashboard/batches/create/'.$course->getRouteKey()) }}" tone="orange">
                    <i class="fa-solid fa-plus"></i>
                    Add Batch
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
                <h3 class="text-lg font-extrabold text-slate-950">{{ $course->title }} Batch List</h3>
                <p class="mt-1 text-sm text-slate-500">Students, mentors and class status for this course.</p>
            </div>
            <a href="{{ route('dashboard.courses.show', $course) }}" class="inline-flex items-center gap-2 rounded-2xl bg-slate-100 px-3 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-200">
                <i class="fa-solid fa-arrow-left"></i>
                Course Details
            </a>
        </div>
        <div class="overflow-x-auto">
            <table id="course-batches-table" class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">SL</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Dates</th>
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
                $('#course-batches-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('dashboard.courses.batches.index', $course) }}',
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'batch_display', name: 'name' },
                        { data: 'dates', name: 'start_date', searchable: false },
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
