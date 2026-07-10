<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('All Roles') }}
        </h2>
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
        <style>
            /* Fix: the select arrow overlaps the numbers in the length dropdown */
            .dataTables_wrapper .dataTables_length select {
                padding-right: 2.0rem !important;
                padding-left: 0.75rem !important;
                background-position: right 0.5rem center !important;
                background-repeat: no-repeat !important;
            }
        </style>
    @endpush

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            {{-- session messages --}}
                            @if (session('success'))
                                <div class="text-sm text-green-600">
                                    {{ session('success') }}
                                </div>
                            @endif
                        </div>
                        <a href="/roles/create"
                            class="inline-flex items-center px-4 py-2 bg-indigo-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                            Create Role
                        </a>
                    </div>

                    <table id="roles-table" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SL</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Permissions</th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="confirm-role-delete" :show="false" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">Delete Role</h2>
            <p class="mt-2 text-sm text-gray-600">
                Are you sure you want to delete <span id="role-delete-name" class="font-semibold"></span>?
                This action cannot be undone.
            </p>

            <form id="role-delete-form" method="POST" class="mt-6">
                @csrf
                @method('DELETE')

                <div class="flex justify-end gap-3">
                    <button type="button"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirm-role-delete' }))">
                        Cancel
                    </button>

                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-red-700 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script>
            $(function () {
                $('#roles-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('roles.index') }}',
                    columns: [
                        {
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false,
                            className: 'px-6 py-4 whitespace-nowrap'
                        },
                        {
                            data: 'name',
                            name: 'name',
                            className: 'px-6 py-4 whitespace-nowrap'
                        },
                        {
                            data: 'permissions',
                            name: 'permissions.name',
                            orderable: false,
                            searchable: false,
                            className: 'px-6 py-4 whitespace-normal'
                        },
                        {
                            data: 'actions',
                            name: 'actions',
                            orderable: false,
                            searchable: false,
                            className: 'px-6 py-4 whitespace-nowrap text-right'
                        },
                    ],
                    order: [[1, 'asc']],
                });

                $(document).on('click', '.js-role-delete', function () {
                    const deleteUrl = $(this).data('delete-url');
                    const roleName = $(this).data('role-name') || 'this role';

                    $('#role-delete-form').attr('action', deleteUrl);
                    $('#role-delete-name').text(roleName);

                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-role-delete' }));
                });
            });
        </script>
    @endpush
</x-app-layout>
