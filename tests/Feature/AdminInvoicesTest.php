<?php

use App\Models\User;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseOrder;
use Spatie\Permission\Models\Role;

it('allows admin to view all invoices and mark one as completed', function () {
    $adminRole = Role::findOrCreate('admin');
    $studentRole = Role::findOrCreate('student');

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $student = User::factory()->create();
    $student->assignRole($studentRole);

    $creator = User::factory()->create();

    $course = Course::query()->create([
        'title' => 'Invoice Admin Course',
        'description' => 'Test description',
        'status' => 'active',
        'created_by' => $creator->id,
    ]);

    $order = CourseOrder::query()->create([
        'user_id' => $student->id,
        'course_id' => $course->id,
        'amount' => 500,
        'currency' => 'BDT',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->get('/dashboard/admin/invoices')
        ->assertOk()
        ->assertSee('All Invoices');

    $this->actingAs($admin)
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get('/dashboard/admin/invoices?draw=1&start=0&length=10')
        ->assertOk()
        ->assertJsonFragment(['invoice' => '#INV-'.$order->id]);

    $this->actingAs($admin)
        ->get('/dashboard/admin/invoices/'.$order->getRouteKey().'/download')
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    $this->actingAs($admin)
        ->patch('/dashboard/admin/invoices/'.$order->getRouteKey(), ['status' => 'completed'])
        ->assertRedirect();

    expect($order->refresh()->status)->toBe('paid');
});

it('prevents non-admin from accessing admin invoices', function () {
    $studentRole = Role::findOrCreate('student');

    $student = User::factory()->create();
    $student->assignRole($studentRole);

    $this->actingAs($student)
        ->get('/dashboard/admin/invoices')
        ->assertForbidden();
});
