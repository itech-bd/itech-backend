<x-app-layout>
    @php
        $showCreateBatchModal = request()->boolean('create') || old('_batch_form') === 'create';
        $showEditBatchModal = old('_batch_form') === 'edit';
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Admin · Course Batches</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">Batches</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Manage batches for: <span class="font-extrabold text-slate-800">{{ $course->title }}</span></p>
            </div>

            @can('create', \Modules\Batch\Models\Batch::class)
                <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-batch' }))" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#F47B20] px-4 py-2 text-sm font-extrabold text-white shadow-[0_12px_26px_rgba(244,123,32,.22)] transition duration-200 hover:-translate-y-0.5 hover:bg-[#d96816]">
                    <i class="fa-solid fa-plus"></i>
                    Add Batch
                </button>
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

    @can('create', \Modules\Batch\Models\Batch::class)
        <x-modal name="create-batch" :show="$showCreateBatchModal" maxWidth="2xl" focusable>
            <div class="max-h-[calc(100vh-6rem)] overflow-y-auto bg-white">
                <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-slate-100 bg-white px-6 py-5">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">New Batch</p>
                        <h3 class="mt-1 text-xl font-black text-slate-950">Create batch</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500">This batch will be added under {{ $course->title }}.</p>
                    </div>
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'create-batch' }))" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-800">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="px-6 py-5">
                    @include('batch::admin.batches.partials.create-form', [
                        'modalName' => 'create-batch',
                        'includeCourseSelect' => false,
                        'course' => $course,
                    ])
                </div>
            </div>
        </x-modal>
    @endcan

    @can('editBatch')
        @include('batch::admin.batches.partials.edit-modal', [
            'modalName' => 'edit-batch',
            'show' => $showEditBatchModal,
        ])
    @endcan

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
