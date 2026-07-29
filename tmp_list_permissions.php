<?php
require __DIR__ . '/vendor/autoload.php';
use Spatie\Permission\Models\Permission;
foreach (Permission::all() as $p) {
    $slug = $p->slug_fa;
    if (!$slug || strpbrk($slug, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')) {
        echo $p->id . ' | ' . $p->name . ' | ' . $p->display_name . ' | ' . ($slug ?: 'NULL') . PHP_EOL;
    }
}
