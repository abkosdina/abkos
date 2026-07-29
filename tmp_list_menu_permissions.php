<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$permissions = Spatie\Permission\Models\Permission::where('name', 'like', 'menu.%')->get();
if ($permissions->isEmpty()) {
    echo "NO_MENU_PERMISSIONS_FOUND\n";
    exit(0);
}
foreach ($permissions as $permission) {
    echo $permission->name . ' / ' . ($permission->display_name ?? '-') . ' / ' . ($permission->slug_fa ?? '-') . PHP_EOL;
}
