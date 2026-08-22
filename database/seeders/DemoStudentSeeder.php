<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Modules\Batch\Models\Batch;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseOrder;

class DemoStudentSeeder extends Seeder
{
    /**
     * Seed a ready-to-login student account and attach one visible batch when available.
     */
    public function run(): void
    {
        $student = Student::query()->firstOrNew(['email' => 'student@itechbd.test']);
        $student->forceFill([
            'name' => 'Demo Student',
            'password' => '12345678',
            'must_change_password' => false,
            'email_verified_at' => $student->email_verified_at ?: Carbon::now(),
        ])->save();

        $adminId = $this->adminUserId();

        $batch = $this->resolveDemoBatch($adminId);

        if ($batch) {
            $student->studentBatches()->syncWithoutDetaching([
                $batch->id => [
                    'status' => 'approved',
                    'batch_type' => 'online',
                    'approved_at' => Carbon::now(),
                    'approved_by' => null,
                ],
            ]);

            if ($adminId && ! $batch->classSchedules()->exists()) {
                $batch->autoGenerateClassSchedules($adminId);
            }

            if ($batch->course && Schema::hasTable('course_orders')) {
                CourseOrder::query()->updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'course_id' => $batch->course->id,
                        'batch_id' => $batch->id,
                    ],
                    [
                        'batch_type' => 'online',
                        'amount' => $this->courseFee($batch->course),
                        'currency' => 'BDT',
                        'status' => 'paid',
                    ]
                );
            }
        }

        $this->seedProfile($student);
    }

    private function adminUserId(): ?int
    {
        return User::query()->role('admin')->orderBy('id')->value('id')
            ?? User::query()->orderBy('id')->value('id');
    }

    private function resolveDemoBatch(?int $createdBy): ?Batch
    {
        $batch = Batch::query()
            ->with('course')
            ->whereIn('status', ['running', 'upcoming'])
            ->orderByRaw("CASE status WHEN 'running' THEN 0 WHEN 'upcoming' THEN 1 ELSE 2 END")
            ->orderBy('start_date')
            ->orderBy('id')
            ->first();

        if ($batch) {
            return $batch;
        }

        $batch = Batch::query()
            ->with('course')
            ->latest('id')
            ->first();

        if ($batch) {
            return $batch;
        }

        $course = Course::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->first();

        if (! $course) {
            return null;
        }

        if (! $createdBy) {
            return null;
        }

        return Batch::query()->create([
            'course_id' => $course->id,
            'name' => 'Demo Student Batch',
            'start_date' => Carbon::now()->subWeek()->toDateString(),
            'end_date' => Carbon::now()->addMonths(3)->toDateString(),
            'class_days' => ['Saturday', 'Monday', 'Wednesday'],
            'class_time' => '08:00 PM',
            'live_class_link' => 'https://meet.google.com/demo-student-class',
            'status' => 'running',
            'created_by' => $createdBy,
        ])->load('course');
    }

    private function courseFee(Course $course): float
    {
        return (float) (
            $course->online_discount_price
            ?? $course->offline_discount_price
            ?? $course->discount_price
            ?? $course->old_price
            ?? 0
        );
    }

    private function seedProfile(Student $student): void
    {
        $student->forceFill([
            'gender' => 'male',
            'date_of_birth' => '2000-01-15',
            'mobile_number' => '01805565500',
            'father_name' => 'Demo Father',
            'father_mobile' => '01805565501',
            'mother_name' => 'Demo Mother',
            'mother_mobile' => '01805565502',
            'bio' => 'Demo student account for checking the student panel.',
            'public_url' => 'demo-student',
            'house_number' => 'Ka-66/1',
            'street' => 'Azahar Plaza, Kuril Chowrasta',
            'city' => 'Dhaka',
            'post_office' => 'Khilkhet',
            'zip_code' => '1229',
            'country' => 'Bangladesh',
        ])->save();
    }
}
