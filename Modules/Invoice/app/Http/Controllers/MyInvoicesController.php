<?php

namespace Modules\Invoice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Course\Models\CourseOrder;
use Modules\Invoice\Support\InvoicePdf;

/**
 * Student invoices controller.
 *
 * @category Controller
 * @package  Modules\Invoice\Http\Controllers
 * @author   Edu App <support@example.test>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://laravel.com
 */
class MyInvoicesController extends Controller
{
    /**
     * List the authenticated student's invoices.
     *
     * @param Request $request Incoming request.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

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

        $orders = $ordersQuery->paginate(12)->withQueryString();

        return view(
            'invoice::student.invoices.index',
            [
                'orders' => $orders,
                'activeStatus' => $activeStatus,
            ]
        );
    }

    /**
     * Show a single invoice (printable).
     *
     * @param CourseOrder $order Order to display.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function show(CourseOrder $order)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        abort_unless((int) $order->user_id === (int) $user->id, 403);

        $order->loadMissing(
            [
                'course',
                'batch',
                'user:id,name,email',
            ]
        );

        return view(
            'invoice::student.invoices.show',
            [
                'order' => $order,
                'user' => $user,
            ]
        );
    }

    /**
     * Download an invoice as a PDF file.
     *
     * @param CourseOrder $order Order to download.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(CourseOrder $order)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        abort_unless((int) $order->user_id === (int) $user->id, 403);

        $order->loadMissing(
            [
                'course',
                'batch',
                'user:id,name,email',
            ]
        );

        return InvoicePdf::download($order, $user);
    }
}
