<?php

use App\Models\User;
use Modules\Batch\Models\Batch;
use Modules\Course\Models\Course;
use Spatie\Permission\Models\Role;

it('allows a student to view their approved batch from the student panel', function () {
    $studentRole = Role::findOrCreate('student');

    $student = User::factory()->create();
    $student->assignRole($studentRole);

    $creator = User::factory()->create();

    $course = Course::query()->create([
        'title' => 'Student Batch Course',
        'description' => 'Desc',
        'status' => 'active',
        'created_by' => $creator->id,
    ]);

    $batch = Batch::query()->create([
        'course_id' => $course->id,
        'name' => 'Batch 11',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(10)->toDateString(),
        'class_days' => ['Monday'],
        'class_time' => '10:00 AM',
        'status' => 'upcoming',
        'created_by' => $creator->id,
    ]);

    $student->studentBatches()->attach($batch->id, [
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $creator->id,
    ]);

    $this->actingAs($student)
        ->get('/dashboard/student/batches/' . $batch->getRouteKey())
        ->assertOk()
        ->assertSee('Batch 11');
});

it('allows a student to view their pending batch from the student panel', function () {
    $studentRole = Role::findOrCreate('student');

    $student = User::factory()->create();
    $student->assignRole($studentRole);

    $creator = User::factory()->create();

    $course = Course::query()->create([
        'title' => 'Pending Student Batch Course',
        'description' => 'Desc',
        'status' => 'active',
        'created_by' => $creator->id,
    ]);

    $batch = Batch::query()->create([
        'course_id' => $course->id,
        'name' => 'Batch Pending',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(10)->toDateString(),
        'class_days' => ['Monday'],
        'class_time' => '10:00 AM',
        'status' => 'upcoming',
        'created_by' => $creator->id,
    ]);

    $student->studentBatches()->attach($batch->id, [
        'status' => 'pending',
    ]);

    $this->actingAs($student)
        ->get('/dashboard/student/batches/' . $batch->getRouteKey())
        ->assertOk()
        ->assertSee('Batch Pending');
});