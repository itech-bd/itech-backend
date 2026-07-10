<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::query()->firstOrCreate(['name' => 'mentor', 'guard_name' => 'web']);
    Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
});

test('mentor cannot access users index', function () {
    $user = User::factory()->create();
    $user->assignRole('mentor');

    $this
        ->actingAs($user)
        ->get('/users')
        ->assertForbidden();
});

test('mentor cannot delete their account', function () {
    $user = User::factory()->create();
    $user->assignRole('mentor');

    $this
        ->actingAs($user)
        ->delete('/profile', ['password' => 'password'])
        ->assertForbidden();

    $this->assertAuthenticated();
    $this->assertNotNull($user->fresh());
});
