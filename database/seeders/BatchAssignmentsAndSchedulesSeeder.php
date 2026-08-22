<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Batch\Models\Batch;
use Modules\Mentors\Models\Mentor;

class BatchAssignmentsAndSchedulesSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->role('admin')->orderBy('id')->first()
            ?: User::query()->orderBy('id')->first();

        $createdBy = $admin ? (int) $admin->id : 1;

        $mentors = Mentor::query()->where('is_active', true)->orderBy('id')->get(['id']);
        $students = Student::query()->orderBy('id')->get(['id']);

        $batches = Batch::query()->with('course:id,title')->orderBy('id')->get();
        if ($batches->isEmpty()) {
            return;
        }

        foreach ($batches as $batch) {
            // Assign mentors
            if ($mentors->isNotEmpty()) {
                $mentorIds = $mentors->pluck('id')->shuffle()->take(2)->values()->all();
                $batch->mentors()->syncWithoutDetaching($mentorIds);
            }

            // Assign students
            if ($students->isNotEmpty()) {
                $studentIds = $students->pluck('id')->shuffle()->take(8)->values()->all();
                $studentPayload = collect($studentIds)
                    ->mapWithKeys(
                        fn (int $studentId): array => [
                            $studentId => [
                                'status' => 'approved',
                                'approved_at' => Carbon::now(),
                                'approved_by' => $createdBy,
                            ],
                        ]
                    )
                    ->all();

                $batch->students()->syncWithoutDetaching($studentPayload);
            }

            // Create a handful of schedules
            $classDays = is_array($batch->class_days) ? $batch->class_days : [];
            $dayNumbers = $this->toCarbonDayNumbers($classDays);

            $start = Carbon::parse($batch->start_date)->startOfDay();
            $end = Carbon::parse($batch->end_date)->endOfDay();

            $created = 0;
            $cursor = (clone $start);

            while ($cursor->lte($end) && $created < 10) {
                if (in_array($cursor->dayOfWeek, $dayNumbers, true)) {
                    $batch->classSchedules()->updateOrCreate(
                        ['class_date' => $cursor->toDateString()],
                        [
                            'topic' => 'Class '.($created + 1).': '.($batch->course?->title ?? 'Batch Session'),
                            'live_class_link' => null,
                            'recorded_video_link' => null,
                            'created_by' => $createdBy,
                        ]
                    );

                    $created++;
                }

                $cursor->addDay();
            }
        }
    }

    /**
     * @param  array<int, string>  $days
     * @return array<int, int>
     */
    private function toCarbonDayNumbers(array $days): array
    {
        $map = [
            'Sunday' => Carbon::SUNDAY,
            'Monday' => Carbon::MONDAY,
            'Tuesday' => Carbon::TUESDAY,
            'Wednesday' => Carbon::WEDNESDAY,
            'Thursday' => Carbon::THURSDAY,
            'Friday' => Carbon::FRIDAY,
            'Saturday' => Carbon::SATURDAY,
        ];

        $numbers = [];
        foreach ($days as $day) {
            if (isset($map[$day])) {
                $numbers[] = $map[$day];
            }
        }

        return array_values(array_unique($numbers));
    }
}
