<?php

namespace Modules\AccessControl\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin'),
        ];
    }

    public function index()
    {
        if (request()->ajax() && request()->has('draw')) {
            $query = Role::query()
                ->with(['permissions:id,name'])
                ->select(['id', 'name']);

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('permissions', function (Role $role) {
                    if ($role->permissions->isEmpty()) {
                        return '<span class="text-sm text-gray-500">-</span>';
                    }

                    return $role->permissions
                        ->sortBy('name')
                        ->map(fn (Permission $permission) =>
                            '<span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700 ring-1 ring-inset ring-slate-200">'
                            . e($permission->name)
                            . '</span>'
                        )
                        ->implode(' ');
                })
                ->addColumn('actions', function (Role $role) {
                    $editUrl = route('roles.edit', $role);
                    $deleteUrl = route('roles.destroy', $role);

                    return
                        '<div class="inline-flex items-center gap-2">'
                        . '<a href="' . e($editUrl) . '" class="inline-flex items-center px-3 py-1.5 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition">Edit</a>'
                        . '<button type="button"'
                        . ' class="js-role-delete inline-flex items-center px-3 py-1.5 bg-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 transition"'
                        . ' data-delete-url="' . e($deleteUrl) . '"'
                        . ' data-role-name="' . e($role->name) . '"'
                        . '>Delete</button>'
                        . '</div>';
                })
                ->rawColumns(['permissions', 'actions'])
                ->toJson();
        }

        return view('accesscontrol::roles.index');
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();

        return view('accesscontrol::roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::create(['name' => $request->name]);

        $permissionIds = $request->input('permissions', []);
        $permissions = Permission::query()->whereIn('id', $permissionIds)->get();
        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::orderBy('name')->get();
        $rolePermissions = $role->permissions()->pluck('id')->all();

        return view('accesscontrol::roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role->update(['name' => $request->name]);
        $permissionIds = $request->input('permissions', []);
        $permissions = Permission::query()->whereIn('id', $permissionIds)->get();
        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
