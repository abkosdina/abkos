<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['advertisements', 'advertisement_views', 'advertisement_workflow_audits'];
foreach ($tables as $table) {
    echo "\n== {$table} ==\n";
    $cols = Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM `{$table}`");
    foreach ($cols as $col) {
        echo $col->Field . ' ' . $col->Type . ' null=' . ($col->Null === 'YES' ? 'yes' : 'no') . ' key=' . $col->Key . ' extra=' . $col->Extra . PHP_EOL;
    }
    echo "-- indexes --\n";
    foreach (Illuminate\Support\Facades\DB::select("SHOW INDEX FROM `{$table}`") as $idx) {
        echo $idx->Key_name . ' ' . $idx->Column_name . ' ' . $idx->Non_unique . PHP_EOL;
    }
    echo "-- fks --\n";
    foreach (Illuminate\Support\Facades\DB::select("SELECT COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL", [$table]) as $fk) {
        echo $fk->COLUMN_NAME . ' -> ' . $fk->REFERENCED_TABLE_NAME . '.' . $fk->REFERENCED_COLUMN_NAME . ' (' . $fk->CONSTRAINT_NAME . ')' . PHP_EOL;
    }
}
