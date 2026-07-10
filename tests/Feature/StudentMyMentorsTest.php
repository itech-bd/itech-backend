<?php

use App\Models\User;
use Modules\Batch\Models\Batch;
use Modules\Course\Models\Course;
use Modules\Mentors\Models\Mentor;
use Spatie\Permission\Models\Role;

it('shows only mentors from the student approved batches', function () {
    $studentRole = Role::findOrCreate('student');
    $mentorRole = Role::findOrCreate('mentor');

    $student = User::factory()->create();
    $student->assignRole($studentRole);

    $courseCreator = User::factory()->create();

    $course = Course::query()->create([
        'title' => 'Course 1',
        'description' => 'Desc',
        'status' => 'active',
        'created_by' => $courseCreator->id,
    ]);

    $batch = Batch::query()->create([
        'course_id' => $course->id,
        'name' => 'Batch A',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(10)->toDateString(),
        'class_days' => ['Monday'],
        'class_time' => '10:00 AM',
        'status' => 'upcoming',
        'created_by' => $courseCreator->id,
    ]);

    $student->studentBatches()->attach($batch->id, [
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $courseCreator->id,
    ]);

    $mentorUserInBatch = User::factory()->create();
    $mentorUserInBatch->assignRole($mentorRole);

    Mentor::query()->create([
        'user_id' => $mentorUserInBatch->id,
        'name' => 'Mentor In Batch',
        'topic' => 'Topic',
        'bio' => 'Bio',
        'is_active' => true,
    ]);

    $batch->mentors()->attach($mentorUserInBatch->id);

    $mentorUserOut = User::factory()->create();
    $mentorUserOut->assignRole($mentorRole);

    Mentor::query()->create([
        'user_id' => $mentorUserOut->id,
        'name' => 'Mentor Out',
        'topic' => 'Other',
        'bio' => 'Other',
        'is_active' => true,
    ]);

    $this->actingAs($student)
        ->get('/dashboard/student/mentors')
        ->assertOk()
        ->assertSee('My Mentors')
        ->assertSee('Mentor In Batch')
        ->assertDontSee('Mentor Out');
});
