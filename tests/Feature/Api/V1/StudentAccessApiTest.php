<?php

use App\Models\User;
use Modules\Batch\Models\Batch;
use Modules\Batch\Models\ClassSchedule;
use Modules\Course\Models\Course;
use Spatie\Permission\Models\Role;

it('rejects unauthenticated student dashboard requests with JSON', function () {
    $this->getJson('/api/v1/student/dashboard')
        ->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'UNAUTHENTICATED');
});

it('allows a verified student to read the dashboard', function () {
    $student = User::factory()->create();
    $student->assignRole(Role::findOrCreate('student', 'web'));

    $this->actingAs($student)
        ->getJson('/api/v1/student/dashboard')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.id', $student->id)
        ->assertJsonStructure(['data' => ['menu', 'stats', 'upcoming_schedules', 'recent_batches', 'recent_orders']]);
});

it('does not expose class links before enrollment approval', function () {
    $student = User::factory()->create();
    $student->assignRole(Role::findOrCreate('student', 'web'));
    $creator = User::factory()->create();

    $course = Course::query()->create([
        'title' => 'Protected Class Links',
        'description' => 'API access test.',
        'status' => 'active',
        'created_by' => $creator->id,
    ]);

    $batch = Batch::query()->create([
        'course_id' => $course->id,
        'name' => 'Pending API Batch',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'class_days' => ['Monday'],
        'class_time' => '8:00 PM',
        'live_class_link' => 'https://example.test/live',
        'status' => 'running',
        'created_by' => $creator->id,
    ]);

    ClassSchedule::query()->create([
        'batch_id' => $batch->id,
        'class_date' => now()->addDay()->toDateString(),
        'topic' => 'Private class',
        'live_class_link' => 'https://example.test/schedule-live',
        'recorded_video_link' => 'https://example.test/recording',
        'created_by' => $creator->id,
    ]);

    $student->studentBatches()->attach($batch->id, [
        'status' => 'pending',
        'batch_type' => 'online',
    ]);

    $this->actingAs($student)
        ->getJson('/api/v1/student/batches/'.$batch->id)
        ->assertOk()
        ->assertJsonPath('data.batch.live_class_link', null)
        ->assertJsonPath('data.schedule_access', false)
        ->assertJsonCount(0, 'data.schedules');
});
