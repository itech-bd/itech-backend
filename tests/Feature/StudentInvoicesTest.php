<?php

use App\Models\User;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseOrder;
use Spatie\Permission\Models\Role;

it('allows a student to see their invoice list', function () {
    $studentRole = Role::findOrCreate('student');

    $student = User::factory()->create();
    $student->assignRole($studentRole);

    $creator = User::factory()->create();

    $course = Course::query()->create([
        'title' => 'Test Course',
        'description' => 'Test description',
        'status' => 'active',
        'created_by' => $creator->id,
    ]);

    $order = CourseOrder::query()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'amount' => 100,
        'currency' => 'BDT',
        'status' => 'paid',
    ]);

    $this->actingAs($student)
        ->get('/dashboard/student/invoices')
        ->assertOk()
        ->assertSee('Invoices')
        ->assertSee('#INV-'.$order->id);
});

it('prevents a student from viewing another student invoice', function () {
    $studentRole = Role::findOrCreate('student');

    $studentA = User::factory()->create();
    $studentA->assignRole($studentRole);

    $studentB = User::factory()->create();
    $studentB->assignRole($studentRole);

    $creator = User::factory()->create();

    $course = Course::query()->create([
        'title' => 'Another Course',
        'description' => 'Test description',
        'status' => 'active',
        'created_by' => $creator->id,
    ]);

    $order = CourseOrder::query()->create([
        'user_id' => $studentA->id,
        'course_id' => $course->id,
        'amount' => 250,
        'currency' => 'BDT',
        'status' => 'paid',
    ]);

    $this->actingAs($studentB)
        ->get('/dashboard/student/invoices/'.$order->getRouteKey())
        ->assertForbidden();

    $this->actingAs($studentB)
        ->get('/dashboard/student/invoices/'.$order->getRouteKey().'/download')
        ->assertForbidden();
});

it('downloads a student invoice as pdf', function () {
    $studentRole = Role::findOrCreate('student');

    $student = User::factory()->create();
    $student->assignRole($studentRole);

    $creator = User::factory()->create();

    $course = Course::query()->create([
        'title' => 'PDF Course',
        'description' => 'Test description',
        'status' => 'active',
        'created_by' => $creator->id,
    ]);

    $order = CourseOrder::query()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'amount' => 199,
        'currency' => 'BDT',
        'status' => 'paid',
    ]);

    $this->actingAs($student)
        ->get('/dashboard/student/invoices/'.$order->getRouteKey().'/download')
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
