<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\FrontendPage;
use App\Models\FrontendSection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\BatchAssignmentsAndSchedulesSeeder;
use Database\Seeders\FrontendSettingsSeeder;
use Database\Seeders\MentorsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SampleBatchesSeeder;
use Database\Seeders\SampleCoursesSeeder;
use Database\Seeders\SampleReviewsSeeder;
use Database\Seeders\SuperAdminSeeder;
use Database\Seeders\StudentsSeeder;
use Database\Seeders\DemoStudentSeeder;
use Database\Seeders\FrontendContentSeeder;
use Modules\Batch\Models\Batch;
use Modules\Course\Models\Course;
use Modules\Mentors\Models\Mentor;
use Modules\Reviews\Models\Review;

/**
 * Seeds the application with demo/initial data.
 *
 * @category Database
 * @package  Database\Seeders
 * @author   Unknown <unknown@example.invalid>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://laravel.com
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(SuperAdminSeeder::class);
        $this->call(DemoStudentSeeder::class);

        // Demo/sample content is intentionally opt-in so importing a real SQL dump
        // is not overwritten by a later `db:seed`.
        if ((bool) env('DEMO_CONTENT_SEED', false)) {
            if (Mentor::query()->count() === 0) {
                $this->call(MentorsSeeder::class);
            }

            if (User::query()->role('student')->count() === 0) {
                $this->call(StudentsSeeder::class);
            }

            if (Course::query()->count() === 0) {
                $this->call(SampleCoursesSeeder::class);
            }

            if (Review::query()->count() === 0) {
                $this->call(SampleReviewsSeeder::class);
            }

            if (Batch::query()->count() === 0) {
                $this->call(SampleBatchesSeeder::class);
                $this->call(BatchAssignmentsAndSchedulesSeeder::class);
            }

            if (FrontendPage::query()->count() === 0 && FrontendSection::query()->count() === 0) {
                $this->call(FrontendContentSeeder::class);
            }

            $this->call(FrontendSettingsSeeder::class);
        }

        User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => '12345678',
            ]
        );
    }
}
