<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "📊 DATABASE SCHEMA INSPECTION\n";
echo "════════════════════════════════════════\n\n";

// Check users table columns
echo "👥 USERS TABLE COLUMNS:\n";
echo "─────────────────────────────────────\n";
$columns = Schema::getColumns('users');
foreach ($columns as $column) {
    echo "  • {$column['name']} ({$column['type']})\n";
}

// Check if certain columns exist
echo "\n🔍 CHECKING FOR SPECIFIC COLUMNS:\n";
echo "─────────────────────────────────────\n";
$specific = ['is_verified', 'is_vip', 'profile_photo_path', 'profile_photo_url', 'status'];
foreach ($specific as $col) {
    $exists = Schema::hasColumn('users', $col);
    echo "  " . ($exists ? "✅" : "❌") . " $col\n";
}

// List all tables
echo "\n📋 ALL TABLES IN DATABASE:\n";
echo "─────────────────────────────────────\n";
$tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;");
foreach ($tables as $table) {
    echo "  • {$table->name}\n";
}
