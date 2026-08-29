<?php

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Modules\Batch\Models\Batch;
use Modules\Batch\Models\ClassSchedule;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseOrder;
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

it('lists only new courses in the student course catalog', function () {
    $student = Student::query()->create([
        'name' => 'Catalog Student',
        'email' => 'catalog-student@example.test',
        'email_verified_at' => now(),
        'password' => Hash::make('password'),
    ]);
    $creator = User::factory()->create();

    $approvedCourse = Course::query()->create([
        'title' => 'Web Development Catalog',
        'description' => 'Approved catalog course.',
        'online_old_price' => 12000,
        'online_discount_price' => 9000,
        'status' => 'active',
        'created_by' => $creator->id,
    ]);
    $pendingCourse = Course::query()->create([
        'title' => 'Digital Marketing Catalog',
        'description' => 'Pending catalog course.',
        'online_old_price' => 10000,
        'online_discount_price' => 8000,
        'status' => 'active',
        'created_by' => $creator->id,
    ]);
    $openCourse = Course::query()->create([
        'title' => 'Graphic Design Catalog',
        'description' => 'Open catalog course.',
        'online_old_price' => 11000,
        'online_discount_price' => 8500,
        'status' => 'active',
        'created_by' => $creator->id,
    ]);
    $inactiveCourse = Course::query()->create([
        'title' => 'Inactive Catalog',
        'description' => 'Hidden catalog course.',
        'status' => 'inactive',
        'created_by' => $creator->id,
    ]);

    $approvedBatch = Batch::query()->create([
        'course_id' => $approvedCourse->id,
        'name' => 'Approved Batch',
        'start_date' => now()->addWeek()->toDateString(),
        'end_date' => now()->addMonths(2)->toDateString(),
        'class_days' => ['Monday'],
        'class_time' => '8:00 PM',
        'status' => 'upcoming',
        'created_by' => $creator->id,
    ]);
    $pendingBatch = Batch::query()->create([
        'course_id' => $pendingCourse->id,
        'name' => 'Pending Batch',
        'start_date' => now()->addWeek()->toDateString(),
        'end_date' => now()->addMonths(2)->toDateString(),
        'class_days' => ['Tuesday'],
        'class_time' => '9:00 PM',
        'status' => 'upcoming',
        'created_by' => $creator->id,
    ]);
    Batch::query()->create([
        'course_id' => $openCourse->id,
        'name' => 'Open Batch',
        'start_date' => now()->addWeek()->toDateString(),
        'end_date' => now()->addMonths(2)->toDateString(),
        'class_days' => ['Wednesday'],
        'class_time' => '7:00 PM',
        'status' => 'upcoming',
        'created_by' => $creator->id,
    ]);
    Batch::query()->create([
        'course_id' => $inactiveCourse->id,
        'name' => 'Inactive Batch',
        'start_date' => now()->addWeek()->toDateString(),
        'end_date' => now()->addMonths(2)->toDateString(),
        'class_days' => ['Thursday'],
        'class_time' => '6:00 PM',
        'status' => 'upcoming',
        'created_by' => $creator->id,
    ]);

    $student->studentBatches()->attach($approvedBatch->id, [
        'status' => 'approved',
        'batch_type' => 'online',
    ]);
    $student->studentBatches()->attach($pendingBatch->id, [
        'status' => 'pending',
        'batch_type' => 'online',
    ]);

    $pendingOrder = CourseOrder::query()->create([
        'student_id' => $student->id,
        'course_id' => $pendingCourse->id,
        'batch_id' => $pendingBatch->id,
        'batch_type' => 'online',
        'amount' => 8000,
        'currency' => 'BDT',
        'status' => 'pending',
    ]);

    Sanctum::actingAs($student, ['student-panel']);

    $response = $this->getJson('/api/v1/student/course-catalog?per_page=10')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.pagination.total', 1);

    $items = collect($response->json('data.items'));

    expect($pendingOrder->fresh()->id)->toBe($pendingOrder->id)
        ->and($items->pluck('id')->values()->all())->toBe([$openCourse->id])
        ->and($items->firstWhere('id', $approvedCourse->id))->toBeNull()
        ->and($items->firstWhere('id', $pendingCourse->id))->toBeNull()
        ->and($items->firstWhere('id', $inactiveCourse->id))->toBeNull();
});
