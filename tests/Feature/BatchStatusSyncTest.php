<?php

use App\Models\User;
use Modules\Batch\Models\Batch;
use Modules\Course\Models\Course;

test('batches:sync-statuses updates upcoming batches to running when start_date is today/past', function () {
    $creator = User::factory()->create();

    $course = Course::query()->create([
        'title' => 'Status Sync Course',
        'description' => 'Test',
        'old_price' => 1000,
        'discount_price' => null,
        'thumbnail' => null,
        'status' => 'active',
        'created_by' => $creator->id,
    ]);

    $batchPast = Batch::query()->create([
        'course_id' => $course->id,
        'name' => 'Past Start',
        'start_date' => now()->subDay()->toDateString(),
        'end_date' => now()->addDays(10)->toDateString(),
        'class_days' => ['Saturday'],
        'class_time' => '10:00 AM',
        'status' => 'upcoming',
        'created_by' => $creator->id,
    ]);

    $batchFuture = Batch::query()->create([
        'course_id' => $course->id,
        'name' => 'Future Start',
        'start_date' => now()->addDays(3)->toDateString(),
        'end_date' => now()->addDays(30)->toDateString(),
        'class_days' => ['Saturday'],
        'class_time' => '10:00 AM',
        'status' => 'upcoming',
        'created_by' => $creator->id,
    ]);

    $this->artisan('batches:sync-statuses')->assertExitCode(0);

    expect($batchPast->refresh()->status)->toBe('running');
    expect($batchFuture->refresh()->status)->toBe('upcoming');
});
