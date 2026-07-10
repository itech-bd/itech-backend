<?php

use App\Models\User;
use Modules\Course\Models\Course;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response
        ->assertRedirect(route('home'))
        ->assertSessionHas('auth_modal', 'login');
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('login redirect from checkout does not loop', function () {
    $creator = User::factory()->create();

    $course = Course::query()->create([
        'title' => 'Checkout Flow Course',
        'description' => 'Test description',
        'old_price' => 8000,
        'thumbnail' => null,
        'status' => 'active',
        'created_by' => $creator->id,
    ]);

    $checkoutUrl = url('/courses/' . $course->id . '/checkout');

    $response = $this
        ->from($checkoutUrl)
        ->withSession(['url.intended' => $checkoutUrl])
        ->get('/login');

    $response
        ->assertRedirect(url('/courses/' . $course->id))
        ->assertSessionHas('auth_modal', 'login');
});
