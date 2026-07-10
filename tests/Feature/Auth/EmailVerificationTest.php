<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('verification.notice', absolute: false));

    $response->assertStatus(200);
});

test('email can be verified', function () {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('guest can change unverified email and resend verification link', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create([
        'email' => 'old@example.com',
        'password' => 'secret-pass-123',
    ]);

    $response = $this->post(route('verification.email.update', absolute: false), [
        'current_email' => 'old@example.com',
        'new_email' => 'new@example.com',
        'password' => 'secret-pass-123',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'verification-email-updated');

    $updated = $user->fresh();
    expect($updated)->not->toBeNull();
    expect($updated?->email)->toBe('new@example.com');
    expect($updated?->email_verified_at)->toBeNull();
    expect(session('verification_email'))->toBe('new@example.com');
});

test('email change before verification requires valid credentials', function () {
    $user = User::factory()->unverified()->create([
        'email' => 'wrong@example.com',
        'password' => 'secret-pass-123',
    ]);

    $response = $this->from(route('verification.notice', absolute: false))
        ->post(route('verification.email.update', absolute: false), [
            'current_email' => 'wrong@example.com',
            'new_email' => 'new@example.com',
            'password' => 'invalid-password',
        ]);

    $response
        ->assertRedirect(route('verification.notice', absolute: false))
        ->assertSessionHasErrors('password');

    expect($user->fresh()?->email)->toBe('wrong@example.com');
});

test('guest verification redirects to home with verified status', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->get($verificationUrl);

    $response
        ->assertRedirect(route('home', absolute: false).'?auth=login&verified=1')
        ->assertSessionHas('status', 'verified')
        ->assertSessionHas('auth_modal', 'auth-verify-status');
});

test('guest verification for already verified user shows already-verified status', function () {
    $user = User::factory()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->get($verificationUrl);

    $response
        ->assertRedirect(route('home', absolute: false).'?auth=login&verified=1')
        ->assertSessionHas('status', 'already-verified')
        ->assertSessionHas('auth_modal', 'auth-verify-status');
});
