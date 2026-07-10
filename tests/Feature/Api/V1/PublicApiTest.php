<?php

use App\Models\User;

it('returns the public bootstrap contract', function () {
    $this->getJson('/api/v1/public/bootstrap')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'locale',
                'settings',
                'navigation',
                'footer_navigation',
                'auth',
            ],
        ]);
});

it('returns a configured public profile without authentication', function () {
    $user = User::factory()->create(['name' => 'API Public User']);
    $user->profile()->create([
        'public_url' => 'api-public-user',
        'bio' => 'Public API profile.',
    ]);

    $this->getJson('/api/v1/public/profiles/api-public-user')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.name', 'API Public User')
        ->assertJsonPath('data.details.public_url', 'api-public-user');
});

it('returns the API error envelope for an unknown public page', function () {
    $this->getJson('/api/v1/public/pages/not-a-real-page')
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'HTTP_404');
});
