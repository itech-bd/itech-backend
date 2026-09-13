<?php

use App\Models\Student;
use App\Models\User;
use Modules\Batch\Models\Batch;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseOrder;

test('guest is redirected to login for checkout page', function () {
    $user = User::factory()->create();

    $course = Course::query()->create([
        'title' => 'Test Course',
        'description' => 'Test description',
        'old_price' => 8000,
        'discount_price' => 6500,
        'thumbnail' => null,
        'status' => 'active',
        'created_by' => $user->id,
    ]);

    $this
        ->get(route('checkout.show', $course))
        ->assertRedirect(route('login'));
});

test('authenticated user can view checkout and create pending order', function () {
    $creator = User::factory()->create();
    $buyer = Student::query()->create(['name' => 'Test Student', 'email' => fake()->unique()->safeEmail(), 'password' => 'password', 'email_verified_at' => now()]);

    $course = Course::query()->create([
        'title' => 'Checkout Course',
        'description' => 'Test description',
        'old_price' => 9000,
        'discount_price' => 7000,
        'thumbnail' => null,
        'status' => 'active',
        'created_by' => $creator->id,
    ]);

    $this
        ->actingAs($buyer, 'student')
        ->get(route('checkout.show', $course))
        ->assertOk()
        ->assertSee('Checkout');

    $this
        ->actingAs($buyer, 'student')
        ->post(route('checkout.store', $course))
        ->assertRedirect();

    $order = CourseOrder::query()
        ->where('student_id', $buyer->id)
        ->where('course_id', $course->id)
        ->latest('id')
        ->first();

    expect($order)->not->toBeNull();
    expect($order->status)->toBe('pending');
    expect((float) $order->amount)->toBe(7000.0);

    $this
        ->actingAs($buyer, 'student')
        ->get(route('checkout.success', $order))
        ->assertOk()
        ->assertSee((string) $order->id);
});

test('checkout requires selecting an upcoming/running batch when available and creates pending enrollment', function () {
    $creator = User::factory()->create();
    $buyer = Student::query()->create(['name' => 'Test Student', 'email' => fake()->unique()->safeEmail(), 'password' => 'password', 'email_verified_at' => now()]);

    $course = Course::query()->create([
        'title' => 'Course With Batches',
        'description' => 'Test description',
        'old_price' => 9000,
        'discount_price' => 7000,
        'thumbnail' => null,
        'status' => 'active',
        'created_by' => $creator->id,
    ]);

    $batch = Batch::query()->create([
        'course_id' => $course->id,
        'name' => 'Batch A',
        'start_date' => now()->addDays(7)->toDateString(),
        'end_date' => now()->addDays(30)->toDateString(),
        'class_days' => ['Saturday'],
        'class_time' => '10:00 AM',
        'status' => 'upcoming',
        'created_by' => $creator->id,
    ]);

    $this
        ->actingAs($buyer, 'student')
        ->post(route('checkout.store', $course), [])
        ->assertRedirect()
        ->assertSessionHasErrors(['batch_id']);

    $this
        ->actingAs($buyer, 'student')
        ->post(route('checkout.store', $course), ['batch_id' => $batch->id])
        ->assertRedirect();

    $order = CourseOrder::query()
        ->where('student_id', $buyer->id)
        ->where('course_id', $course->id)
        ->latest('id')
        ->first();

    expect($order)->not->toBeNull();
    expect((int) $order->batch_id)->toBe((int) $batch->id);

    $this->assertDatabaseHas('batch_students', [
        'batch_id' => $batch->id,
        'student_id' => $buyer->id,
        'status' => 'pending',
    ]);
});

test('student cannot join the same batch again', function () {
    $creator = User::factory()->create();
    $buyer = Student::query()->create(['name' => 'Test Student', 'email' => fake()->unique()->safeEmail(), 'password' => 'password', 'email_verified_at' => now()]);

    $course = Course::query()->create([
        'title' => 'No Double Join Course',
        'description' => 'Test description',
        'old_price' => 9000,
        'discount_price' => 7000,
        'thumbnail' => null,
        'status' => 'active',
        'created_by' => $creator->id,
    ]);

    $batch = Batch::query()->create([
        'course_id' => $course->id,
        'name' => 'Batch A',
        'start_date' => now()->addDays(7)->toDateString(),
        'end_date' => now()->addDays(30)->toDateString(),
        'class_days' => ['Saturday'],
        'class_time' => '10:00 AM',
        'status' => 'upcoming',
        'created_by' => $creator->id,
    ]);

    // Existing enrollment request (pending) without a pending order.
    \Illuminate\Support\Facades\DB::table('batch_students')->insert([
        'batch_id' => $batch->id,
        'student_id' => $buyer->id,
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this
        ->actingAs($buyer, 'student')
        ->post(route('checkout.store', $course), ['batch_id' => $batch->id])
        ->assertRedirect()
        ->assertSessionHasErrors(['batch_id']);
});

test('user cannot view another users order success page', function () {
    $creator = User::factory()->create();
    $buyer = Student::query()->create(['name' => 'Test Student', 'email' => fake()->unique()->safeEmail(), 'password' => 'password', 'email_verified_at' => now()]);
    $otherUser = Student::query()->create(['name' => 'Test Student', 'email' => fake()->unique()->safeEmail(), 'password' => 'password', 'email_verified_at' => now()]);

    $course = Course::query()->create([
        'title' => 'Private Order Course',
        'description' => 'Test description',
        'old_price' => 8000,
        'thumbnail' => null,
        'status' => 'active',
        'created_by' => $creator->id,
    ]);

    $order = CourseOrder::query()->create([
        'student_id' => $buyer->id,
        'course_id' => $course->id,
        'amount' => 8000,
        'currency' => 'BDT',
        'status' => 'pending',
    ]);

    $this
        ->actingAs($otherUser, 'student')
        ->get(route('checkout.success', $order))
        ->assertForbidden();
});
