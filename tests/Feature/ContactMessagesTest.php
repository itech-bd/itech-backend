<?php

use App\Models\User;
use Modules\ContactMessages\Models\ContactMessage;
use Spatie\Permission\Models\Role;

it('stores contact messages from the public contact form', function () {
    $response = $this->post('/contact', [
        'name' => 'Visitor Example',
        'email' => 'visitor@example.com',
        'phone' => '+8801712345678',
        'subject' => 'Need course details',
        'message' => 'Please share the next batch schedule.',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/contact#contact-form');

    $message = ContactMessage::query()->first();

    expect($message)->not->toBeNull();
    expect($message->name)->toBe('Visitor Example');
    expect($message->email)->toBe('visitor@example.com');
    expect($message->subject)->toBe('Need course details');
    expect($message->read_at)->toBeNull();
});

it('allows admins to review contact messages and marks them as read on open', function () {
    $adminRole = Role::findOrCreate('admin');

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $message = ContactMessage::query()->create([
        'name' => 'Prospective Student',
        'email' => 'student@example.com',
        'subject' => 'Admission query',
        'message' => 'I want to know about the next intake.',
    ]);

    $this->actingAs($admin)
        ->get('/dashboard/contact-messages')
        ->assertOk()
        ->assertSee('Contact Messages');

    $this->actingAs($admin)
        ->withHeader('X-Requested-With', 'XMLHttpRequest')
        ->get('/dashboard/contact-messages?draw=1&start=0&length=10')
        ->assertOk()
        ->assertJsonFragment(['name' => 'Prospective Student'])
        ->assertJsonFragment(['email' => 'student@example.com']);

    $this->actingAs($admin)
        ->get('/dashboard/contact-messages/' . $message->getRouteKey())
        ->assertOk()
        ->assertSee('Admission query')
        ->assertSee('I want to know about the next intake.');

    expect($message->fresh()->read_at)->not->toBeNull();
});

it('allows admins to delete a contact message', function () {
    $adminRole = Role::findOrCreate('admin');

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $message = ContactMessage::query()->create([
        'name' => 'Delete Me',
        'email' => 'delete@example.com',
        'subject' => 'Removal request',
        'message' => 'This message should be deleted by admin.',
    ]);

    $this->actingAs($admin)
        ->delete('/dashboard/contact-messages/' . $message->getRouteKey())
        ->assertRedirect('/dashboard/contact-messages');

    expect(ContactMessage::query()->find($message->id))->toBeNull();
});

it('prevents non-admin users from opening admin contact messages', function () {
    $user = User::factory()->create();

    $message = ContactMessage::query()->create([
        'name' => 'Blocked Visitor',
        'email' => 'blocked@example.com',
        'subject' => 'Protected',
        'message' => 'This should not be visible to regular users.',
    ]);

    $this->actingAs($user)
        ->get('/dashboard/contact-messages/' . $message->getRouteKey())
        ->assertForbidden();
});
