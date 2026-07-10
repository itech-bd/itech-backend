<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-[#2E3192]/70">Admin · Access Control</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">Users</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Manage student, mentor and admin accounts with role visibility.</p>
            </div>
        </div>
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    @endpush

    <div class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-extrabold text-slate-950">User Directory</h3>
                <p class="mt-1 text-sm text-slate-500">Use role tabs to keep the list clean and easy to scan.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2 rounded-3xl bg-slate-50 p-2 ring-1 ring-slate-200/70" aria-label="User role tabs">
                @foreach([
                    'student' => ['label' => 'Students', 'icon' => 'fa-solid fa-user-graduate'],
                    'mentor' => ['label' => 'Mentors', 'icon' => 'fa-solid fa-chalkboard-user'],
                    'admin' => ['label' => 'Admins', 'icon' => 'fa-solid fa-user-shield'],
                ] as $role => $tab)
                    <button type="button" data-users-tab="{{ $role }}" class="users-tab-btn inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-sm font-extrabold transition {{ $role === 'student' ? 'bg-[#2E3192] text-white shadow-sm' : 'text-slate-600 hover:bg-white hover:text-[#2E3192]' }}">
                        <i class="{{ $tab['icon'] }}"></i>
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </div>
        </div>

        @foreach(['student', 'mentor', 'admin'] as $role)
            <div id="users-tab-{{ $role }}" class="users-tab-panel {{ $role !== 'student' ? 'hidden' : '' }}">
                <div class="w-full overflow-x-auto">
                    <table id="users-table-{{ $role }}" class="w-full min-w-max divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">SL</th>
                                <th class="px-6 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Registration Date</th>
                                <th class="px-6 py-3 text-left text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Roles</th>
                                <th class="px-6 py-3 text-right text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white"></tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script>
            $(function () {
                const ajaxUrl = @json(route('users.index'));
                const tableByRole = {};

                function initUsersTable(role) {
                    if (tableByRole[role]) {
                        return tableByRole[role];
                    }

                    tableByRole[role] = $('#users-table-' + role).DataTable({
                        processing: true,
                        serverSide: true,
                        autoWidth: false,
                        scrollX: true,
                        scrollCollapse: true,
                        ajax: {
                            url: ajaxUrl,
                            data: function (d) {
                                d.role = role;
                            }
                        },
                        columns: [
                            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-6 py-4 whitespace-nowrap' },
                            { data: 'name', name: 'name', className: 'px-6 py-4 whitespace-nowrap font-semibold text-slate-900' },
                            { data: 'email', name: 'email', className: 'px-6 py-4 whitespace-nowrap text-slate-600' },
                            { data: 'registration_date', name: 'created_at', className: 'px-6 py-4 whitespace-nowrap text-slate-600' },
                            { data: 'roles', name: 'roles.name', orderable: false, searchable: false, className: 'px-6 py-4 whitespace-normal' },
                            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'px-6 py-4 whitespace-nowrap text-right' },
                        ],
                        order: [[3, 'desc']],
                        pageLength: 10,
                        language: {
                            search: 'Search users:',
                            lengthMenu: 'Show _MENU_ users',
                        }
                    });

                    return tableByRole[role];
                }

                function setActiveTab(role) {
                    $('.users-tab-panel').addClass('hidden');
                    $('#users-tab-' + role).removeClass('hidden');

                    $('.users-tab-btn')
                        .removeClass('bg-[#2E3192] text-white shadow-sm')
                        .addClass('text-slate-600 hover:bg-white hover:text-[#2E3192]');

                    $('[data-users-tab="' + role + '"]')
                        .removeClass('text-slate-600 hover:bg-white hover:text-[#2E3192]')
                        .addClass('bg-[#2E3192] text-white shadow-sm');

                    const table = initUsersTable(role);
                    setTimeout(function () {
                        table.columns.adjust();
                    }, 0);
                }

                $(document).on('click', '.users-tab-btn', function () {
                    setActiveTab($(this).data('users-tab'));
                });

                setActiveTab('student');
            });
        </script>
    @endpush
</x-app-layout>
