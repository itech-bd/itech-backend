<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Create sample student accounts.
 */
class StudentsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demo = [
            'Aminul Islam',
            'Nadia Rahman',
            'Raihan Hossain',
            'Jannat Ara',
            'Fahim Ahmed',
            'Sabrina Akter',
            'Tanvir Hasan',
            'Mim Sultana',
            'Rakibul Islam',
            'Sumaiya Khan',
            'Shuvo Islam',
            'Raisa Ahmed',
        ];

        foreach ($demo as $i => $name) {
            $email = 'student'.($i + 1).'@example.com';

            Student::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => '12345678',
                    'must_change_password' => true,
                    'email_verified_at' => Carbon::now(),
                ]
            );
        }
    }
}
