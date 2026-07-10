<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Modules\Batch\Models\Batch;
use Spatie\Permission\Models\Role;

class DemoStudentSeeder extends Seeder
{
    /**
     * Seed a ready-to-login student account and attach one visible batch when available.
     */
    public function run(): void
    {
        Role::query()->firstOrCreate([
            'name' => 'student',
            'guard_name' => 'web',
        ]);

        $student = User::query()->updateOrCreate(
            ['email' => 'student@itechbd.test'],
            [
                'name' => 'Demo Student',
                'password' => '12345678',
                'must_change_password' => false,
                'email_verified_at' => Carbon::now(),
            ]
        );

        if (! $student->hasRole('student')) {
            $student->assignRole('student');
        }

        $batch = Batch::query()
            ->whereIn('status', ['upcoming', 'running'])
            ->orderBy('start_date')
            ->orderBy('id')
            ->first();

        if ($batch) {
            $student->studentBatches()->syncWithoutDetaching([
                $batch->id => [
                    'status' => 'approved',
                    'batch_type' => 'online',
                    'approved_at' => Carbon::now(),
                    'approved_by' => null,
                ],
            ]);
        }
    }
}
