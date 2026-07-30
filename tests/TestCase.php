<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

abstract class TestCase extends BaseTestCase
{
    // migrations are handled explicitly in setUp()

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure middleware aliases from Spatie are available during tests
        if ($this->app && isset($this->app['router'])) {
            $this->app['router']->aliasMiddleware('permission', PermissionMiddleware::class);
            $this->app['router']->aliasMiddleware('role', RoleMiddleware::class);
        }

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if ($database === ':memory:') {
            // For in-memory SQLite avoid `migrate:fresh` because it performs a DB wipe
            // which runs `VACUUM` and may fail inside a transaction. Instead run
            // the required migrations explicitly.
            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => 'database/migrations/0001_01_01_000000_create_users_table.php',
            ]);

            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => 'database/migrations/2026_07_09_000000_add_mobile_to_users.php',
            ]);

            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => 'database/migrations/2026_07_10_000001_create_personal_access_tokens_table.php',
            ]);

            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => 'database/migrations/2026_07_15_000001_create_provinces_table.php',
            ]);

            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => 'database/migrations/2026_07_15_000002_create_cities_table.php',
            ]);

            // Ensure workflow core tables exist (definitions, instances, logs)
            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => 'database/migrations/2026_07_07_000004_create_workflow_documents_and_communication_tables.php',
            ]);

            // Reconcile and add generic workflow columns/tables if missing
            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => 'database/migrations/2026_07_19_000000_create_generic_workflow_tables.php',
            ]);

            // Run workflow module migrations (adds additional workflow support tables)
            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => 'Modules/Workflow/Database/Migrations',
            ]);

            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => 'database/migrations/2026_07_07_000002_create_kyc_and_banking_tables.php',
            ]);

            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => 'Modules/Advertisements/Database/Migrations',
            ]);

            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => 'database/migrations/2026_07_19_000001_add_workflow_instance_to_advertisements.php',
            ]);

            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => 'Modules/Chat/Database/Migrations',
            ]);

            // Ensure Ledger and Wallet module migrations run for in-memory tests
            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => 'Modules/Ledger/Database/Migrations',
            ]);

            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => 'Modules/Wallet/Database/Migrations',
            ]);

            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => 'database/migrations/2026_07_15_000004_add_counters_to_advertisements_table.php',
            ]);

            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => 'database/migrations/2026_07_06_000000_create_permission_tables.php',
            ]);

            Artisan::call('migrate', [
                '--database' => $connection,
                '--path' => 'database/migrations/2026_07_13_000100_create_advertisement_favorites_and_views.php',
            ]);
        } else {
            Artisan::call('migrate:fresh', ['--database' => $connection]);
        }

        if (Schema::hasTable('roles')) {
            foreach (['user', 'operator', 'admin', 'senior-operator'] as $roleName) {
                Role::findOrCreate($roleName, 'web');
            }
        }

        // Ensure a minimal set of users exist for tests that rely on specific ids
        if (Schema::hasTable('users')) {
            $now = now();
            // Insert id 1 and 99 if they don't exist
            if (! \Illuminate\Support\Facades\DB::table('users')->where('id', 1)->exists()) {
                \Illuminate\Support\Facades\DB::table('users')->insert([
                    'id' => 1,
                    'name' => 'Test User 1',
                    'email' => 'user1@example.test',
                    'password' => bcrypt('password'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            if (! \Illuminate\Support\Facades\DB::table('users')->where('id', 99)->exists()) {
                \Illuminate\Support\Facades\DB::table('users')->insert([
                    'id' => 99,
                    'name' => 'Test User 99',
                    'email' => 'user99@example.test',
                    'password' => bcrypt('password'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Add compatibility columns to negotiations expected by older tests
            if (Schema::hasTable('negotiations')) {
                if (! Schema::hasColumn('negotiations', 'buyer_id')) {
                    Schema::table('negotiations', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->unsignedBigInteger('buyer_id')->nullable()->after('advertisement_id');
                    });
                }
                if (! Schema::hasColumn('negotiations', 'seller_id')) {
                    Schema::table('negotiations', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->unsignedBigInteger('seller_id')->nullable()->after('buyer_id');
                    });
                }

                // Backwards-compat: older schema used initiator_user_id/counterparty_user_id
                if (! Schema::hasColumn('negotiations', 'initiator_user_id')) {
                    Schema::table('negotiations', function (Blueprint $table) {
                        $table->unsignedBigInteger('initiator_user_id')->nullable()->after('advertisement_id');
                    });
                }
                if (! Schema::hasColumn('negotiations', 'counterparty_user_id')) {
                    Schema::table('negotiations', function (Blueprint $table) {
                        $table->unsignedBigInteger('counterparty_user_id')->nullable()->after('initiator_user_id');
                    });
                }
            }

            if (Schema::hasTable('advertisements') && ! Schema::hasColumn('advertisements', 'advertisement_number')) {
                Schema::table('advertisements', function (Blueprint $table) {
                    $table->string('advertisement_number')->unique()->after('uuid');
                });
            }
        }
    }
}
