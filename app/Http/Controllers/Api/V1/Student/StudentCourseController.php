<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Api\V1\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Batch\Models\Batch;
use Modules\Course\Models\Course;

class StudentCourseController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $userId = (int) $request->user()->id;

        $query = Course::query()
            ->whereHas('batches.students', fn ($builder) => $builder
                ->where('users.id', $userId)
                ->whereIn('batch_students.status', ['pending', 'approved']))
            ->withCount([
                'batches as enrolled_batches_count' => fn ($builder) => $builder
                    ->whereHas('students', fn ($studentQuery) => $studentQuery
                        ->where('users.id', $userId)
                        ->whereIn('batch_students.status', ['pending', 'approved'])),
            ])
            ->latest('id');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(fn ($builder) => $builder
                ->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }

        $paginator = $query->paginate(min(max($request->integer('per_page', 12), 1), 50));

        return $this->success([
            ...$this->paginated($paginator, fn (Course $course) => [
                'id' => $course->id,
                'slug' => $course->slug,
                'title' => $course->title,
                'thumbnail_url' => $course->thumbnail_url,
                'status' => $course->status,
                'enrolled_batches_count' => $course->enrolled_batches_count,
            ]),
            'filters' => ['search' => $search],
        ]);
    }

    public function show(Request $request, Course $course): JsonResponse
    {
        $userId = (int) $request->user()->id;

        $batchIds = DB::table('batch_students')
            ->join('batches', 'batches.id', '=', 'batch_students.batch_id')
            ->where('batch_students.student_id', $userId)
            ->where('batches.course_id', $course->id)
            ->whereIn('batch_students.status', ['pending', 'approved'])
            ->pluck('batch_students.batch_id');

        abort_unless($batchIds->isNotEmpty(), 403);

        $enrollments = DB::table('batch_students')
            ->where('student_id', $userId)
            ->whereIn('batch_id', $batchIds)
            ->get(['batch_id', 'status', 'batch_type', 'approved_at'])
            ->keyBy('batch_id');

        $batches = Batch::query()
            ->whereIn('id', $batchIds)
            ->with('mentors:id,name,email,profile_image')
            ->withCount(['classSchedules', 'mentors'])
            ->latest('id')
            ->get()
            ->map(function (Batch $batch) use ($enrollments): array {
                $enrollment = $enrollments->get($batch->id);

                return [
                    'id' => $batch->id,
                    'name' => $batch->name,
                    'status' => $batch->status,
                    'start_date' => $batch->start_date?->toDateString(),
                    'end_date' => $batch->end_date?->toDateString(),
                    'class_days' => $batch->class_days ?: [],
                    'class_time' => $batch->class_time,
                    'enrollment' => $enrollment ? [
                        'status' => $enrollment->status,
                        'batch_type' => $enrollment->batch_type,
                        'approved_at' => $enrollment->approved_at,
                    ] : null,
                    'class_schedules_count' => $batch->class_schedules_count,
                    'mentors_count' => $batch->mentors_count,
                    'mentors' => $batch->mentors->map(fn ($mentor) => [
                        'id' => $mentor->id,
                        'name' => $mentor->name,
                        'email' => $mentor->email,
                        'profile_image_url' => $mentor->profile_image_url,
                    ])->values(),
                ];
            })->values();

        return $this->success([
            'course' => [
                'id' => $course->id,
                'slug' => $course->slug,
                'title' => $course->title,
                'description' => $course->description,
                'thumbnail_url' => $course->thumbnail_url,
                'status' => $course->status,
            ],
            'batches' => $batches,
        ]);
    }
}
