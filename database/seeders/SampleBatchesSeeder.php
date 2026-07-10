<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Batch\Models\Batch;
use Modules\Course\Models\Course;

class SampleBatchesSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->role('admin')->orderBy('id')->first()
            ?: User::query()->orderBy('id')->first();

        $createdBy = $admin ? (int) $admin->id : 1;

        $courses = Course::query()->orderBy('id')->get();
        if ($courses->isEmpty()) {
            return;
        }

        $now = Carbon::now()->startOfDay();
        $defaultDays = ['Saturday', 'Monday', 'Wednesday'];

        foreach ($courses as $index => $course) {
            $start1 = (clone $now)->addWeeks($index)->next(Carbon::SATURDAY);
            $end1 = (clone $start1)->addWeeks(8);

            $start2 = (clone $start1)->addWeeks(10)->next(Carbon::SATURDAY);
            $end2 = (clone $start2)->addWeeks(8);

            $batches = [
                [
                    'name' => $course->title.' - Batch A',
                    'start_date' => $start1->toDateString(),
                    'end_date' => $end1->toDateString(),
                    'class_days' => $defaultDays,
                    'class_time' => '19:00',
                    'status' => $start1->isPast() ? 'running' : 'upcoming',
                ],
                [
                    'name' => $course->title.' - Batch B',
                    'start_date' => $start2->toDateString(),
                    'end_date' => $end2->toDateString(),
                    'class_days' => ['Sunday', 'Tuesday', 'Thursday'],
                    'class_time' => '20:00',
                    'status' => 'upcoming',
                ],
            ];

            foreach ($batches as $data) {
                Batch::query()->updateOrCreate(
                    [
                        'course_id' => (int) $course->id,
                        'name' => $data['name'],
                    ],
                    $data + [
                        'course_id' => (int) $course->id,
                        'created_by' => $createdBy,
                    ]
                );
            }
        }
    }
}
