<?php

use App\Support\TextEncoding;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Modules\Course\Models\Course;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('courses:backfill-fees {--fee=} {--dry-run}', function () {
    if (!Schema::hasTable('courses')) {
        $this->error('Table "courses" does not exist. Run: php artisan migrate');
        return 1;
    }

    $hasOldPrice = Schema::hasColumn('courses', 'old_price');
    $hasDiscount = Schema::hasColumn('courses', 'discount_price');

    if (!$hasOldPrice || !$hasDiscount) {
        $this->error('Missing price columns on "courses" table.');
        $this->line('Run migrations first (Course module):');
        $this->line('  php artisan migrate');
        $this->line('Then run this command again.');
        return 1;
    }

    $feeOption = $this->option('fee');
    $dryRun = (bool) $this->option('dry-run');

    if ($feeOption !== null && (!is_numeric($feeOption) || (float) $feeOption < 0)) {
        $this->error('Invalid --fee value. Provide a non-negative number.');
        return 1;
    }

    $defaultFee = $feeOption !== null ? (float) $feeOption : null;

    $courses = Course::query()
        ->whereNull('old_price')
        ->orderBy('id')
        ->get(['id', 'title', 'old_price', 'discount_price']);

    if ($courses->isEmpty()) {
        $this->info('No courses found with missing fee (old_price is already set).');
        return 0;
    }

    $this->info('Courses missing fee: ' . $courses->count());
    if ($dryRun) {
        $this->warn('Dry-run enabled: no database changes will be made.');
    }

    $updated = 0;

    foreach ($courses as $course) {
        $newFee = null;

        if (!is_null($course->discount_price)) {
            $newFee = (float) $course->discount_price;
        } elseif ($defaultFee !== null) {
            $newFee = $defaultFee;
        }

        if ($newFee === null) {
            $this->line("- [skip] #{$course->id} {$course->title} (no discount_price; pass --fee=...)");
            continue;
        }

        $this->line("- [set]  #{$course->id} {$course->title} => old_price={$newFee}");

        if (!$dryRun) {
            $course->forceFill(['old_price' => $newFee])->save();
        }

        $updated++;
    }

    $this->info("Done. Updated {$updated} course(s).");
    return 0;
})->purpose('Backfill missing course fees (sets old_price when null).');

Artisan::command('user:make-admin {user : User id or email}', function () {
    /** @var string $userInput */
    $userInput = (string) $this->argument('user');

    $user = is_numeric($userInput)
        ? User::query()->find((int) $userInput)
        : User::query()->where('email', $userInput)->first();

    if (! $user) {
        $this->error('User not found: ' . $userInput);
        return 1;
    }

    $adminRole = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $user->assignRole($adminRole);

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->info("OK: {$user->email} is now role=admin");
    return 0;
})->purpose('Assign the admin role to a user (by email or id).');

Artisan::command('user:verify-email {user : User id or email} {--force : Re-verify even if already verified}', function () {
    /** @var string $userInput */
    $userInput = (string) $this->argument('user');

    $user = is_numeric($userInput)
        ? User::query()->find((int) $userInput)
        : User::query()->where('email', $userInput)->first();

    if (! $user) {
        $this->error('User not found: ' . $userInput);
        return 1;
    }

    $alreadyVerified = method_exists($user, 'hasVerifiedEmail') && $user->hasVerifiedEmail();
    $force = (bool) $this->option('force');

    if ($alreadyVerified && ! $force) {
        $this->info('Already verified: ' . $user->email);
        $this->line('email_verified_at=' . (string) ($user->email_verified_at ?? ''));
        return 0;
    }

    if (method_exists($user, 'markEmailAsVerified')) {
        $user->markEmailAsVerified();
    } else {
        $user->forceFill(['email_verified_at' => now()])->save();
    }

    $user->refresh();
    $this->info('OK: verified email for ' . $user->email);
    $this->line('email_verified_at=' . (string) ($user->email_verified_at ?? ''));
    return 0;
})->purpose('Mark a user email as verified (sets email_verified_at).');

Artisan::command('courses:repair-text-encoding {--dry-run : Show what would change without saving}', function () {
    if (! Schema::hasTable('courses')) {
        $this->error('Table "courses" does not exist. Run: php artisan migrate');
        return 1;
    }

    $updated = 0;
    $dryRun = (bool) $this->option('dry-run');

    Course::query()->orderBy('id')->chunkById(100, function ($courses) use (&$updated, $dryRun) {
        foreach ($courses as $course) {
            $changes = [];

            foreach (['title', 'description', 'slug', 'thumbnail'] as $field) {
                $original = $course->{$field};
                $repaired = TextEncoding::repairMojibake($original);

                if ($repaired !== $original) {
                    $changes[$field] = $repaired;
                }
            }

            if ($changes === []) {
                continue;
            }

            $updated++;
            $this->line("- [repair] #{$course->id} {$course->title}");

            if (! $dryRun) {
                $course->forceFill($changes)->save();
            }
        }
    });

    $this->info($dryRun
        ? "Dry run complete. {$updated} course(s) need repair."
        : "Repair complete. Updated {$updated} course(s).");

    return 0;
})->purpose('Repair mojibake/encoding issues in course text fields.');
