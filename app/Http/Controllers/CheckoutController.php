<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Batch\Models\Batch;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseOrder;

class CheckoutController extends Controller
{
    /**
     * Show the checkout page for a selected course.
     */
    public function show(Course $course): View
    {
        abort_unless($course->status === 'active', 404);

        $userId = (int) Auth::id();
        abort_unless($userId > 0, 403);

        $course->load([
            'batches' => function ($query) {
                $query
                    ->whereIn('status', ['upcoming', 'running'])
                    ->orderBy('start_date');
            },
        ]);

        $hasOnlineOfflinePricing = $this->hasOnlineOfflinePricing($course);
        $amount = $this->courseAmount($course);

        $joinedBatchIds = [];
        if ($course->relationLoaded('batches') && $course->batches->count() > 0) {
            $batchIds = $course->batches->pluck('id')->map(fn ($id) => (int) $id)->all();
            $joinedBatchIds = DB::table('batch_students')
                ->where('student_id', $userId)
                ->whereIn('batch_id', $batchIds)
                ->pluck('batch_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return view('pages.checkout', compact('course', 'amount', 'joinedBatchIds', 'hasOnlineOfflinePricing'));
    }

    /**
     * Create a pending order for the selected course.
     */
    public function store(Request $request, Course $course): RedirectResponse
    {
        abort_unless($course->status === 'active', 404);

        $userId = (int) Auth::id();
        abort_unless($userId > 0, 403);

        $hasOnlineOfflinePricing = $this->hasOnlineOfflinePricing($course);

        $validated = $request->validate([
            'batch_id' => ['nullable', 'integer'],
            'batch_type' => $hasOnlineOfflinePricing
                ? ['required', 'in:online,offline']
                : ['nullable', 'in:online,offline'],
        ]);

        $batchType = $validated['batch_type'] ?? null;
        $amount = $this->courseAmount($course, $batchType);

        $batchId = (int) ($validated['batch_id'] ?? 0);

        $hasAvailableBatches = Batch::query()
            ->where('course_id', $course->id)
            ->whereIn('status', ['upcoming', 'running'])
            ->exists();

        if ($hasAvailableBatches && $batchId <= 0) {
            return redirect()
                ->back()
                ->withErrors(['batch_id' => 'Please select a batch.'])
                ->withInput();
        }

        $selectedBatch = null;
        if ($batchId > 0) {
            $selectedBatch = Batch::query()
                ->whereKey($batchId)
                ->where('course_id', $course->id)
                ->whereIn('status', ['upcoming', 'running'])
                ->firstOrFail();
        }

        $existing = CourseOrder::query()
            ->where('user_id', $userId)
            ->where('course_id', $course->id)
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->first();

        if ($selectedBatch) {
            $alreadyJoined = DB::table('batch_students')
                ->where('batch_id', $selectedBatch->id)
                ->where('student_id', $userId)
                ->exists();

            // If the user already joined this batch, don't allow creating a second enrollment.
            if ($alreadyJoined) {
                if ($existing && (int) $existing->batch_id === (int) $selectedBatch->id) {
                    return redirect()->route('checkout.success', $existing);
                }

                return redirect()
                    ->back()
                    ->withErrors(['batch_id' => 'You already joined this batch. Please select another batch.'])
                    ->withInput();
            }
        }

        if ($existing) {
            if ($selectedBatch && (int) $existing->batch_id !== (int) $selectedBatch->id) {
                $existing->update(['batch_id' => $selectedBatch->id, 'batch_type' => $batchType]);
            } elseif ($batchType !== null && $existing->batch_type !== $batchType) {
                $existing->update(['batch_type' => $batchType]);
            }

            if ($selectedBatch) {
                $this->ensurePendingEnrollment($selectedBatch->id, $userId, $batchType);
            }

            return redirect()->route('checkout.success', $existing);
        }

        $order = CourseOrder::query()->create([
            'user_id' => $userId,
            'course_id' => $course->id,
            'batch_id' => $selectedBatch?->id,
            'batch_type' => $batchType,
            'amount' => $amount,
            'currency' => 'BDT',
            'status' => 'pending',
        ]);

        if ($selectedBatch) {
            $this->ensurePendingEnrollment($selectedBatch->id, $userId, $batchType);
        }

        return redirect()->route('checkout.success', $order);
    }

    /**
     * Show checkout success / order summary page.
     */
    public function success(CourseOrder $order): View
    {
        $userId = (int) Auth::id();
        abort_unless($userId > 0, 403);
        abort_unless((int) $order->user_id === $userId, 403);

        $order->load('course');

        return view('pages.checkout-success', compact('order'));
    }

    private function courseAmount(Course $course, ?string $batchType = null): float
    {
        if ($batchType === 'online') {
            $discount = $course->online_discount_price;
            if (!is_null($discount)) return (float) $discount;
            $old = $course->online_old_price;
            if (!is_null($old)) return (float) $old;
        } elseif ($batchType === 'offline') {
            $discount = $course->offline_discount_price;
            if (!is_null($discount)) return (float) $discount;
            $old = $course->offline_old_price;
            if (!is_null($old)) return (float) $old;
        }

        // Fallback to general pricing
        $discount = $course->discount_price;
        if (!is_null($discount)) {
            return (float) $discount;
        }

        $old = $course->old_price;
        if (!is_null($old)) {
            return (float) $old;
        }

        return 0.0;
    }

    private function hasOnlineOfflinePricing(Course $course): bool
    {
        return !is_null($course->online_old_price)
            || !is_null($course->online_discount_price)
            || !is_null($course->offline_old_price)
            || !is_null($course->offline_discount_price);
    }

    private function ensurePendingEnrollment(int $batchId, int $studentId, ?string $batchType = null): void
    {
        $existing = DB::table('batch_students')
            ->where('batch_id', $batchId)
            ->where('student_id', $studentId)
            ->first();

        if ($existing) {
            if ($batchType !== null && ($existing->batch_type ?? null) !== $batchType) {
                DB::table('batch_students')
                    ->where('batch_id', $batchId)
                    ->where('student_id', $studentId)
                    ->update(['batch_type' => $batchType, 'updated_at' => now()]);
            }
            return;
        }

        DB::table('batch_students')->insert([
            'batch_id' => $batchId,
            'student_id' => $studentId,
            'status' => 'pending',
            'batch_type' => $batchType,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
