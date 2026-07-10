<?php

use App\Models\User;
use Modules\Course\Models\Course;

test('public course details page can be viewed for active course', function () {
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
        ->get(route('courses.show', $course))
        ->assertOk()
        ->assertSee('Test Course');
});

test('public course details page returns 404 for inactive course', function () {
    $user = User::factory()->create();

    $course = Course::query()->create([
        'title' => 'Inactive Course',
        'description' => 'Hidden',
        'old_price' => 8000,
        'thumbnail' => null,
        'status' => 'inactive',
        'created_by' => $user->id,
    ]);

    $this->get(route('courses.show', $course))->assertNotFound();
});

test('public course route uses the slug and legacy numeric url redirects', function () {
    $user = User::factory()->create();

    $course = Course::query()->create([
        'title' => 'Computer Hardware & Networking',
        'description' => 'Friendly URL test',
        'old_price' => 8000,
        'thumbnail' => null,
        'status' => 'active',
        'created_by' => $user->id,
    ]);

    expect($course->slug)->toBe('computer-hardware-and-networking');
    expect(route('courses.show', $course))->toEndWith('/courses/computer-hardware-and-networking');

    $this->get('/courses/'.$course->id)
        ->assertRedirect(route('courses.show', $course));
});
