<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Reviews\Models\Review;

class SampleReviewsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->role('admin')->orderBy('id')->first()
            ?: User::query()->orderBy('id')->first();

        $createdBy = $admin ? (int) $admin->id : 1;

        $reviews = [
            [
                'name' => 'Ayesha Rahman',
                'designation' => 'Student, Web Development',
                'quote' => 'The course was structured and practical. Assignments and mentor feedback made it easy to improve week by week.',
                'rating' => 5,
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'name' => 'Tanvir Ahmed',
                'designation' => 'Student, Flutter',
                'quote' => 'Loved the hands-on projects. The pacing was perfect and the support in the community group was excellent.',
                'rating' => 5,
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'name' => 'Nusrat Jahan',
                'designation' => 'Student, UI/UX',
                'quote' => 'Clear guidance and real-world examples. I feel confident building better layouts and understanding user needs.',
                'rating' => 4,
                'status' => 'active',
                'sort_order' => 3,
            ],
            [
                'name' => 'Imran Hossain',
                'designation' => 'Student, SEO',
                'quote' => 'Great fundamentals with actionable checklists. I was able to optimize my site and see results quickly.',
                'rating' => 4,
                'status' => 'active',
                'sort_order' => 4,
            ],
            [
                'name' => 'Sadia Islam',
                'designation' => 'Alumni',
                'quote' => 'A very helpful learning experience. The curriculum and instructor support helped me stay consistent.',
                'rating' => 5,
                'status' => 'inactive',
                'sort_order' => 99,
            ],
        ];

        foreach ($reviews as $data) {
            Review::query()->updateOrCreate(
                ['name' => $data['name']],
                $data + ['created_by' => $createdBy]
            );
        }
    }
}
