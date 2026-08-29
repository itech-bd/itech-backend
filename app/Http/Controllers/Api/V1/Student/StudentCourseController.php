<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Api\V1\ApiController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Batch\Models\Batch;
use Modules\Course\Models\Course;

class StudentCourseController extends ApiController
{
    public function catalog(Request $request): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $excludedCourseIds = $this->excludedCatalogCourseIds($userId);

        $allActive = Course::query()
            ->where('status', 'active')
            ->when(
                $excludedCourseIds->isNotEmpty(),
                fn (Builder $builder) => $builder->whereNotIn('id', $excludedCourseIds)
            )
            ->get(['id', 'title']);

        $tracks = $allActive
            ->map(fn (Course $course): string => $this->courseTrack($course))
            ->unique()
            ->values();

        $query = Course::query()
            ->where('status', 'active')
            ->when(
                $excludedCourseIds->isNotEmpty(),
                fn (Builder $builder) => $builder->whereNotIn('id', $excludedCourseIds)
            )
            ->with([
                'batches' => fn ($builder) => $builder
                    ->whereIn('status', ['upcoming', 'running'])
                    ->with('mentors:id,name,email,profile_image')
                    ->orderBy('start_date')
                    ->orderBy('id'),
            ])
            ->latest('id');

        $search = trim((string) $request->query('search', ''));
        $track = trim((string) $request->query('track', ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($track !== '') {
            $ids = $allActive
                ->filter(fn (Course $course): bool => $this->courseTrack($course) === $track)
                ->pluck('id');
            $query->whereIn('id', $ids->isEmpty() ? [0] : $ids->all());
        }

        $paginator = $query->paginate(min(max($request->integer('per_page', 12), 1), 50));

        return $this->success([
            ...$this->paginated($paginator, fn (Course $course) => [
                ...$this->catalogCoursePayload($course),
                'enrollment_status' => 'none',
                'joined_batch_ids' => [],
                'joined_batches' => [],
                'pending_order_id' => null,
            ]),
            'filters' => [
                'search' => $search,
                'track' => $track,
                'tracks' => $tracks,
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $userId = (int) $request->user()->id;

        $query = Course::query()
            ->whereHas('batches.students', fn ($builder) => $builder
                ->where('students.id', $userId)
                ->whereIn('batch_students.status', ['pending', 'approved']))
            ->withCount([
                'batches as enrolled_batches_count' => fn ($builder) => $builder
                    ->whereHas('students', fn ($studentQuery) => $studentQuery
                        ->where('students.id', $userId)
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

    private function catalogCoursePayload(Course $course): array
    {
        return [
            'id' => $course->id,
            'slug' => $course->slug,
            'title' => $course->title,
            'track' => $this->courseTrack($course),
            'thumbnail_url' => $course->thumbnail_url,
            'status' => $course->status,
            'pricing' => [
                'old_price' => $this->decimal($course->old_price),
                'discount_price' => $this->decimal($course->discount_price),
                'online_old_price' => $this->decimal($course->online_old_price),
                'online_discount_price' => $this->decimal($course->online_discount_price),
                'offline_old_price' => $this->decimal($course->offline_old_price),
                'offline_discount_price' => $this->decimal($course->offline_discount_price),
                'currency' => 'BDT',
            ],
            'batches' => $course->relationLoaded('batches')
                ? $course->batches->map(fn (Batch $batch) => $this->catalogBatchPayload($batch))->values()
                : [],
        ];
    }

    private function excludedCatalogCourseIds(int $userId)
    {
        $enrolledCourseIds = DB::table('batch_students')
            ->join('batches', 'batches.id', '=', 'batch_students.batch_id')
            ->where('batch_students.student_id', $userId)
            ->whereIn('batch_students.status', ['pending', 'approved'])
            ->pluck('batches.course_id');

        $orderedCourseIds = DB::table('course_orders')
            ->where('student_id', $userId)
            ->whereIn('status', ['pending', 'paid'])
            ->pluck('course_id');

        return $enrolledCourseIds
            ->merge($orderedCourseIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    private function catalogBatchPayload(Batch $batch): array
    {
        return [
            'id' => $batch->id,
            'name' => $batch->name,
            'status' => $this->batchDisplayStatus($batch),
            'start_date' => $batch->start_date?->toDateString(),
            'end_date' => $batch->end_date?->toDateString(),
            'class_days' => $batch->class_days ?: [],
            'class_time' => $batch->class_time,
            'mentors' => $batch->relationLoaded('mentors')
                ? $batch->mentors->map(fn ($mentor) => [
                    'id' => $mentor->id,
                    'name' => $mentor->name,
                    'email' => $mentor->email,
                    'profile_image_url' => $mentor->profile_image_url,
                ])->values()
                : [],
        ];
    }

    private function courseTrack(Course $course): string
    {
        $title = Str::lower((string) $course->title);

        return match (true) {
            Str::contains($title, ['graphic', 'design']) => 'Graphic & Multimedia',
            Str::contains($title, ['marketing', 'seo', 'digital']) => 'Digital Marketing',
            Str::contains($title, ['hardware', 'network']) => 'Hardware & Networking',
            Str::contains($title, ['web', '.net', 'dotnet', 'software', 'development']) => 'Web & Software',
            default => 'Professional Skill',
        };
    }

    private function batchDisplayStatus(Batch $batch): string
    {
        $today = now()->toDateString();

        if ($batch->status === 'completed' || ($batch->end_date && $batch->end_date->toDateString() < $today)) {
            return 'completed';
        }

        if ($batch->start_date && $batch->start_date->toDateString() <= $today) {
            return 'running';
        }

        return 'upcoming';
    }

    private function decimal(mixed $value): ?float
    {
        return is_null($value) ? null : (float) $value;
    }
}
