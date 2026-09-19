<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->admin->assignRole(Role::findOrCreate('admin', 'web'));
});

it('renders access directories and searchable assignment previews', function () {
    $role = Role::findOrCreate('course-manager', 'web');
    $permission = Permission::findOrCreate('readCourse', 'web');
    $role->givePermissionTo($permission);
    Permission::findOrCreate('unassignedAction', 'web');
    $this->actingAs($this->admin);
    foreach (['roles', 'permissions'] as $entity) {
        $this->get(route($entity.'.index'))->assertOk()->assertSee('Access management')
            ->assertViewHas('stats', fn ($stats) => $stats['unassigned'] === 1);
        $this->getJson(route($entity.'.index').'?draw=1&start=0&length=10', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonStructure(['data' => [['name', 'access', 'actions']]])
            ->assertSee('readCourse')->assertSee('course-manager');
    }
    $query = http_build_query(['draw' => 1, 'search' => ['value' => 'missing-role'], 'columns' => [['data' => 'name', 'name' => 'name', 'searchable' => 'true']]]);
    $this->getJson('/roles?'.$query, ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()->assertJsonPath('recordsFiltered', 0);
});

it('renders shared forms and saves role permission changes', function () {
    $permission = Permission::findOrCreate('readCourse', 'web');
    $this->actingAs($this->admin)->get(route('roles.create'))->assertOk()->assertSee('Assign permissions');
    $this->post(route('roles.store'), ['name' => 'course-manager', 'permissions' => [$permission->id]])
        ->assertRedirect(route('roles.index'))->assertSessionHasNoErrors();
    $role = Role::findByName('course-manager', 'web');
    expect($role->hasPermissionTo($permission))->toBeTrue();
    $this->get(route('roles.edit', $role))->assertOk()->assertSee('1 selected');
    $this->put(route('roles.update', $role), ['name' => 'course-manager'])->assertRedirect(route('roles.index'));
    expect($role->fresh()->permissions)->toHaveCount(0);
    $this->get(route('permissions.create'))->assertOk()->assertSee('Permission details');
    $this->get(route('permissions.edit', $permission))->assertOk()->assertSee('readCourse');
});

it('escapes record names in action buttons and assignment previews', function () {
    $role = Role::findOrCreate('<script>alert(1)</script>', 'web');
    $permission = Permission::findOrCreate('<img src=x onerror=alert(1)>', 'web');
    $role->givePermissionTo($permission);
    $response = $this->actingAs($this->admin)->getJson('/roles?draw=1', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
    $row = collect($response->json('data'))->firstWhere('id', $role->id);
    expect($row['name'])->not->toContain('<script>');
    expect($row['actions'])->not->toContain('<script>');
    expect($row['access'])->not->toContain('<img');
});

it('keeps access management restricted to admins', function () {
    $this->actingAs(User::factory()->create());
    foreach (['roles', 'permissions'] as $entity) {
        $this->get(route($entity.'.index'))->assertForbidden();
        $this->get(route($entity.'.create'))->assertForbidden();
        $this->post(route($entity.'.store'), ['name' => 'unauthorized'])->assertForbidden();
    }
});
