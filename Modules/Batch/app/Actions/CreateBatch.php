<?php

namespace Modules\Batch\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Batch\Models\Batch;
use Modules\Course\Models\Course;

class CreateBatch
{
    public function handle(Course $course, array $validated, int $adminId): Batch
    {
        return DB::transaction(
            function () use ($course, $validated, $adminId) {
                $batch = Batch::query()->create([
                    'course_id' => $course->id,
                    'name' => $validated['name'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'class_days' => $validated['class_days'],
                    'class_time' => $validated['class_time'],
                    'live_class_link' => $validated['live_class_link'] ?? null,
                    'status' => $validated['status'],
                    'created_by' => $adminId,
                ]);

                // New batches should start with no manual assignments.
                $batch->mentors()->detach();
                $batch->students()->detach();

                $batch->autoGenerateClassSchedules($adminId);

                return $batch;
            }
        );
    }
}
