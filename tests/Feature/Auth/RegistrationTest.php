<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response
        ->assertRedirect(route('home'))
        ->assertSessionHas('auth_modal', 'register');
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $response
        ->assertRedirect(route('verification.notice', absolute: false))
        ->assertSessionHas('status', 'verification-link-sent');
});
