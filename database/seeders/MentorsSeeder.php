<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Mentors\Models\Mentor;

class MentorsSeeder extends Seeder
{
    public function run(): void
    {
        $demo = [
            ['name' => 'Arif Hasan', 'topic' => 'Web Development', 'bio' => 'Laravel + Tailwind mentor. Focus on clean code and real projects.'],
            ['name' => 'Nusrat Jahan', 'topic' => 'SEO', 'bio' => 'Technical SEO, keyword research, and reporting best practices.'],
            ['name' => 'Sabbir Ahmed', 'topic' => '.NET', 'bio' => 'C# fundamentals to ASP.NET Core API development.'],
            ['name' => 'Farzana Islam', 'topic' => 'Graphics Design', 'bio' => 'Branding, typography, and modern layout systems.'],
            ['name' => 'Mehedi Hasan', 'topic' => 'UI/UX', 'bio' => 'Figma workflows, UX thinking, and design systems.'],
            ['name' => 'Tahmid Rahman', 'topic' => 'Flutter', 'bio' => 'Mobile app architecture and practical state management.'],
            ['name' => 'Shakil Hossain', 'topic' => 'DevOps', 'bio' => 'Deployments, CI/CD basics, and server hygiene.'],
            ['name' => 'Sadia Akter', 'topic' => 'Data Analytics', 'bio' => 'Dashboards, data storytelling, and practical analysis.'],
            ['name' => 'Imran Kabir', 'topic' => 'Cybersecurity', 'bio' => 'Security fundamentals, OWASP, and safe development habits.'],
            ['name' => 'Rafiul Islam', 'topic' => 'QA', 'bio' => 'Testing mindset, automation basics, and quality processes.'],
            ['name' => 'Samia Sultana', 'topic' => 'Digital Marketing', 'bio' => 'Campaign strategy, tracking, and conversion optimization.'],
            ['name' => 'Zahidul Karim', 'topic' => 'Project Management', 'bio' => 'Agile basics, planning, and stakeholder communication.'],
        ];

        foreach ($demo as $i => $m) {
            $email = 'mentor'.($i + 1).'@example.com';

            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $m['name'],
                    'password' => '12345678',
                    'must_change_password' => true,
                ]
            );

            if (! $user->hasRole('mentor')) {
                $user->assignRole('mentor');
            }

            Mentor::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $m['name'],
                    'topic' => $m['topic'],
                    'bio' => $m['bio'],
                    'is_active' => true,
                ]
            );
        }
    }
}
