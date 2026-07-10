<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$userId = (int) ($argv[1] ?? 28);

$user = App\Models\User::query()->find($userId);

echo 'user_id=' . $userId . PHP_EOL;
if (! $user) {
    echo "user=null" . PHP_EOL;
    exit(0);
}

echo 'email=' . $user->email . PHP_EOL;
echo 'hasRole(admin)=' . ($user->hasRole('admin') ? 'yes' : 'no') . PHP_EOL;
echo 'can(addBatch)=' . ($user->can('addBatch') ? 'yes' : 'no') . PHP_EOL;

echo 'Gate viewAny Batch=' . (Illuminate\Support\Facades\Gate::forUser($user)->allows('viewAny', Modules\Batch\Models\Batch::class) ? 'yes' : 'no') . PHP_EOL;
echo 'Gate create Batch=' . (Illuminate\Support\Facades\Gate::forUser($user)->allows('create', Modules\Batch\Models\Batch::class) ? 'yes' : 'no') . PHP_EOL;
