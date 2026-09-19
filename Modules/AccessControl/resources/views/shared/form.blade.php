@php
    $isRole = $entity === 'roles';
    $record = $isRole ? ($role ?? null) : ($permission ?? null);
    $editing = $record !== null;
    $singular = $isRole ? 'role' : 'permission';
    $title = ($editing ? 'Edit ' : 'Create ').$singular;
    $selected = session()->hasOldInput() ? old('permissions', []) : ($rolePermissions ?? []);
@endphp
<x-app-layout>
    <x-slot name="header">
        <p class="text-xs font-extrabold uppercase tracking-widest text-indigo-600">Access management / {{ ucfirst($entity) }}</p>
        <h2 class="mt-1 text-2xl font-extrabold text-slate-950">{{ $title }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ $isRole ? 'Give this role a name and choose the actions it allows.' : 'Define a permission that can be assigned to roles.' }}</p>
    </x-slot>
    <x-slot name="headerActions">
        <a href="{{ route($entity.'.index') }}" class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Back to {{ $entity }}</a>
    </x-slot>
    <div class="grid items-start gap-5 xl:grid-cols-4">
        <form action="{{ $editing ? route($entity.'.update', $record) : route($entity.'.store') }}" method="POST" class="overflow-hidden rounded-2xl border border-slate-200 bg-white xl:col-span-3">
            @csrf
            @if ($editing) @method('PUT') @endif
            <div class="space-y-6 p-5 sm:p-6">
                <section>
                    <h3 class="text-lg font-bold text-slate-900">{{ $isRole ? 'Role details' : 'Permission details' }}</h3>
                    <p class="mt-1 text-sm text-slate-500">Choose a unique, descriptive name.</p>
                    <label for="name" class="mt-5 block text-sm font-semibold text-slate-700">{{ ucfirst($singular) }} name <span class="text-rose-600">*</span></label>
                    <input id="name" name="name" type="text" required maxlength="255" autocomplete="off" value="{{ old('name', $record?->name) }}" placeholder="{{ $isRole ? 'e.g. course-manager' : 'e.g. readCourse' }}" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror class="mt-2 block w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name')<p id="name-error" class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </section>
                @if ($isRole)
                    <section class="border-t border-slate-100 pt-6" aria-labelledby="permissions-heading">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div><h3 id="permissions-heading" class="text-lg font-bold text-slate-900">Assign permissions</h3><p class="mt-1 text-sm text-slate-500">Choose the actions available to this role.</p></div>
                            <span id="permission-count" aria-live="polite" class="rounded-lg bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700">{{ count($selected) }} selected</span>
                        </div>
                        @if ($permissions->isNotEmpty())
                            <div class="mt-4 flex flex-wrap items-end justify-between gap-4 rounded-xl bg-slate-50 p-4">
                                <div class="min-w-0 flex-1"><label for="permissions-search" class="block text-xs font-semibold text-slate-500">Find a permission</label><input id="permissions-search" type="search" placeholder="Search by name..." class="mt-2 w-full rounded-xl border-slate-300 text-sm"></div>
                                <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 text-sm"><input id="permissions-select-all" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">Select visible</label>
                            </div>
                            <div id="permissions-grid" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($permissions as $item)
                                    <label class="permission-item flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-indigo-300 hover:bg-indigo-50/50" data-permission-name="{{ strtolower($item->name) }}">
                                        <input type="checkbox" name="permissions[]" value="{{ $item->id }}" @checked(in_array($item->id, $selected)) class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="min-w-0 break-all text-sm font-medium text-slate-700">{{ $item->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p id="permissions-empty" class="mt-4 hidden rounded-xl bg-slate-50 p-4 text-sm text-slate-500">No permissions match your search.</p>
                        @else
                            <p class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-500">No permissions are available yet. You can save this role and assign permissions later.</p>
                        @endif
                        @foreach ($errors->get('permissions*') as $messages)
                            @foreach ($messages as $message)<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@endforeach
                        @endforeach
                    </section>
                @endif
            </div>
            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 bg-slate-50/60 p-5">
                <a href="{{ route($entity.'.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-100">Cancel</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-700 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-800"><i class="fa-solid fa-check" aria-hidden="true"></i>{{ $editing ? 'Save changes' : 'Create '.$singular }}</button>
            </div>
        </form>
        <aside class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-5">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white text-indigo-600"><i class="fa-solid {{ $isRole ? 'fa-user-shield' : 'fa-key' }}" aria-hidden="true"></i></span>
            <h3 class="mt-4 font-bold text-slate-900">{{ $isRole ? 'Access through roles' : 'How permissions work' }}</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $isRole ? 'Roles group permissions together. Assign a role to an account to give it the access associated with that role.' : 'Permissions represent actions checked by the application. Creating a name here does not create a new application feature.' }}</p>
            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $isRole ? 'Review your selection before saving. Changes apply to accounts using this role.' : 'Keep permission names consistent with the application. Renaming an existing permission can affect access to its features.' }}</p>
        </aside>
    </div>
    @if ($isRole)
        @push('scripts')
            <script>
                (() => {
                    const items = [...document.querySelectorAll('.permission-item')];
                    const search = document.getElementById('permissions-search');
                    const all = document.getElementById('permissions-select-all');
                    if (!search || !all) return;
                    const visible = () => items.filter(item => !item.hidden);
                    function update() {
                        const checks = visible().map(item => item.querySelector('input'));
                        const count = checks.filter(input => input.checked).length;
                        all.checked = checks.length > 0 && count === checks.length;
                        all.indeterminate = count > 0 && count < checks.length;
                        all.disabled = checks.length === 0;
                        document.getElementById('permissions-empty').classList.toggle('hidden', checks.length !== 0);
                        document.getElementById('permission-count').textContent = items.filter(item => item.querySelector('input').checked).length + ' selected';
                    }
                    search.addEventListener('input', () => {
                        items.forEach(item => {
                            item.hidden = !item.dataset.permissionName.includes(search.value.trim().toLowerCase());
                            item.style.display = item.hidden ? 'none' : '';
                        });
                        update();
                    });
                    all.addEventListener('change', () => { visible().forEach(item => item.querySelector('input').checked = all.checked); update(); });
                    items.forEach(item => item.querySelector('input').addEventListener('change', update));
                    update();
                })();
            </script>
        @endpush
    @endif
</x-app-layout>
