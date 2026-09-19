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
            'output' => 'nullable|in:csv,print',
        ]);
        $output = $filters['output'] ?? null;
        unset($filters['output']);
        $query = Payment::query()->when($student, fn ($q) => $q->forStudent($student->id));
        $methods = (clone $query)->distinct()->orderBy('payment_method')->pluck('payment_method');
        $query->filtered($filters);
        // Aggregate the entire filtered result, keeping each currency separate.
        $summary = (clone $query)->selectRaw('currency, status, COUNT(*) as transactions, SUM(amount) as total')
            ->groupBy('currency', 'status')->orderBy('currency')->get();
        $query->with(['courseOrder.student', 'courseOrder.course'])->latest('id');
        if ($output === 'csv') {
            return response()->streamDownload(function () use ($query) {
                $stream = fopen('php://output', 'w');
                // UTF-8 BOM preserves student names when opened in Excel.
                fwrite($stream, "\xEF\xBB\xBF");
                fputcsv($stream, ['Payment', 'Payment time ('.config('app.timezone').')', 'Time accuracy', 'Student', 'Email', 'Invoice', 'Currency', 'Amount', 'Method', 'Reference', 'Status', 'Reversed at ('.config('app.timezone').')'], ',', '"', '');
                foreach ($query->lazy(500) as $payment) {
                    $row = [
                        '#PAY-'.$payment->id, $payment->paid_at?->format('Y-m-d H:i:s') ?? 'Unknown',
                        $payment->payment_method === 'legacy' ? 'Estimated' : ($payment->paid_at ? 'Recorded' : 'Unknown'),
                        $payment->courseOrder?->student?->name, $payment->courseOrder?->student?->email,
                        '#INV-'.$payment->course_order_id, $payment->currency, $payment->amount,
                        $payment->payment_method === 'legacy' ? 'Previous invoice' : $payment->payment_method,
                        $payment->transaction_reference, $payment->status, $payment->reversed_at?->format('Y-m-d H:i:s'),
                    ];
                    // Treat user-entered values as text, never spreadsheet formulas.
                    $row = array_map(static function ($value) {
                        $value = (string) ($value ?? '');
                        return preg_match('/^[\s\x{FEFF}]*[=+@-]/u', $value) || preg_match('/^[\t\r\n]/', $value) ? "'".$value : $value;
                    }, $row);
                    fputcsv($stream, $row, ',', '"', '');
                }
                fclose($stream);
            }, 'payment-report-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
        }
        $printReport = $output === 'print';
        $payments = $printReport ? $query->lazy(500) : $query->paginate(25)->withQueryString();
        $transactionCount = (int) $summary->sum('transactions');

        return view($printReport ? 'payment::report-print' : 'payment::index', compact('payments', 'student', 'summary', 'methods', 'filters', 'printReport', 'transactionCount'));
    }

    public function create(CourseOrder $order)
    {
        if ($order->status !== 'pending') {
            return redirect()->route('dashboard.admin.invoices.show', $order)
                ->with('success', 'This invoice is not pending. Review its payment history below.');
        }
        $order->load(['student', 'course']);

        return view('payment::create', compact('order'));
    }

    public function store(Request $request, CourseOrder $order, RecordPayment $service)
    {
        $service->record($order, $request->user()->id, $request->only(['payment_method', 'transaction_reference', 'paid_at', 'note']));

        return redirect()->route('dashboard.admin.invoices.show', $order)->with('success', 'Payment recorded.');
    }

    public function reverse(Request $request, Payment $payment, RecordPayment $service)
    {
        $data = $request->validate(['reversal_reason' => 'required|string|max:2000']);
        $service->reverse($payment, $request->user()->id, $data['reversal_reason']);

        return back()->with('success', 'Payment reversed; invoice is pending.');
    }
}
