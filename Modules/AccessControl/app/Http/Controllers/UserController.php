<?php

namespace Modules\AccessControl\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Modules\Course\Models\CourseOrder;
use Modules\Invoice\Support\InvoicePdf;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

/**
 * Manage users and roles.
 *
 * @category Controller
 * @package  Modules\AccessControl\Http\Controllers
 * @author   Edu App <support@example.test>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://laravel.com
 */
class UserController extends Controller implements HasMiddleware
{
    /**
     * Controller middleware.
     *
     * @return array
     */
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin', only: ['edit', 'update']),
        ];
    }

    /**
     * List users (DataTables JSON for AJAX; Blade view otherwise).
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        if (request()->ajax() && request()->has('draw')) {
            $roleFilter = request()->string('role')->trim()->lower()->value();
            $allowedRoles = ['student', 'mentor', 'admin'];
            if (!in_array($roleFilter, $allowedRoles, true)) {
                $roleFilter = null;
            }

            $query = User::query()
                ->with(['roles:id,name'])
                ->select(['id', 'name', 'email', 'created_at'])
                ->latest();

            if ($roleFilter) {
                $query->whereHas(
                    'roles',
                    fn ($rolesQuery) => $rolesQuery->where('name', $roleFilter)
                );
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn(
                    'registration_date',
                    fn (User $user) => $this->_renderRegistrationDate($user)
                )
                ->addColumn(
                    'roles',
                    fn (User $user) => $this->_renderRolesBadges($user)
                )
                ->addColumn(
                    'actions',
                    fn (User $user) => $this->_renderActions($user)
                )
                ->rawColumns(['roles', 'actions'])
                ->toJson();
        }

        return view('accesscontrol::users.index');
    }

    /**
     * Store a new user.
     *
     * @param Request $request Incoming request.
     *
     * @return mixed
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show a user.
     *
     * @param string $id User id.
     *
     * @return mixed
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Create a new user.
     *
     * @return mixed
     */
    public function create()
    {
        //
    }

    /**
     * Edit a user's roles.
     *
     * @param string $id User id.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(string $id)
    {
        $user = User::query()->with(['roles:id,name'])->findOrFail($id);
        $roles = Role::query()->orderBy('name')->get(['id', 'name']);
        $userRoleIds = $user->roles->pluck('id')->all();

        return view(
            'accesscontrol::users.edit',
            [
                'user' => $user,
                'roles' => $roles,
                'userRoleIds' => $userRoleIds,
            ]
        );
    }

     /**
      * Update a user's roles.
      *
      * @param Request $request Incoming request.
      * @param string  $id      User id.
      *
      * @return \Illuminate\Http\RedirectResponse
      */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate(
            [
                'roles' => 'nullable|array',
                'roles.*' => 'integer|exists:roles,id',
            ]
        );

        $roleIds = $request->input('roles', []);
        $roles = Role::query()->whereIn('id', $roleIds)->get();
        $user->syncRoles($roles);

        return redirect()
            ->route('users.index')
            ->with('success', 'User roles updated successfully.');
    }

    /**
     * Delete a user.
     *
     * @param string $id User id.
     *
     * @return RedirectResponse
     */
    public function destroy(string $id)
    {
        $user = User::query()->findOrFail($id);

        if (auth()->id() === $user->id) {
            return redirect()
                ->back()
                ->with('error', 'You cannot delete your own account.');
        }

        // Detach roles to avoid orphaned records in model_has_roles.
        $user->syncRoles([]);

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * List a student's invoices (admin view under Users section).
     *
     * @param User    $user    Student user.
     * @param Request $request Incoming request.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function invoices(User $user, Request $request)
    {
        $user->loadMissing(['roles:id,name']);
        abort_unless($user->roles->contains('name', 'student'), 404);

        $status = $request->string('status')->lower()->value();
        $allowedStatuses = ['pending', 'paid', 'cancelled'];
        $activeStatus = in_array($status, $allowedStatuses, true) ? $status : null;

        $ordersQuery = CourseOrder::query()
            ->where('user_id', $user->id)
            ->with(['course:id,title', 'batch:id,name'])
            ->orderByDesc('id');

        if ($activeStatus) {
            $ordersQuery->where('status', $activeStatus);
        }

        $orders = $ordersQuery->paginate(20)->withQueryString();

        return view(
            'accesscontrol::users.invoices.index',
            [
                'student' => $user,
                'orders' => $orders,
                'activeStatus' => $activeStatus,
            ]
        );
    }

     /**
      * View a student's invoice (admin view under Users section).
      *
      * @param User        $user  Student user.
      * @param CourseOrder $order Invoice/order.
      *
      * @return \Illuminate\Contracts\View\View
      */
    public function invoiceShow(User $user, CourseOrder $order)
    {
        $user->loadMissing(['roles:id,name']);
        abort_unless($user->roles->contains('name', 'student'), 404);

        if ((int) $order->user_id !== (int) $user->id) {
            abort(404);
        }

        $order->loadMissing(
            [
                'course',
                'batch',
                'user:id,name,email',
            ]
        );

        return view(
            'accesscontrol::users.invoices.show',
            [
                'student' => $user,
                'order' => $order,
            ]
        );
    }

         /**
            * Download a student's invoice as PDF (admin view under Users section).
            *
            * @param User        $user  Student user.
            * @param CourseOrder $order Invoice/order.
            *
            * @return \Symfony\Component\HttpFoundation\Response
            */
    public function invoiceDownload(User $user, CourseOrder $order)
    {
        $user->loadMissing(['roles:id,name']);
        abort_unless($user->roles->contains('name', 'student'), 404);

        if ((int) $order->user_id !== (int) $user->id) {
            abort(404);
        }

        $order->loadMissing(
            [
                'course',
                'batch',
            ]
        );

        return InvoicePdf::download($order, $user);
    }

    /**
     * Render role badges HTML for DataTables.
     *
     * @param User $user User row.
     *
     * @return string
     */
    private function _renderRolesBadges(User $user): string
    {
        $roles = $user->roles ?? collect();
        if ($roles->isEmpty()) {
            return '<span class="text-sm text-gray-500">-</span>';
        }

        return $roles
            ->sortBy('name')
            ->map(
                fn ($role) => '<span class="inline-flex items-center rounded-md '
                    . 'bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700 '
                    . 'ring-1 ring-inset ring-slate-200">'
                    . e($role->name) . '</span>'
            )
            ->implode(' ');
    }

    /**
     * Render formatted registration date.
     *
     * @param User $user User row.
     *
     * @return string
     */
    private function _renderRegistrationDate(User $user): string
    {
        return optional($user->created_at)->format('d M Y, h:i A');
    }

    /**
     * Render actions HTML for DataTables.
     *
     * @param User $user User row.
     *
     * @return string
     */
    private function _renderActions(User $user): string
    {
        $roleFilter = request()->string('role')->trim()->lower()->value();

        $editUrl = route('users.edit', $user);
        $profileUrl = route('admin.users.profile.edit', $user);

        $invoicesHtml = '';
        if ($roleFilter === 'student') {
            $invoicesUrl = route('users.invoices.index', $user);
            $invoicesHtml = '<a href="' . e($invoicesUrl) . '" class="'
                . 'inline-flex items-center px-3 py-1.5 bg-emerald-600 '
                . 'border border-transparent rounded-md font-semibold text-xs '
                . 'text-white uppercase tracking-widest hover:bg-emerald-500 '
                . 'focus:outline-none focus:ring-2 focus:ring-emerald-500 '
                . 'focus:ring-offset-2 transition">Invoices</a>';
        }

        return '<div class="inline-flex items-center gap-2">'
            . '<a href="' . e($editUrl) . '" class="'
            . 'inline-flex items-center px-3 py-1.5 bg-amber-600 '
            . 'border border-transparent rounded-md font-semibold text-xs '
            . 'text-white uppercase tracking-widest hover:bg-amber-500 '
            . 'focus:outline-none focus:ring-2 focus:ring-amber-500 '
            . 'focus:ring-offset-2 transition">Edit</a>'
            . '<a href="' . e($profileUrl) . '" class="'
            . 'inline-flex items-center px-3 py-1.5 bg-indigo-600 '
            . 'border border-transparent rounded-md font-semibold text-xs '
            . 'text-white uppercase tracking-widest hover:bg-indigo-500 '
            . 'focus:outline-none focus:ring-2 focus:ring-indigo-500 '
            . 'focus:ring-offset-2 transition">Profile</a>'
            . $invoicesHtml
            . '</div>';
    }
}
