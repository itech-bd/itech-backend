<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Admin · Finance</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">All Invoices</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Financial status only: Pending or Completed.</p>
            </div>
        </div>
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    @endpush

    @php
        $filters = [
            '' => ['label' => 'All', 'icon' => 'fa-solid fa-layer-group'],
            'pending' => ['label' => 'Pending', 'icon' => 'fa-solid fa-clock'],
            'completed' => ['label' => 'Completed', 'icon' => 'fa-solid fa-circle-check'],
        ];
    @endphp

    <div class="mb-5 flex flex-wrap items-center gap-2 rounded-3xl bg-white p-2 shadow-sm ring-1 ring-slate-200/70">
        @foreach ($filters as $value => $filter)
            @php
                $isActive = ($activeStatus === $value) || ($value === '' && $activeStatus === null);
                $href = route('dashboard.admin.invoices.index');
                if ($value !== '') {
                    $href .= '?status=' . $value;
                }
            @endphp

            <a href="{{ $href }}" class="inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-sm font-extrabold transition {{ $isActive ? 'bg-[#2E3192] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-[#2E3192]' }}">
                <i class="{{ $filter['icon'] }}"></i>
                {{ $filter['label'] }}
            </a>
        @endforeach
    </div>

    <div class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-extrabold text-slate-950">Invoice Records</h3>
                <p class="mt-1 text-sm text-slate-500">Search by invoice, student, course or batch.</p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-2xl bg-emerald-50 px-3 py-2 text-sm font-bold text-emerald-700">
                <i class="fa-solid fa-shield-check"></i>
                Payment Tracking
            </div>
        </div>
        <div id="admin-invoices-table-error" class="mb-4 hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
            Failed to load invoices. Please refresh the page.
        </div>
        <div class="overflow-x-auto">
            <table id="admin-invoices-table" class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Invoice</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Course</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Batch</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Date</th>
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
                const activeStatus = @json($activeStatus);

                $.fn.dataTable.ext.errMode = 'none';

                $('#admin-invoices-table')
                    .on('error.dt', function () {
                        $('#admin-invoices-table-error').removeClass('hidden');
                    })
                    .DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: '{{ route('dashboard.admin.invoices.index') }}',
                            data: function (d) {
                                if (activeStatus) {
                                    d.status = activeStatus;
                                }
                            }
                        },
                        columns: [
                            { data: 'invoice', name: 'id' },
                            { data: 'student', name: 'student', orderable: false },
                            { data: 'course', name: 'course', orderable: false },
                            { data: 'batch', name: 'batch', orderable: false },
                            { data: 'status', name: 'status', orderable: false, searchable: false },
                            { data: 'total', name: 'total', orderable: false, searchable: false, className: 'text-right' },
                            { data: 'date', name: 'date', orderable: false, searchable: false },
                            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right' },
                        ],
                        order: [[0, 'desc']],
                        pageLength: 10,
                        language: {
                            search: 'Search invoices:',
                            lengthMenu: 'Show _MENU_ invoices',
                        }
                    });
            });
        </script>
    @endpush
</x-app-layout>
