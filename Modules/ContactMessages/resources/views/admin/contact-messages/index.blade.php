<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 leading-tight">Contact Messages</h2>
                <p class="mt-1 text-sm text-slate-500">Review visitor enquiries submitted from the public contact page.</p>
            </div>

            <div class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-sm font-semibold text-amber-800 ring-1 ring-amber-200">
                Unread: {{ $unreadCount }}
            </div>
        </div>
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
        <style>
            .dataTables_wrapper .dataTables_length select {
                padding-right: 2rem !important;
                padding-left: 0.75rem !important;
                background-position: right 0.5rem center !important;
                background-repeat: no-repeat !important;
            }
        </style>
    @endpush

    <div class="space-y-6">
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="grid gap-4 md:grid-cols-[220px_auto] md:items-end">
                <div>
                    <label for="contact-message-status-filter" class="block text-sm font-medium text-slate-700">Status</label>
                    <select id="contact-message-status-filter" class="mt-1 w-full rounded-lg border-slate-300">
                        <option value="all">All</option>
                        <option value="unread">Unread</option>
                        <option value="read">Read</option>
                    </select>
                </div>

                <p class="text-sm text-slate-500">Use the table search to find messages by name, email, phone, subject, or message content.</p>
            </div>
        </div>

        {{-- Bulk actions bar (shown when rows are selected) --}}
        <form id="bulk-delete-form" method="POST" action="{{ route('dashboard.contact-messages.destroyBulk') }}">
            @csrf
            @method('DELETE')
            <div id="bulk-ids-container"></div>
            <div id="bulk-actions-bar" class="hidden items-center justify-between gap-4 rounded-xl bg-rose-50 px-4 py-3 ring-1 ring-rose-200">
                <span id="selected-count-label" class="text-sm font-semibold text-rose-800">0 selected</span>
                <button type="submit"
                        onclick="return confirm('Delete all selected messages? This cannot be undone.')"
                        class="rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                    Delete Selected
                </button>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto p-4">
                <table id="contact-messages-table" class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <input type="checkbox" id="select-all"
                                       class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                       title="Select all">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">SL</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Visitor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Subject</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Received</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white"></tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script>
            $(function () {
                var table = $('#contact-messages-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('dashboard.contact-messages.index') }}',
                        data: function (d) {
                            d.status = $('#contact-message-status-filter').val();
                        }
                    },
                    columns: [
                        {
                            data: 'id',
                            name: 'id',
                            orderable: false,
                            searchable: false,
                            render: function (data) {
                                return '<input type="checkbox" class="row-select rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" value="' + data + '">';
                            }
                        },
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'visitor', name: 'name', orderable: false },
                        { data: 'subject', name: 'subject', orderable: false },
                        { data: 'status_badge', name: 'read_at', searchable: false },
                        { data: 'received_at', name: 'created_at' },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false },
                    ],
                    order: [[5, 'desc']],
                });

                $('#contact-message-status-filter').on('change', function () {
                    table.ajax.reload();
                });

                // Reset checkboxes and bulk bar after each DataTables draw
                table.on('draw', function () {
                    $('#select-all').prop('checked', false);
                    updateBulkBar();
                });

                // Select-all checkbox
                $('#select-all').on('change', function () {
                    $('#contact-messages-table .row-select').prop('checked', this.checked);
                    updateBulkBar();
                });

                // Individual row checkboxes (delegated — DataTables re-renders rows)
                $('#contact-messages-table').on('change', '.row-select', function () {
                    var total   = $('#contact-messages-table .row-select').length;
                    var checked = $('#contact-messages-table .row-select:checked').length;
                    $('#select-all').prop('checked', total > 0 && checked === total);
                    updateBulkBar();
                });

                function updateBulkBar() {
                    var count = $('#contact-messages-table .row-select:checked').length;
                    if (count > 0) {
                        $('#bulk-actions-bar').removeClass('hidden').addClass('flex');
                        $('#selected-count-label').text(count + ' message' + (count !== 1 ? 's' : '') + ' selected');
                    } else {
                        $('#bulk-actions-bar').addClass('hidden').removeClass('flex');
                        $('#select-all').prop('checked', false);
                    }
                }

                // Populate hidden id inputs before bulk-delete form submits
                $('#bulk-delete-form').on('submit', function () {
                    $('#bulk-ids-container').empty();
                    $('#contact-messages-table .row-select:checked').each(function () {
                        $('<input>').attr({ type: 'hidden', name: 'ids[]', value: $(this).val() })
                            .appendTo('#bulk-ids-container');
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
