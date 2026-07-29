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

$tables = Capsule::connection()->select('SELECT TABLE_NAME,TABLE_TYPE,ENGINE,TABLE_ROWS,TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA=? ORDER BY TABLE_NAME', [$schema]);
writeJson('schema_tables.json', $tables);

$columns = Capsule::connection()->select('SELECT TABLE_NAME,COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,COLUMN_DEFAULT,CHARACTER_SET_NAME,EXTRA,DATA_TYPE,COLUMN_KEY,ORDINAL_POSITION FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? ORDER BY TABLE_NAME,ORDINAL_POSITION', [$schema]);
writeJson('schema_columns.json', $columns);

$indexes = Capsule::connection()->select('SELECT TABLE_NAME,INDEX_NAME,COLUMN_NAME,NON_UNIQUE,SEQ_IN_INDEX,INDEX_TYPE,COLLATION_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=? ORDER BY TABLE_NAME,INDEX_NAME,SEQ_IN_INDEX', [$schema]);
writeJson('schema_indexes.json', $indexes);

$fks = Capsule::connection()->select('SELECT k.TABLE_NAME,k.CONSTRAINT_NAME,k.COLUMN_NAME,k.REFERENCED_TABLE_NAME,k.REFERENCED_COLUMN_NAME,c.UPDATE_RULE,c.DELETE_RULE FROM information_schema.KEY_COLUMN_USAGE k LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS c ON k.CONSTRAINT_SCHEMA=c.CONSTRAINT_SCHEMA AND k.CONSTRAINT_NAME=c.CONSTRAINT_NAME WHERE k.TABLE_SCHEMA=? AND k.REFERENCED_TABLE_NAME IS NOT NULL ORDER BY k.TABLE_NAME,k.CONSTRAINT_NAME,k.ORDINAL_POSITION', [$schema]);
writeJson('schema_fks.json', $fks);

$migrations = [];
foreach (glob($root . '/database/migrations/*.php') as $file) {
    $migrations[] = str_replace('\\', '/', substr($file, strlen($root) + 1));
}
$legacy = [];
foreach (glob($root . '/database/migrations/legacy/*.php') as $file) {
    $legacy[] = str_replace('\\', '/', substr($file, strlen($root) + 1));
}
$moduleMigrations = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/Modules'));
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $path = str_replace('\\', '/', substr($file->getRealPath(), strlen($root) + 1));
    if (str_contains($path, '/Database/Migrations/')) {
        $moduleMigrations[] = $path;
    }
}
writeJson('migration_inventory.json', [
    'root' => $migrations,
    'legacy' => $legacy,
    'modules' => $moduleMigrations,
]);

echo "audit schema extraction complete\n";
