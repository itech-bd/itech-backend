<?php

namespace Modules\Invoice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Course\Models\CourseOrder;
use Modules\Invoice\Support\InvoicePdf;
use Yajra\DataTables\Facades\DataTables;

/**
 * Admin invoices controller.
 *
 * Invoice status is financial only and independent
 * from batch status.
 *
 * @category Controller
 * @package  Modules\Invoice\Http\Controllers
 * @author   Edu App <support@example.test>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://laravel.com
 */
class AdminInvoicesController extends Controller
{
    /**
     * List all invoices.
     *
     * For normal requests, returns the Blade view.
     * For DataTables AJAX requests, returns DataTables JSON.
     *
     * @param Request $request Incoming request.
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($request->has('draw')) {
            return $this->_indexDataTable($request);
        }

        $status = $request->string('status')->lower()->value();
        $allowed = ['pending', 'completed'];
        $activeStatus = in_array($status, $allowed, true) ? $status : null;

        $balances = CourseOrder::query()->whereIn('status', ['pending', 'paid'])
            ->withSum(['payments as received' => fn ($query) => $query->where('status', 'successful')], 'amount');
        $summary = DB::query()->fromSub($balances, 'balances')
            ->selectRaw('currency, SUM(COALESCE(received, 0)) as received, SUM(CASE WHEN status = ? THEN amount - COALESCE(received, 0) ELSE 0 END) as due, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count', ['pending', 'pending'])
            ->groupBy('currency')->orderBy('currency')->get();

        return view(
            'invoice::admin.invoices.index',
            [
                'activeStatus' => $activeStatus,
                'summary' => $summary,
            ]
        );
    }

    /**
     * DataTables JSON for the admin invoices listing.
     *
     * @param Request $request Incoming request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function _indexDataTable(Request $request)
    {
        $status = $request->string('status')->lower()->value();
        $allowed = ['pending', 'completed'];
        $activeStatus = in_array($status, $allowed, true) ? $status : null;

        $ordersQuery = CourseOrder::query()
            ->with(['user:id,name,email', 'course:id,title', 'batch:id,name'])
            ->select(['course_orders.*'])
            ->withSum(['payments as received' => fn ($query) => $query->where('status', 'successful')], 'amount')
            ->whereIn('status', ['pending', 'paid'])
            ->orderByDesc('id');

        if ($activeStatus === 'pending') {
            $ordersQuery->where('status', 'pending');
        }

        if ($activeStatus === 'completed') {
            $ordersQuery->where('status', 'paid');
        }

        return DataTables::eloquent($ordersQuery)
            ->addIndexColumn()
            ->addColumn(
                'invoice',
                fn (CourseOrder $order) => '#INV-'.(string) $order->id
            )
            ->addColumn(
                'student',
                fn (CourseOrder $order) => $this->_renderStudentCell($order)
            )
            ->addColumn(
                'course',
                fn (CourseOrder $order) => e($order->course?->title ?? '—')
            )
            ->addColumn(
                'batch',
                fn (CourseOrder $order) => e($order->batch?->name ?? '—')
            )
            ->addColumn(
                'status',
                fn (CourseOrder $order) => $this->_renderStatusBadge($order)
            )
            ->addColumn(
                'total',
                fn (CourseOrder $order) => e($order->currency)
                    . ' '
                    . number_format((float) $order->amount, 2)
            )
            ->addColumn(
                'received',
                fn (CourseOrder $order) => e($order->currency).' '.number_format((float) $order->received, 2)
            )
            ->addColumn(
                'due',
                fn (CourseOrder $order) => e($order->currency).' '.number_format($order->status === 'pending' ? max(0, (float) $order->amount - (float) $order->received) : 0, 2)
            )
            ->addColumn(
                'date',
                fn (CourseOrder $order) => optional($order->created_at)
                    ->format('d M Y, h:i A')
            )
            ->addColumn(
                'actions',
                fn (CourseOrder $order) => $this->_renderActions($order)
            )
            ->filterColumn(
                'student',
                function ($query, $keyword) {
                    $query->whereHas(
                        'user',
                        fn ($q) => $q
                            ->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                    );
                }
            )
            ->filterColumn(
                'course',
                function ($query, $keyword) {
                    $query->whereHas(
                        'course',
                        fn ($q) => $q->where('title', 'like', "%{$keyword}%")
                    );
                }
            )
            ->filterColumn(
                'batch',
                function ($query, $keyword) {
                    $query->whereHas(
                        'batch',
                        fn ($q) => $q->where('name', 'like', "%{$keyword}%")
                    );
                }
            )
            ->filterColumn(
                'invoice',
                function ($query, $keyword) {
                    $numeric = preg_replace('/[^0-9]/', '', (string) $keyword);
                    if ($numeric !== '') {
                        $query->where('id', 'like', "%{$numeric}%");
                        return;
                    }

                    $query->where('id', 'like', "%{$keyword}%");
                }
            )
                ->rawColumns(['student', 'status', 'actions'])
            ->toJson();
    }

    /**
     * Render student cell HTML.
     *
     * @param CourseOrder $order Order row.
     *
     * @return string
     */
    private function _renderStudentCell(CourseOrder $order): string
    {
        $name = $order->user?->name;
        $email = $order->user?->email;

        if (! $name && ! $email) {
            return '<span class="text-slate-500">—</span>';
        }

        $html = '';
        if ($name) {
            $html .= '<div class="font-medium text-slate-900">'
                . e($name)
                . '</div>';
        }

        if ($email) {
            $html .= '<div class="text-xs text-slate-500">'
                . e($email)
                . '</div>';
        }

        return $html;
    }

    /**
     * Render status badge HTML (Pending/Completed).
     *
     * @param CourseOrder $order Order row.
     *
     * @return string
     */
    private function _renderStatusBadge(CourseOrder $order): string
    {
        $isCompleted = $order->status === 'paid';
        $label = $isCompleted ? 'Paid' : 'Due';
        $badge = $isCompleted
            ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
            : 'bg-amber-50 text-amber-700 ring-amber-200';

        return '<span class="inline-flex items-center rounded-md px-2 py-1 '
            . 'text-xs font-semibold ring-1 ring-inset '
            . e($badge)
            . '">'
            . e($label)
            . '</span>';
    }

    /**
     * Render invoice actions HTML.
     *
     * @param CourseOrder $order Order row.
     *
     * @return string
     */
    private function _renderActions(CourseOrder $order): string
    {
        $html = '<div class="flex items-center justify-end gap-2">'
            . '<a href="'.e(route('dashboard.admin.invoices.show', $order)).'" class="inline-flex items-center justify-center whitespace-nowrap rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-100 hover:text-indigo-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">View invoice</a>';
        if ($order->status === 'pending') {
            $html .= '<a href="'.e(route('dashboard.admin.payments.create', $order)).'" class="rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Record payment</a>';
        }

        return $html.'</div>';
    }

    public function show(CourseOrder $order): View
    {
        $order->load(['student', 'course', 'batch', 'payments' => fn ($query) => $query->latest('id'), 'payments.recordedBy', 'payments.reversedBy']);
        abort_unless($order->student, 404);

        return view('accesscontrol::users.invoices.show', [
            'order' => $order,
            'student' => $order->student,
            'backUrl' => route('dashboard.admin.invoices.index'),
        ]);
    }

    public function download(CourseOrder $order)
    {
        $order->loadMissing(
            [
                'course',
                'batch',
                'user:id,name,email',
            ]
        );

        $user = $order->user;
        abort_unless($user !== null, 404);

        return InvoicePdf::download($order, $user);
    }

    /**
     * Compatibility endpoint for recording a manual payment.
     *
     * Paid-to-pending changes require an explicit payment reversal.
     * Persists "completed" as "paid".
     *
     * @param Request     $request Incoming request.
     * @param CourseOrder $order   Invoice/order.
     *
     * @return RedirectResponse
     */
    public function updateStatus(Request $request, CourseOrder $order)
    {
        $request->validate(
            [
                'status' => ['required', 'string', 'in:pending,completed,paid'],
            ]
        );

        if ($request->input('status') === 'pending') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'status' => 'Open the invoice and reverse its payment with a reason to return it to pending.',
            ]);
        }
        app(\Modules\Payment\Services\RecordPayment::class)->record($order, $request->user()->id);

        return back()->with('success', 'Payment recorded successfully.');
    }
}
