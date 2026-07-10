<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Batch\Models\Batch;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseOrder;

class CheckoutController extends ApiController
{
    public function preview(Request $request, Course $course): JsonResponse
    {
        abort_unless($course->status === 'active', 404);

        $course->load([
            'batches' => fn ($query) => $query
                ->whereIn('status', ['upcoming', 'running'])
                ->orderBy('start_date'),
        ]);

        $batchIds = $course->batches->pluck('id');
        $joined = DB::table('batch_students')
            ->where('student_id', $request->user()->id)
            ->whereIn('batch_id', $batchIds)
            ->get(['batch_id', 'status', 'batch_type']);

        return $this->success([
            'course' => $this->coursePayload($course),
            'requires_batch_type' => $this->hasOnlineOfflinePricing($course),
            'default_amount' => $this->courseAmount($course),
            'batches' => $course->batches->map(fn (Batch $batch) => [
                'id' => $batch->id,
                'name' => $batch->name,
                'status' => $batch->status,
                'start_date' => $batch->start_date?->toDateString(),
                'end_date' => $batch->end_date?->toDateString(),
                'class_days' => $batch->class_days ?: [],
                'class_time' => $batch->class_time,
            ])->values(),
            'joined_batches' => $joined,
        ]);
    }

    public function store(Request $request, Course $course): JsonResponse
    {
        abort_unless($course->status === 'active', 404);

        $requiresType = $this->hasOnlineOfflinePricing($course);
        $data = $request->validate([
            'batch_id' => ['nullable', 'integer'],
            'batch_type' => $requiresType
                ? ['required', 'in:online,offline']
                : ['nullable', 'in:online,offline'],
        ]);

        $userId = (int) $request->user()->id;
        $batchId = (int) ($data['batch_id'] ?? 0);
        $batchType = $data['batch_type'] ?? null;

        $hasAvailableBatches = Batch::query()
            ->where('course_id', $course->id)
            ->whereIn('status', ['upcoming', 'running'])
            ->exists();

        if ($hasAvailableBatches && $batchId <= 0) {
            return $this->failure('Please select a batch.', 422, 'BATCH_REQUIRED', [
                'batch_id' => ['Please select a batch.'],
            ]);
        }

        $batch = null;
        if ($batchId > 0) {
            $batch = Batch::query()
                ->whereKey($batchId)
                ->where('course_id', $course->id)
                ->whereIn('status', ['upcoming', 'running'])
                ->firstOrFail();

            $joined = DB::table('batch_students')
                ->where('batch_id', $batch->id)
                ->where('student_id', $userId)
                ->first();

            if ($joined) {
                $existingSameOrder = CourseOrder::query()
                    ->where('user_id', $userId)
                    ->where('course_id', $course->id)
                    ->where('batch_id', $batch->id)
                    ->where('status', 'pending')
                    ->latest('id')
                    ->first();

                if ($existingSameOrder) {
                    $existingSameOrder->load(['course', 'batch']);
                    return $this->success($this->orderPayload($existingSameOrder), 'Existing pending order returned.');
                }

                return $this->failure(
                    'You already joined this batch. Please select another batch.',
                    409,
                    'BATCH_ALREADY_JOINED',
                    ['batch_id' => ['You already joined this batch.']]
                );
            }
        }

        $amount = $this->courseAmount($course, $batchType);

        $order = DB::transaction(function () use ($course, $batch, $batchType, $amount, $userId): CourseOrder {
            $existing = CourseOrder::query()
                ->where('user_id', $userId)
                ->where('course_id', $course->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($existing) {
                $oldBatchId = $existing->batch_id ? (int) $existing->batch_id : null;
                $newBatchId = $batch?->id;

                $existing->update([
                    'batch_id' => $newBatchId,
                    'batch_type' => $batchType,
                    'amount' => $amount,
                    'currency' => 'BDT',
                ]);

                if ($oldBatchId && $oldBatchId !== $newBatchId) {
                    DB::table('batch_students')
                        ->where('batch_id', $oldBatchId)
                        ->where('student_id', $userId)
                        ->where('status', 'pending')
                        ->delete();
                }

                if ($batch) {
                    $this->ensurePendingEnrollment($batch->id, $userId, $batchType);
                }

                return $existing->refresh();
            }

            $created = CourseOrder::query()->create([
                'user_id' => $userId,
                'course_id' => $course->id,
                'batch_id' => $batch?->id,
                'batch_type' => $batchType,
                'amount' => $amount,
                'currency' => 'BDT',
                'status' => 'pending',
            ]);

            if ($batch) {
                $this->ensurePendingEnrollment($batch->id, $userId, $batchType);
            }

            return $created;
        });

        $order->load(['course', 'batch']);

        return $this->success($this->orderPayload($order), 'Pending order created.', 201);
    }

    public function order(Request $request, CourseOrder $order): JsonResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        $order->load(['course', 'batch']);

        return $this->success($this->orderPayload($order));
    }

    private function ensurePendingEnrollment(int $batchId, int $studentId, ?string $batchType): void
    {
        $query = DB::table('batch_students')->where([
            'batch_id' => $batchId,
            'student_id' => $studentId,
        ]);

        if ($query->exists()) {
            $query->update([
                'status' => 'pending',
                'batch_type' => $batchType,
                'approved_at' => null,
                'approved_by' => null,
                'updated_at' => now(),
            ]);

            return;
        }

        DB::table('batch_students')->insert([
            'batch_id' => $batchId,
            'student_id' => $studentId,
            'status' => 'pending',
            'batch_type' => $batchType,
            'approved_at' => null,
            'approved_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function courseAmount(Course $course, ?string $batchType = null): float
    {
        if ($batchType === 'online') {
            return (float) ($course->online_discount_price ?? $course->online_old_price ?? $course->discount_price ?? $course->old_price ?? 0);
        }

        if ($batchType === 'offline') {
            return (float) ($course->offline_discount_price ?? $course->offline_old_price ?? $course->discount_price ?? $course->old_price ?? 0);
        }

        return (float) ($course->discount_price ?? $course->old_price ?? 0);
    }

    private function hasOnlineOfflinePricing(Course $course): bool
    {
        return ! is_null($course->online_old_price)
            || ! is_null($course->online_discount_price)
            || ! is_null($course->offline_old_price)
            || ! is_null($course->offline_discount_price);
    }

    private function coursePayload(Course $course): array
    {
        return [
            'id' => $course->id,
            'slug' => $course->slug,
            'title' => $course->title,
            'thumbnail_url' => $course->thumbnail_url,
            'pricing' => [
                'old_price' => is_null($course->old_price) ? null : (float) $course->old_price,
                'discount_price' => is_null($course->discount_price) ? null : (float) $course->discount_price,
                'online_old_price' => is_null($course->online_old_price) ? null : (float) $course->online_old_price,
                'online_discount_price' => is_null($course->online_discount_price) ? null : (float) $course->online_discount_price,
                'offline_old_price' => is_null($course->offline_old_price) ? null : (float) $course->offline_old_price,
                'offline_discount_price' => is_null($course->offline_discount_price) ? null : (float) $course->offline_discount_price,
                'currency' => 'BDT',
            ],
        ];
    }

    private function orderPayload(CourseOrder $order): array
    {
        return [
            'id' => $order->id,
            'status' => $order->status,
            'amount' => (float) $order->amount,
            'currency' => $order->currency,
            'batch_type' => $order->batch_type,
            'created_at' => $order->created_at?->toIso8601String(),
            'course' => $order->course ? [
                'id' => $order->course->id,
                'slug' => $order->course->slug,
                'title' => $order->course->title,
            ] : null,
            'batch' => $order->batch ? [
                'id' => $order->batch->id,
                'name' => $order->batch->name,
            ] : null,
        ];
    }
}
