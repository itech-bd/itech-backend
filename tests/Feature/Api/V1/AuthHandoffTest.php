<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

it('returns a one-time backend login handoff URL for admin users', function () {
    $admin = User::factory()->create([
        'email' => 'admin@example.test',
    ]);
    $admin->assignRole(Role::findOrCreate('admin', 'web'));

    $handoffUrl = $this->postJson('/api/v1/auth/login', [
        'email' => 'admin@example.test',
        'password' => 'password',
        'device_name' => 'nextjs-web',
        'issue_token' => true,
    ])
        ->assertOk()
        ->assertJsonPath('data.user.roles.0', 'admin')
        ->assertJsonPath('data.access_token', fn ($token) => is_string($token) && $token !== '')
        ->json('data.login_handoff_url');

    expect($handoffUrl)->toBeString()->toContain('/login/handoff/');

    $path = parse_url($handoffUrl, PHP_URL_PATH);

    $this->get($path)
        ->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($admin);

    Auth::guard('web')->logout();

    $this->get($path)
        ->assertNotFound();
});

it('does not return a backend login handoff URL for student users', function () {
    $student = User::factory()->create([
        'email' => 'student@example.test',
    ]);
    $student->assignRole(Role::findOrCreate('student', 'web'));

    $this->postJson('/api/v1/auth/login', [
        'email' => 'student@example.test',
        'password' => 'password',
        'device_name' => 'nextjs-web',
        'issue_token' => true,
    ])
        ->assertOk()
        ->assertJsonPath('data.user.roles.0', 'student')
        ->assertJsonPath('data.login_handoff_url', null);
});
