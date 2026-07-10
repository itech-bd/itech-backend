<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Roles') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Create Role</h3>
                        <p class="mt-1 text-sm text-gray-600">Add a new role name and submit.</p>
                    </div>

                    <form action="{{ route('roles.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <div class="mt-2">
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="e.g. admin"
                                    value="{{ old('name') }}"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            @if($permissions->isNotEmpty())
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-700">Assign Permissions</h4>
                                        <p class="mt-1 text-xs text-gray-500">Select one or more permissions for this role.</p>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 select-none">
                                            <input
                                                id="permissions-select-all"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <span>Select all</span>
                                        </label>

                                        <div class="relative">
                                            <input
                                                id="permissions-search"
                                                type="text"
                                                autocomplete="off"
                                                placeholder="Search permissions..."
                                                class="block w-full sm:w-64 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50/40 p-3">
                                    <div id="permissions-grid" class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                        @foreach($permissions as $permission)
                                            <label
                                                class="permission-item group flex cursor-pointer items-start gap-3 rounded-md border border-gray-200 bg-white p-3 shadow-sm transition hover:border-indigo-200 hover:shadow"
                                                data-permission-name="{{ strtolower($permission->name) }}"
                                            >
                                                <input
                                                    type="checkbox"
                                                    name="permissions[]"
                                                    value="{{ $permission->id }}"
                                                    @checked(in_array($permission->id, old('permissions', [])))
                                                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                />
                                                <span class="min-w-0">
                                                    <span class="block truncate text-sm font-medium text-gray-800 group-hover:text-indigo-700">{{ $permission->name }}</span>
                                                    <span class="mt-0.5 block text-xs text-gray-500">Permission</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>

                                    @error('permissions')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <a
                                href="/roles"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Cancel
                            </a>

                            <button
                                type="submit"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                style="background-color: #4f46e5; color: #ffffff;"
                            >
                                Submit
                            </button>
                        </div>
                    </form>

                    @if($permissions->isNotEmpty())
                        @push('scripts')
                            <script>
                                (function () {
                                    const grid = document.getElementById('permissions-grid');
                                    const searchInput = document.getElementById('permissions-search');
                                    const selectAll = document.getElementById('permissions-select-all');

                                    if (!grid || !searchInput || !selectAll) return;

                                    const items = Array.from(grid.querySelectorAll('.permission-item'));

                                    function updateSelectAllState() {
                                        const visibleCheckboxes = items
                                            .filter(el => el.style.display !== 'none')
                                            .map(el => el.querySelector('input[type="checkbox"]'))
                                            .filter(Boolean);

                                        if (visibleCheckboxes.length === 0) {
                                            selectAll.checked = false;
                                            selectAll.indeterminate = false;
                                            return;
                                        }

                                        const checkedCount = visibleCheckboxes.filter(cb => cb.checked).length;
                                        selectAll.checked = checkedCount === visibleCheckboxes.length;
                                        selectAll.indeterminate = checkedCount > 0 && checkedCount < visibleCheckboxes.length;
                                    }

                                    function applyFilter() {
                                        const q = (searchInput.value || '').trim().toLowerCase();
                                        items.forEach(el => {
                                            const name = el.getAttribute('data-permission-name') || '';
                                            el.style.display = name.includes(q) ? '' : 'none';
                                        });
                                        updateSelectAllState();
                                    }

                                    searchInput.addEventListener('input', applyFilter);

                                    selectAll.addEventListener('change', function () {
                                        const shouldCheck = selectAll.checked;
                                        items
                                            .filter(el => el.style.display !== 'none')
                                            .forEach(el => {
                                                const cb = el.querySelector('input[type="checkbox"]');
                                                if (cb) cb.checked = shouldCheck;
                                            });
                                        updateSelectAllState();
                                    });

                                    grid.addEventListener('change', function (e) {
                                        const target = e.target;
                                        if (target && target.matches('input[type="checkbox"][name="permissions[]"]')) {
                                            updateSelectAllState();
                                        }
                                    });

                                    updateSelectAllState();
                                })();
                            </script>
                        @endpush
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
