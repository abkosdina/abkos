<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;

$roles = Role::all();
foreach ($roles as $r) {
    $display = $r->display_name ?? 'NULL';
    $slug = $r->slug_fa ?? 'NULL';
    echo $r->id . '[' . $r->name . '] display_name=[' . $display . '] slug_fa=[' . $slug . ']\n';
}
