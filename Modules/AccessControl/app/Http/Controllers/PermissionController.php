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
        if (request()->ajax()) {
            $query = Permission::query()->select(['id', 'name']);

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('actions', function (Permission $permission) {
                    $editUrl = route('permissions.edit', $permission);
                    $deleteUrl = route('permissions.destroy', $permission);

                    return
                        '<div class="inline-flex items-center gap-2">'
                        . '<a href="' . e($editUrl) . '" class="inline-flex items-center px-3 py-1.5 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition">Edit</a>'
                        . '<button type="button"'
                        . ' class="js-permission-delete inline-flex items-center px-3 py-1.5 bg-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 transition"'
                        . ' data-delete-url="' . e($deleteUrl) . '"'
                        . ' data-permission-name="' . e($permission->name) . '"'
                        . '>Delete</button>'
                        . '</div>';
                })
                ->rawColumns(['actions'])
                ->toJson();
        }

        return view('accesscontrol::permissions.index');
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
