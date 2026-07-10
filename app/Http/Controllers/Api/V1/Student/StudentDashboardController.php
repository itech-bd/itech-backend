<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Api\V1\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Batch\Models\Batch;
use Modules\Batch\Models\ClassSchedule;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseOrder;

class StudentDashboardController extends ApiController
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $enrollments = DB::table('batch_students')
            ->where('student_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->get(['batch_id', 'status', 'batch_type', 'approved_at']);

        $batchIds = $enrollments->pluck('batch_id');
        $approvedBatchIds = $enrollments->where('status', 'approved')->pluck('batch_id');

        $stats = [
            'courses' => Course::query()
                ->whereHas('batches.students', fn ($query) => $query
                    ->where('users.id', $user->id)
                    ->whereIn('batch_students.status', ['pending', 'approved']))
                ->count(),
            'batches' => $batchIds->count(),
            'approved_batches' => $approvedBatchIds->count(),
            'pending_batches' => $enrollments->where('status', 'pending')->count(),
            'paid_invoices' => CourseOrder::query()
                ->where('user_id', $user->id)
                ->where('status', 'paid')
                ->count(),
            'paid_amount' => (float) CourseOrder::query()
                ->where('user_id', $user->id)
                ->where('status', 'paid')
                ->sum('amount'),
        ];

        $upcomingSchedules = ClassSchedule::query()
            ->with('batch.course:id,title,slug')
            ->whereIn('batch_id', $approvedBatchIds)
            ->whereDate('class_date', '>=', now()->toDateString())
            ->orderBy('class_date')
            ->limit(6)
            ->get()
            ->map(fn (ClassSchedule $schedule) => [
                'id' => $schedule->id,
                'class_date' => $schedule->class_date?->toDateString(),
                'topic' => $schedule->topic,
                'live_class_link' => $schedule->live_class_link ?: $schedule->batch?->live_class_link,
                'recorded_video_link' => $schedule->recorded_video_link,
                'batch' => $schedule->batch ? [
                    'id' => $schedule->batch->id,
                    'name' => $schedule->batch->name,
                    'course' => $schedule->batch->course ? [
                        'id' => $schedule->batch->course->id,
                        'slug' => $schedule->batch->course->slug,
                        'title' => $schedule->batch->course->title,
                    ] : null,
                ] : null,
            ])->values();

        $recentOrders = CourseOrder::query()
            ->where('user_id', $user->id)
            ->with(['course:id,title,slug', 'batch:id,name'])
            ->latest('id')
            ->limit(4)
            ->get()
            ->map(fn (CourseOrder $order) => [
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
            ])->values();

        $enrollmentByBatch = $enrollments->keyBy('batch_id');
        $recentBatches = Batch::query()
            ->whereIn('id', $batchIds)
            ->with('course:id,title,slug,thumbnail')
            ->withCount(['classSchedules', 'mentors'])
            ->latest('id')
            ->limit(4)
            ->get()
            ->map(function (Batch $batch) use ($enrollmentByBatch): array {
                $enrollment = $enrollmentByBatch->get($batch->id);

                return [
                    'id' => $batch->id,
                    'name' => $batch->name,
                    'status' => $batch->status,
                    'start_date' => $batch->start_date?->toDateString(),
                    'end_date' => $batch->end_date?->toDateString(),
                    'class_time' => $batch->class_time,
                    'class_days' => $batch->class_days ?: [],
                    'enrollment' => $enrollment ? [
                        'status' => $enrollment->status,
                        'batch_type' => $enrollment->batch_type,
                        'approved_at' => $enrollment->approved_at,
                    ] : null,
                    'class_schedules_count' => $batch->class_schedules_count,
                    'mentors_count' => $batch->mentors_count,
                    'course' => $batch->course ? [
                        'id' => $batch->course->id,
                        'slug' => $batch->course->slug,
                        'title' => $batch->course->title,
                        'thumbnail_url' => $batch->course->thumbnail_url,
                    ] : null,
                ];
            })->values();

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_image_url' => $user->profile_image_url,
            ],
            'menu' => [
                ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => '/student'],
                ['key' => 'profile', 'label' => 'Profile', 'href' => '/student/profile'],
                ['key' => 'courses', 'label' => 'My Courses', 'href' => '/student/courses'],
                ['key' => 'batches', 'label' => 'My Batches', 'href' => '/student/batches'],
                ['key' => 'mentors', 'label' => 'My Mentors', 'href' => '/student/mentors'],
                ['key' => 'invoices', 'label' => 'Invoices', 'href' => '/student/invoices'],
            ],
            'stats' => $stats,
            'upcoming_schedules' => $upcomingSchedules,
            'recent_batches' => $recentBatches,
            'recent_orders' => $recentOrders,
        ]);
    }
}
