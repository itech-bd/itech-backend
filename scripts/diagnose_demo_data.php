<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Modules\Batch\Models\Batch;
use Modules\Batch\Models\ClassSchedule;
use Modules\Course\Models\Course;

echo "Counts:\n";
echo "- courses=" . Course::query()->count() . "\n";
echo "- batches=" . Batch::query()->count() . "\n";
echo "- schedules=" . ClassSchedule::query()->count() . "\n";
echo "- mentors=" . User::role('mentor')->count() . "\n";
echo "- students=" . User::role('student')->count() . "\n";

$batch = Batch::query()->withCount(['mentors', 'students', 'classSchedules'])->with('course:id,title')->orderBy('id')->first();
if ($batch) {
    echo "\nSample batch:\n";
    echo "- id={$batch->id}\n";
    echo "- name={$batch->name}\n";
    echo "- course=" . ($batch->course?->title ?? '-') . "\n";
    echo "- mentors={$batch->mentors_count}, students={$batch->students_count}, schedules={$batch->class_schedules_count}\n";
}
