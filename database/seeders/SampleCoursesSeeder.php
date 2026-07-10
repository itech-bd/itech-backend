<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Course\Models\Course;

class SampleCoursesSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->role('admin')->orderBy('id')->first()
            ?: User::query()->orderBy('id')->first();

        $createdBy = $admin ? (int) $admin->id : 1;

        $courses = [
            [
                'title' => 'Laravel Fundamentals',
                'description' => 'Build modern web apps with Laravel 12, routing, Eloquent, auth, and Blade.',
                'thumbnail' => null,
                'status' => 'active',
            ],
            [
                'title' => 'Flutter Bootcamp',
                'description' => 'Learn Flutter from basics to real mobile projects with state management.',
                'thumbnail' => null,
                'status' => 'active',
            ],
            [
                'title' => 'UI/UX Design Essentials',
                'description' => 'Design thinking, Figma workflows, wireframes, and usable interfaces.',
                'thumbnail' => null,
                'status' => 'active',
            ],
            [
                'title' => 'SEO & Digital Growth',
                'description' => 'Keyword research, on-page SEO, reporting, and growth fundamentals.',
                'thumbnail' => null,
                'status' => 'active',
            ],
        ];

        foreach ($courses as $data) {
            Course::query()->updateOrCreate(
                ['title' => $data['title']],
                $data + ['created_by' => $createdBy]
            );
        }
    }
}
