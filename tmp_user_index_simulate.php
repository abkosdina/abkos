<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$query = User::query()->with('roles');
$users = $query->paginate(15);

$payload = $users->map(function (User $user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'mobile' => $user->mobile,
        'status' => $user->status ?? 'active',
        'is_verified' => (bool) $user->is_verified,
        'is_suspended' => (bool) $user->is_suspended,
        'moderation_note' => $user->moderation_note,
        'created_at' => $user->created_at?->toISOString(),
        'roles' => $user->roles->pluck('name')->values()->all(),
        'role_labels' => $user->roles->map(fn ($role) => $role->slug_fa ?? $role->display_name ?? $role->name)->values()->all(),
        'permissions' => $user->getPermissionNames()->values()->all(),
    ];
});

echo 'TOTAL: ' . $users->total() . PHP_EOL;
foreach ($payload as $row) {
    echo $row['id'] . ' | ' . $row['name'] . ' | ' . implode(',', $row['roles']) . ' | perms=' . implode(',', $row['permissions']) . PHP_EOL;
}
