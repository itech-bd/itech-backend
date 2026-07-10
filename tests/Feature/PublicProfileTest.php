<?php

use App\Models\User;

test('public profile can be viewed without login', function () {
    $user = User::factory()->create([
        'name' => 'Public User',
    ]);

    $user->profile()->create([
        'public_url' => 'public-user',
        'bio' => 'Hello world',
    ]);

    $this
        ->get('/p/public-user')
        ->assertOk()
        ->assertSee('Public User');
});

test('missing public profile returns 404', function () {
    $this->get('/p/does-not-exist')->assertNotFound();
});
