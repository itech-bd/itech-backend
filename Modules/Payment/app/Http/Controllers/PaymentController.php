<?php

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Modules\Course\Models\CourseOrder;
use Modules\Payment\Models\Payment;
use Modules\Payment\Services\RecordPayment;

class PaymentController extends Controller
{
    public function index(Request $request, ?Student $student = null)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:191',
            'status' => 'nullable|in:successful,reversed',
            'payment_method' => 'nullable|string|max:50',
            'from' => 'nullable|date_format:Y-m-d',
            'to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('from') ? ['after_or_equal:from'] : [])],
        ]);
        $query = Payment::query()->when($student, fn ($q) => $q->forStudent($student->id));
        $methods = (clone $query)->distinct()->orderBy('payment_method')->pluck('payment_method');
        $query->filtered($filters);
        // Aggregate the entire filtered result, keeping each currency separate.
        $summary = (clone $query)->selectRaw('currency, status, COUNT(*) as transactions, SUM(amount) as total')
            ->groupBy('currency', 'status')->orderBy('currency')->get();
        $payments = $query->with(['courseOrder.student', 'courseOrder.course', 'recordedBy', 'reversedBy'])
            ->latest('id')->paginate(25)->withQueryString();

        return view('payment::index', compact('payments', 'student', 'summary', 'methods'));
    }

    public function create(CourseOrder $order)
    {
        if ($order->status !== 'pending') {
            return redirect()->route('users.payments.index', $order->student_id)
                ->with('success', 'This invoice is not pending. Review its payment history below.');
        }
        $order->load(['student', 'course']);

        return view('payment::create', compact('order'));
    }

    public function store(Request $request, CourseOrder $order, RecordPayment $service)
    {
        $service->record($order, $request->user()->id, $request->only(['payment_method', 'transaction_reference', 'paid_at', 'note']));

        return redirect()->route('dashboard.admin.payments.index')->with('success', 'Payment recorded.');
    }

    public function reverse(Request $request, Payment $payment, RecordPayment $service)
    {
        $data = $request->validate(['reversal_reason' => 'required|string|max:2000']);
        $service->reverse($payment, $request->user()->id, $data['reversal_reason']);

        return back()->with('success', 'Payment reversed; invoice is pending.');
    }
}
