<?php

use App\Models\User;
use Modules\Course\Models\Course;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('admin can update a course slug without changing an existing long description', function () {
    $adminRole = Role::findOrCreate('admin');
    $editCourse = Permission::findOrCreate('editCourse');
    $adminRole->givePermissionTo($editCourse);

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $creator = User::factory()->create();

    $course = Course::query()->create([
        'title' => 'Computer Hardware & Networking',
        'slug' => 'computer-hardware-and-networking',
        'description' => str_repeat('A', 4503),
        'old_price' => 8000,
        'discount_price' => 6500,
        'thumbnail' => null,
        'status' => 'active',
        'created_by' => $creator->id,
    ]);

    $response = $this
        ->actingAs($admin)
        ->put(route('dashboard.courses.update', $course), [
            'title' => $course->title,
            'slug' => 'hardware-and-networking',
            'description' => $course->description,
            'old_price' => $course->old_price,
            'discount_price' => $course->discount_price,
            'status' => $course->status,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard.courses.show', 'hardware-and-networking'));

    expect($course->fresh()->slug)->toBe('hardware-and-networking');
});