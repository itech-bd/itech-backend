<?php

use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Modules\Batch\Models\Batch;
use Modules\Batch\Models\ClassSchedule;
use Modules\Course\Models\Course;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test(
    'creating a batch auto-generates schedules for selected weekdays',
    function () {
        $adminRole = Role::query()->firstOrCreate(['name' => 'admin']);
        $addBatch = Permission::query()->firstOrCreate(['name' => 'addBatch']);
        $adminRole->givePermissionTo($addBatch);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $creator = User::factory()->create();
        $course = Course::query()->create(
            [
                'title' => 'Auto Schedule Course',
                'description' => 'Test description',
                'old_price' => 9000,
                'discount_price' => 7000,
                'thumbnail' => null,
                'status' => 'active',
                'created_by' => $creator->id,
            ]
        );

        $start = now()->startOfWeek(Carbon::MONDAY);
        $end = $start->copy()->addDays(13);

        $classDays = ['Monday', 'Wednesday', 'Friday'];

        $this
            ->actingAs($admin)
            ->post(
                route('dashboard.batches.store.course', $course),
                [
                    'name' => 'Batch Auto Schedule',
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'class_days' => $classDays,
                    'class_time' => '10:00 AM',
                    'status' => 'upcoming',
                ]
            )
            ->assertRedirect(route('dashboard.batches.index'));

        $batch = Batch::query()->where('name', 'Batch Auto Schedule')->first();
        expect($batch)->not->toBeNull();

        $expectedDates = [];
        foreach (CarbonPeriod::create($start, $end) as $date) {
            if (in_array($date->format('l'), $classDays, true)) {
                $expectedDates[] = $date->toDateString();
            }
        }

        $actualDates = ClassSchedule::query()
            ->where('batch_id', $batch->id)
            ->orderBy('class_date')
            ->get()
            ->map(fn ($s) => $s->class_date?->toDateString())
            ->all();

        expect($actualDates)->toBe($expectedDates);

        $first = ClassSchedule::query()
            ->where('batch_id', $batch->id)
            ->orderBy('class_date')
            ->first();

        expect($first)->not->toBeNull();
        expect((int) $first->created_by)->toBe((int) $admin->id);
        expect((string) $first->topic)->toContain('Class 1');
    }
);
