<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin', only: ['edit', 'update']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax() && request()->has('draw')) {
            $query = User::query()
                ->with(['roles:id,name'])
                ->select(['id', 'name', 'email', 'created_at'])
                ->latest();

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('registration_date', function (User $user) {
                    return optional($user->created_at)->format('d M Y, h:i A');
                })
                ->addColumn('roles', function (User $user) {
                    $roles = $user->roles ?? collect();
                    if ($roles->isEmpty()) {
                        return '<span class="text-sm text-gray-500">-</span>';
                    }

                    return $roles
                        ->sortBy('name')
                        ->map(fn ($role) =>
                            '<span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700 ring-1 ring-inset ring-slate-200">'
                            . e($role->name)
                            . '</span>'
                        )
                        ->implode(' ');
                })
                ->addColumn('actions', function (User $user) {
                    $editUrl = route('users.edit', $user);
                    $profileUrl = route('admin.users.profile.edit', $user);

                    return
                        '<div class="inline-flex items-center gap-2">'
                        . '<a href="' . e($editUrl) . '" class="inline-flex items-center px-3 py-1.5 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition">Edit</a>'
                        . '<a href="' . e($profileUrl) . '" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">Profile</a>'
                        . '</div>';
                })
                ->rawColumns(['roles', 'actions'])
                ->toJson();
        }

        return view('users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::query()->with(['roles:id,name'])->findOrFail($id);
        $roles = Role::query()->orderBy('name')->get(['id', 'name']);
        $userRoleIds = $user->roles->pluck('id')->all();

        return view('users.edit', compact('user', 'roles', 'userRoleIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:roles,id',
        ]);

        $roleIds = $request->input('roles', []);
        $roles = Role::query()->whereIn('id', $roleIds)->get();
        $user->syncRoles($roles);

        return redirect()->route('users.index')->with('success', 'User roles updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
