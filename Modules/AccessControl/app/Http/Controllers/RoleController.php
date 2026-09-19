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
            $query = Role::query()->select(['id', 'name'])->with('permissions:id,name');

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('access', fn (Role $record) => view('accesscontrol::shared.access-preview', ['items' => $record->permissions, 'label' => 'permissions'])->render())
                ->addColumn('actions', fn (Role $record) => view('accesscontrol::shared.actions', ['record' => $record, 'entity' => 'roles'])->render())
                ->rawColumns(['access', 'actions'])
                ->toJson();
        }

        $stats = [
            'roles' => \Spatie\Permission\Models\Role::count(),
            'permissions' => Permission::count(),
            'unassigned' => Permission::doesntHave('roles')->count(),
        ];

        return view('accesscontrol::roles.index', compact('stats'));
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
