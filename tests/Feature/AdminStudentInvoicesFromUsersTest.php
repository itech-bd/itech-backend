<?php

use App\Models\User;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseOrder;
use Spatie\Permission\Models\Role;

it('allows an admin to view a student invoices list under users section', function () {
    $adminRole = Role::findOrCreate('admin');
    $studentRole = Role::findOrCreate('student');

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

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

    $this->actingAs($admin)
        ->get('/users/'.$student->getRouteKey().'/invoices')
        ->assertOk()
        ->assertSee('Invoices:')
        ->assertSee('#INV-'.$order->id);

    $this->actingAs($admin)
        ->get('/users/'.$student->getRouteKey().'/invoices/'.$order->getRouteKey())
        ->assertOk()
        ->assertSee('Invoice #INV-'.$order->id);

    $this->actingAs($admin)
        ->get('/users/'.$student->getRouteKey().'/invoices/'.$order->getRouteKey().'/download')
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('returns 404 when admin tries to access an invoice that does not belong to the student', function () {
    $adminRole = Role::findOrCreate('admin');
    $studentRole = Role::findOrCreate('student');

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

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
        'user_id' => $studentB->id,
        'course_id' => $course->id,
        'amount' => 250,
        'currency' => 'BDT',
        'status' => 'paid',
    ]);

    $this->actingAs($admin)
        ->get('/users/'.$studentA->getRouteKey().'/invoices/'.$order->getRouteKey())
        ->assertNotFound();

    $this->actingAs($admin)
        ->get('/users/'.$studentA->getRouteKey().'/invoices/'.$order->getRouteKey().'/download')
        ->assertNotFound();
});
