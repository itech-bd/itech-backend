<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

it('opens the frontend with a one-time code while preserving the admin session', function () {
    config(['app.frontend_url' => 'http://localhost:3000']);
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate('admin', 'web'));
    $response = $this->actingAs($admin)->get('/home/handoff?state='.str_repeat('a', 64).'&locale=bn');
    $response->assertRedirect();
    $this->assertAuthenticatedAs($admin);
    $url = $response->headers->get('Location');
    expect($url)->toStartWith('http://localhost:3000/auth/backend?');
    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    expect($query['locale'])->toBe('bn');
    expect($query['state'])->toBe(str_repeat('a', 64));
    expect($admin->tokens()->count())->toBe(0);
    $token = $this->postJson('/api/v1/auth/home-handoff', ['code' => $query['code']])->assertOk()->json('access_token');
    expect($token)->toBeString();
    $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
    expect($accessToken->tokenable_id)->toBe($admin->id);
    expect($accessToken->expires_at)->not->toBeNull();
    $this->postJson('/api/v1/auth/home-handoff', ['code' => $query['code']])->assertUnauthorized();
});

it('rejects expired handoff codes and requires a signed-in backend account', function () {
    $this->get('/home/handoff?state='.str_repeat('a', 64).'&locale=en')->assertRedirect(route('login'));
    $admin = User::factory()->create();
    $url = $this->actingAs($admin)->get('/home/handoff?state='.str_repeat('b', 64).'&locale=en')->headers->get('Location');
    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    $this->travel(2)->minutes();
    $this->postJson('/api/v1/auth/home-handoff', ['code' => $query['code']])->assertUnauthorized();
    $this->getJson('/home/handoff?state=invalid&locale=en')->assertUnprocessable();
    expect($admin->tokens()->count())->toBe(0);
});
