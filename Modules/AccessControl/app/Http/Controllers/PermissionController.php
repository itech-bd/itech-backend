<?php

namespace Modules\AccessControl\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller implements HasMiddleware
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
            $query = Permission::query()->select(['id', 'name'])->with('roles:id,name');

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('access', fn (Permission $record) => view('accesscontrol::shared.access-preview', ['items' => $record->roles, 'label' => 'roles'])->render())
                ->addColumn('actions', fn (Permission $record) => view('accesscontrol::shared.actions', ['record' => $record, 'entity' => 'permissions'])->render())
                ->rawColumns(['access', 'actions'])
                ->toJson();
        }

        $stats = [
            'roles' => \Spatie\Permission\Models\Role::count(),
            'permissions' => Permission::count(),
            'unassigned' => Permission::doesntHave('roles')->count(),
        ];

        return view('accesscontrol::permissions.index', compact('stats'));
    }
    public function create()
    {
        return view('accesscontrol::permissions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        Permission::create(['name' => $request->name]);

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $permission = Permission::findOrFail($id);

        return view('accesscontrol::permissions.edit', compact('permission'));
    }

    public function update(Request $request, string $id)
    {
        $permission = Permission::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update(['name' => $request->name]);

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(string $id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
