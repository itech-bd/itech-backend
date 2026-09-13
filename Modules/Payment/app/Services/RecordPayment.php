<?php

namespace Modules\Payment\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Course\Models\CourseOrder;
use Modules\Payment\Models\Payment;

class RecordPayment
{
    public function record(CourseOrder $order, int $adminId, array $data = []): Payment
    {
        // Validate here so HTTP, console and future gateway callers share the same rules.
        $data = Validator::make($data, [
            'payment_method' => ['sometimes', 'required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'transaction_reference' => 'nullable|string|max:191',
            'paid_at' => 'nullable|date|before_or_equal:now',
            'note' => 'nullable|string|max:2000',
        ])->validate();

        return DB::transaction(function () use ($order, $adminId, $data) {
            $order = CourseOrder::query()->lockForUpdate()->findOrFail($order->id);
            $existing = $order->payments()->where('status', 'successful')->first();
            if ($existing) {
                $order->update(['status' => 'paid']);

                return $existing;
            }
            if ($order->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Only pending invoices can receive a payment.']);
            }
            $payment = new Payment;
            $payment->forceFill([
                'course_order_id' => $order->id,
                'amount' => $order->amount,
                'currency' => $order->currency,
                'payment_method' => $data['payment_method'] ?? 'manual',
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'note' => $data['note'] ?? null,
                'paid_at' => $data['paid_at'] ?? now(),
                'recorded_by' => $adminId,
                'status' => 'successful',
                'success_slot' => 1,
            ])->save();
            $order->update(['status' => 'paid']);

            return $payment;
        }, 3);
    }

    public function reverse(Payment $payment, int $adminId, string $reason): void
    {
        $reason = trim($reason);
        Validator::make(['reversal_reason' => $reason], ['reversal_reason' => 'required|string|max:2000'])->validate();
        DB::transaction(function () use ($payment, $adminId, $reason) {
            $order = CourseOrder::query()->lockForUpdate()->findOrFail($payment->course_order_id);
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            if ($payment->status === 'reversed') {
                return;
            }
            if ($payment->status !== 'successful') {
                throw ValidationException::withMessages(['status' => 'Only successful payments can be reversed.']);
            }
            $payment->forceFill([
                'status' => 'reversed', 'success_slot' => null,
                'reversed_at' => now(), 'reversed_by' => $adminId, 'reversal_reason' => $reason,
            ])->save();
            $order->update(['status' => 'pending']);
        }, 3);
    }
}
