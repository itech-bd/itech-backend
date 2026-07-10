<?php

namespace Modules\NewsUpdates\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\NewsUpdates\Models\NewsUpdate;

class DemoNewsSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('news_updates')) {
            return;
        }

        $items = [
            [
                'title' => 'New batch enrollment is open',
                'excerpt' => 'Limited seats with mentor-led support and weekly code reviews.',
                'body' => '<p>Enrollment is now open for the next batch. Get mentor-led support, weekly reviews, and project guidance.</p><ul><li>Weekly review sessions</li><li>Portfolio-focused projects</li><li>Dedicated mentor support</li></ul>',
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Portfolio & LinkedIn workshop announced',
                'excerpt' => 'Learn how to present your projects and improve your profile for jobs and freelancing.',
                'body' => '<p>Join our workshop on building a strong portfolio and optimizing your LinkedIn presence.</p><p>We will cover project storytelling, visuals, and practical profile improvements.</p>',
                'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Weekly mentor Q&A: client communication',
                'excerpt' => 'A practical Q&A session focused on proposals, scope, and handling real clients.',
                'body' => '<p>This session focuses on real-world client communication.</p><ol><li>How to write a proposal</li><li>How to define scope</li><li>How to handle change requests</li></ol>',
                'published_at' => now()->subDays(6),
            ],
            [
                'title' => 'New course resources added to the dashboard',
                'excerpt' => 'We’ve added new notes, checklists, and practice tasks for learners.',
                'body' => '<p>New resources are now available in the dashboard, including checklists and practice tasks to help you move faster.</p>',
                'published_at' => now()->subDays(8),
            ],
            [
                'title' => 'Project review schedule updated',
                'excerpt' => 'Updated timing for reviews to support both students and mentors.',
                'body' => '<p>We have updated the review schedule to reduce conflicts and improve feedback turnaround time.</p>',
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Community meetup: career guidance & networking',
                'excerpt' => 'Meet mentors and learners, share progress, and get career guidance.',
                'body' => '<p>We are hosting a community meetup for networking and career guidance.</p><p>Bring your questions and your portfolio links.</p>',
                'published_at' => now()->subDays(12),
            ],
        ];

        foreach ($items as $payload) {
            $slug = Str::slug($payload['title']);
            if ($slug === '') {
                continue;
            }

            NewsUpdate::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $payload['title'],
                    'excerpt' => $payload['excerpt'],
                    'body' => $payload['body'],
                    'status' => 'published',
                    'published_at' => $payload['published_at'],
                ]
            );
        }
    }
}
