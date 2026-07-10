<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Api\V1\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Course\Models\CourseOrder;
use Modules\Invoice\Support\InvoicePdf;
use Symfony\Component\HttpFoundation\Response;

class StudentInvoiceController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $status = strtolower(trim((string) $request->query('status', '')));
        $allowed = ['pending', 'paid', 'cancelled'];

        $query = CourseOrder::query()
            ->where('user_id', $request->user()->id)
            ->with(['course:id,title,slug', 'batch:id,name'])
            ->latest('id');

        if (in_array($status, $allowed, true)) {
            $query->where('status', $status);
        }

        $paginator = $query->paginate(min(max($request->integer('per_page', 12), 1), 50));

        return $this->success([
            ...$this->paginated($paginator, fn (CourseOrder $order) => $this->orderPayload($order)),
            'filters' => ['status' => in_array($status, $allowed, true) ? $status : null],
        ]);
    }

    public function show(Request $request, CourseOrder $order): JsonResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);

        $order->loadMissing(['course', 'batch', 'user:id,name,email']);

        return $this->success($this->orderPayload($order, true));
    }

    public function download(Request $request, CourseOrder $order): Response
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);

        $order->loadMissing(['course', 'batch', 'user:id,name,email']);

        return InvoicePdf::download($order, $request->user());
    }

    private function orderPayload(CourseOrder $order, bool $detailed = false): array
    {
        $payload = [
            'id' => $order->id,
            'status' => $order->status,
            'amount' => (float) $order->amount,
            'currency' => $order->currency,
            'batch_type' => $order->batch_type,
            'created_at' => $order->created_at?->toIso8601String(),
            'updated_at' => $order->updated_at?->toIso8601String(),
            'download_path' => "/api/v1/student/invoices/{$order->id}/download",
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

        if ($detailed) {
            $payload['student'] = $order->user ? [
                'id' => $order->user->id,
                'name' => $order->user->name,
                'email' => $order->user->email,
            ] : null;
        }

        return $payload;
    }
}
