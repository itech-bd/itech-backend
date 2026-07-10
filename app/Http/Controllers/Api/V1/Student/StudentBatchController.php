<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Api\V1\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Batch\Models\Batch;
use Modules\Batch\Models\ClassSchedule;

class StudentBatchController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $status = trim((string) $request->query('status', ''));
        $allowed = ['pending', 'approved'];

        $query = $user->studentBatches()
            ->wherePivotIn('status', $allowed)
            ->with('course:id,title,slug,thumbnail')
            ->withCount(['classSchedules', 'mentors'])
            ->orderByDesc('batches.id');

        if (in_array($status, $allowed, true)) {
            $query->wherePivot('status', $status);
        }

        $paginator = $query->paginate(min(max($request->integer('per_page', 12), 1), 50));

        return $this->success([
            ...$this->paginated($paginator, fn (Batch $batch) => [
                'id' => $batch->id,
                'name' => $batch->name,
                'status' => $batch->status,
                'start_date' => $batch->start_date?->toDateString(),
                'end_date' => $batch->end_date?->toDateString(),
                'class_days' => $batch->class_days ?: [],
                'class_time' => $batch->class_time,
                'enrollment' => [
                    'status' => $batch->pivot?->status,
                    'batch_type' => $batch->pivot?->batch_type,
                    'approved_at' => $batch->pivot?->approved_at,
                ],
                'class_schedules_count' => $batch->class_schedules_count,
                'mentors_count' => $batch->mentors_count,
                'course' => $batch->course ? [
                    'id' => $batch->course->id,
                    'slug' => $batch->course->slug,
                    'title' => $batch->course->title,
                    'thumbnail_url' => $batch->course->thumbnail_url,
                ] : null,
            ]),
            'filters' => ['status' => in_array($status, $allowed, true) ? $status : null],
        ]);
    }

    public function show(Request $request, Batch $batch): JsonResponse
    {
        $userId = (int) $request->user()->id;

        $enrollment = DB::table('batch_students')
            ->where('batch_id', $batch->id)
            ->where('student_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        abort_unless($enrollment, 403);

        $batch->load([
            'course:id,title,slug,thumbnail',
            'mentors:id,name,email,profile_image',
        ]);

        $approved = $enrollment->status === 'approved';
        $schedules = collect();

        if ($approved) {
            $schedules = ClassSchedule::query()
                ->where('batch_id', $batch->id)
                ->orderBy('class_date')
                ->get()
                ->map(fn (ClassSchedule $schedule) => [
                    'id' => $schedule->id,
                    'class_date' => $schedule->class_date?->toDateString(),
                    'topic' => $schedule->topic,
                    'live_class_link' => $schedule->live_class_link ?: $batch->live_class_link,
                    'recorded_video_link' => $schedule->recorded_video_link,
                ])->values();
        }

        return $this->success([
            'batch' => [
                'id' => $batch->id,
                'name' => $batch->name,
                'status' => $batch->status,
                'start_date' => $batch->start_date?->toDateString(),
                'end_date' => $batch->end_date?->toDateString(),
                'class_days' => $batch->class_days ?: [],
                'class_time' => $batch->class_time,
                'live_class_link' => $approved ? $batch->live_class_link : null,
                'course' => $batch->course ? [
                    'id' => $batch->course->id,
                    'slug' => $batch->course->slug,
                    'title' => $batch->course->title,
                    'thumbnail_url' => $batch->course->thumbnail_url,
                ] : null,
                'mentors' => $batch->mentors->map(fn ($mentor) => [
                    'id' => $mentor->id,
                    'name' => $mentor->name,
                    'email' => $mentor->email,
                    'profile_image_url' => $mentor->profile_image_url,
                ])->values(),
            ],
            'enrollment' => [
                'status' => $enrollment->status,
                'batch_type' => $enrollment->batch_type,
                'approved_at' => $enrollment->approved_at,
            ],
            'schedules' => $schedules,
            'schedule_access' => $approved,
        ]);
    }
}
