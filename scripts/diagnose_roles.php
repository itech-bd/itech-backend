<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

$roles = Role::query()->withCount('users')->orderBy('name')->get(['id', 'name', 'guard_name']);

echo "Roles:\n";
foreach ($roles as $role) {
    echo "- {$role->id} {$role->name} ({$role->guard_name}) users={$role->users_count}\n";
}

echo "\nUsers (first 20):\n";
$users = User::query()->with('roles:name')->orderBy('id')->limit(20)->get(['id', 'name', 'email']);
foreach ($users as $user) {
    $roleNames = $user->roles->pluck('name')->values()->all();
    $rolesText = $roleNames ? implode(',', $roleNames) : '-';
    echo "- {$user->id} {$user->email} roles={$rolesText}\n";
}

$admin = User::role('admin')->first();
if (! $admin) {
    echo "\nNo user currently has role=admin\n";
    exit(0);
}

echo "\nAdmin permission checks for user_id={$admin->id} ({$admin->email}):\n";
$checks = ['readCourse', 'readBatch', 'readClassSchedule', 'addCourse', 'addBatch'];
foreach ($checks as $perm) {
    echo "- {$perm}=" . ($admin->can($perm) ? 'yes' : 'no') . "\n";
}
