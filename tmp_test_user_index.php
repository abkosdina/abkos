<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$query = User::query()->with('roles')->orderByDesc('created_at');
$users = $query->paginate(15);

echo 'SQL: ' . $query->toSql() . PHP_EOL;
echo 'COUNT:' . $users->total() . PHP_EOL;
foreach ($users as $user) {
    echo $user->id . ' | ' . $user->name . ' | ' . $user->email . ' | ' . implode(',', $user->roles->pluck('name')->toArray()) . PHP_EOL;
}
