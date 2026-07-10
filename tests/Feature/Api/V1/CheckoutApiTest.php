<?php

use App\Models\User;
use Modules\Batch\Models\Batch;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseOrder;
use Spatie\Permission\Models\Role;

it('moves a pending enrollment when the pending order changes batch', function () {
    $student = User::factory()->create();
    $student->assignRole(Role::findOrCreate('student', 'web'));
    $creator = User::factory()->create();

    $course = Course::query()->create([
        'title' => 'Checkout API Course',
        'description' => 'Checkout transaction test.',
        'online_old_price' => 8000,
        'online_discount_price' => 6500,
        'status' => 'active',
        'created_by' => $creator->id,
    ]);

    $batchOne = Batch::query()->create([
        'course_id' => $course->id,
        'name' => 'Batch One',
        'start_date' => now()->addWeek()->toDateString(),
        'end_date' => now()->addMonths(2)->toDateString(),
        'class_days' => ['Monday'],
        'class_time' => '8:00 PM',
        'status' => 'upcoming',
        'created_by' => $creator->id,
    ]);

    $batchTwo = Batch::query()->create([
        'course_id' => $course->id,
        'name' => 'Batch Two',
        'start_date' => now()->addWeeks(2)->toDateString(),
        'end_date' => now()->addMonths(3)->toDateString(),
        'class_days' => ['Wednesday'],
        'class_time' => '9:00 PM',
        'status' => 'upcoming',
        'created_by' => $creator->id,
    ]);

    $order = CourseOrder::query()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'batch_id' => $batchOne->id,
        'batch_type' => 'online',
        'amount' => 6500,
        'currency' => 'BDT',
        'status' => 'pending',
    ]);

    $student->studentBatches()->attach($batchOne->id, [
        'status' => 'pending',
        'batch_type' => 'online',
    ]);

    $this->actingAs($student)
        ->postJson('/api/v1/checkout/courses/'.$course->slug, [
            'batch_id' => $batchTwo->id,
            'batch_type' => 'online',
        ])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.batch.id', $batchTwo->id);

    expect($order->fresh()->batch_id)->toBe($batchTwo->id);

    $this->assertDatabaseMissing('batch_students', [
        'student_id' => $student->id,
        'batch_id' => $batchOne->id,
        'status' => 'pending',
    ]);

    $this->assertDatabaseHas('batch_students', [
        'student_id' => $student->id,
        'batch_id' => $batchTwo->id,
        'status' => 'pending',
        'batch_type' => 'online',
    ]);
});
