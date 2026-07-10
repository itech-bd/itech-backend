<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed a ready-to-login admin account.
     */
    public function run(): void
    {
        Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@itechbd.test'],
            [
                'name' => 'Super Admin',
                'password' => '12345678',
                'must_change_password' => false,
                'email_verified_at' => Carbon::now(),
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
