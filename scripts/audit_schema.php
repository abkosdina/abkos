<?php

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;

$root = realpath(__DIR__ . '/..');
$dotenv = Dotenv::createImmutable($root);
$dotenv->safeLoad();

$capsule = new Capsule();
$capsule->addConnection([
    'driver' => getenv('DB_CONNECTION') ?: 'mysql',
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_DATABASE'),
    'username' => getenv('DB_USERNAME'),
    'password' => getenv('DB_PASSWORD'),
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
    'collation' => getenv('DB_COLLATION') ?: 'utf8mb4_unicode_ci',
    'prefix' => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();
$db = $capsule->getDatabaseManager()->getDefaultConnection();
$schema = $capsule->getDatabaseManager()->connection($db)->getDatabaseName();

$storage = $root . '/storage/audit';
if (!is_dir($storage)) {
    mkdir($storage, 0777, true);
}

function writeJson(string $filename, $data): void
{
    global $storage;
    file_put_contents($storage . '/' . $filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$tables = $capsule->getDatabaseManager()->select('SELECT TABLE_NAME,TABLE_TYPE,ENGINE,TABLE_ROWS,TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA=? ORDER BY TABLE_NAME', [$schema]);
writeJson('schema_tables.json', $tables);

$columns = $capsule->getDatabaseManager()->select('SELECT TABLE_NAME,COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,COLUMN_DEFAULT,CHARACTER_SET_NAME,EXTRA,DATA_TYPE,COLUMN_KEY,ORDINAL_POSITION FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? ORDER BY TABLE_NAME,ORDINAL_POSITION', [$schema]);
writeJson('schema_columns.json', $columns);

$indexes = $capsule->getDatabaseManager()->select('SELECT TABLE_NAME,INDEX_NAME,COLUMN_NAME,NON_UNIQUE,SEQ_IN_INDEX,INDEX_TYPE,COLLATION_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=? ORDER BY TABLE_NAME,INDEX_NAME,SEQ_IN_INDEX', [$schema]);
writeJson('schema_indexes.json', $indexes);

$fks = $capsule->getDatabaseManager()->select('SELECT k.TABLE_NAME,k.CONSTRAINT_NAME,k.COLUMN_NAME,k.REFERENCED_TABLE_NAME,k.REFERENCED_COLUMN_NAME,c.UPDATE_RULE,c.DELETE_RULE FROM information_schema.KEY_COLUMN_USAGE k LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS c ON k.CONSTRAINT_SCHEMA=c.CONSTRAINT_SCHEMA AND k.CONSTRAINT_NAME=c.CONSTRAINT_NAME WHERE k.TABLE_SCHEMA=? AND k.REFERENCED_TABLE_NAME IS NOT NULL ORDER BY k.TABLE_NAME,k.CONSTRAINT_NAME,k.ORDINAL_POSITION', [$schema]);
writeJson('schema_fks.json', $fks);

function scanMigrations(string $dir): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $path = str_replace('\\', '/', str_replace(realpath(__DIR__ . '/..') . '/', '', $file->getRealPath()));
        $files[] = $path;
    }
    sort($files);
    return $files;
}

$migrations = scanMigrations($root . '/database/migrations');
$legacy = scanMigrations($root . '/database/migrations/legacy');
$modules = scanMigrations($root . '/Modules');
writeJson('migration_inventory.json', [
    'root' => $migrations,
    'legacy' => $legacy,
    'modules' => array_filter($modules, fn($path)=>str_contains($path, '/Database/Migrations/')),
]);

echo "audit schema script written\n";
