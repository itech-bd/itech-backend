@php
    $isRoles = $entity === 'roles';
    $title = $isRoles ? 'Roles' : 'Permissions';
    $singular = $isRoles ? 'role' : 'permission';
@endphp
<x-app-layout>
    <x-slot name="header">
        <p class="text-xs font-extrabold uppercase tracking-widest text-indigo-600">Access management</p>
        <h2 class="mt-1 text-2xl font-extrabold text-slate-950">{{ $title }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ $isRoles ? 'Define who can do what with reusable sets of permissions.' : 'Manage the individual actions that can be assigned to roles.' }}</p>
    </x-slot>
    <x-slot name="headerActions">
        <a href="{{ route($entity.'.create') }}" class="inline-flex min-h-11 items-center gap-2 whitespace-nowrap rounded-xl bg-indigo-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-indigo-800"><i class="fa-solid fa-plus" aria-hidden="true"></i>Create {{ $singular }}</a>
    </x-slot>

    <div class="space-y-5">
        <dl class="grid gap-4 sm:grid-cols-3">
            @foreach ([['Roles', $stats['roles'], 'fa-user-shield', 'Groups of permissions'], ['Permissions', $stats['permissions'], 'fa-key', 'Available actions'], ['Not assigned to roles', $stats['unassigned'], 'fa-layer-group', 'Permissions without a role']] as [$label, $count, $icon, $hint])
                <div class="flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-indigo-50 text-indigo-600"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                    <div><dt class="text-xs font-semibold text-slate-500">{{ $label }}</dt><dd class="mt-1 text-2xl font-extrabold text-slate-900">{{ number_format($count) }}</dd><dd class="mt-1 text-xs text-slate-400">{{ $hint }}</dd></div>
                </div>
            @endforeach
        </dl>
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <nav aria-label="Access management" class="flex gap-2 border-b border-slate-200 bg-slate-50/60 p-3">
                @foreach (['roles' => 'Roles', 'permissions' => 'Permissions'] as $key => $label)
                    <a href="{{ route($key.'.index') }}" @if($entity === $key) aria-current="page" @endif class="inline-flex min-h-10 items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold {{ $entity === $key ? 'bg-indigo-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}"><i class="fa-solid {{ $key === 'roles' ? 'fa-user-shield' : 'fa-key' }}" aria-hidden="true"></i>{{ $label }}</a>
                @endforeach
            </nav>
            <div class="p-5 sm:p-6">
                <h3 class="text-lg font-bold text-slate-900">{{ $isRoles ? 'Role directory' : 'Permission directory' }}</h3>
                <p class="mt-1 mb-5 text-sm text-slate-500">{{ $isRoles ? 'Review assigned permissions or edit a role to update access.' : 'See which roles use each permission and manage its name.' }}</p>
                <p id="access-table-error" role="alert" class="mb-4 hidden rounded-xl bg-rose-50 p-4 text-sm text-rose-700">Unable to load records. Please refresh the page.</p>
                <div class="overflow-x-auto">
                    <table id="access-table" class="w-full text-left text-sm">
                        <thead><tr class="bg-slate-50">
                            @foreach (['#', $isRoles ? 'Role name' : 'Permission name', $isRoles ? 'Assigned permissions' : 'Assigned roles', 'Actions'] as $heading)
                                <th scope="col" class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-500">{{ $heading }}</th>
                            @endforeach
                        </tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
    <x-modal name="confirm-access-delete" :show="false" maxWidth="md">
        <div class="p-6">
            <span class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-600"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></span>
            <h2 class="text-xl font-bold text-slate-900">Delete {{ $singular }}?</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">Delete <span id="access-delete-name" class="font-semibold text-slate-900"></span>? This removes its assignments and cannot be undone.</p>
            <form id="access-delete-form" method="POST" class="mt-6 flex justify-end gap-3">
                @csrf @method('DELETE')
                <button type="button" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirm-access-delete' }))">Cancel</button>
                <button type="submit" class="rounded-xl bg-rose-700 px-4 py-2 text-sm font-bold text-white hover:bg-rose-800">Delete {{ $singular }}</button>
            </form>
        </div>
    </x-modal>
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
        <style>
            #access-table_wrapper .dataTables_length select { padding-right: 2rem !important; background-position: right .5rem center !important; }
            #access-table_wrapper .dataTables_filter, #access-table_wrapper .dataTables_length { margin-bottom: 1rem; }
            #access-table td { vertical-align: top; }
            #access-table { border-collapse: collapse; }
        </style>
    @endpush
    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script>
            $(function () {
                $.fn.dataTable.ext.errMode = 'none';
                $('#access-table').on('error.dt', function () { $('#access-table-error').removeClass('hidden'); }).DataTable({
                    processing: true, serverSide: true, ajax: @json(route($entity.'.index')),
                    columns: [
                        { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-4 py-4 text-slate-400' },
                        { data: 'name', name: 'name', className: 'px-4 py-4 font-semibold text-slate-900 break-all' },
                        { data: 'access', orderable: false, searchable: false, className: 'px-4 py-4' },
                        { data: 'actions', orderable: false, searchable: false, className: 'px-4 py-4 text-right whitespace-nowrap' }
                    ],
                    order: [[1, 'asc']], pageLength: 10,
                    language: { search: 'Search:', searchPlaceholder: @json('Search '.$entity.' by name'), lengthMenu: 'Show _MENU_', emptyTable: @json('No '.$entity.' yet. Use Create '.$singular.' to get started.'), zeroRecords: 'No matching records. Try another name.' }
                });
                $(document).on('click', '.js-access-delete', function () {
                    $('#access-delete-form').attr('action', $(this).attr('data-delete-url'));
                    $('#access-delete-name').text($(this).attr('data-record-name'));
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-access-delete' }));
                });
            });
        </script>
    @endpush
</x-app-layout>
