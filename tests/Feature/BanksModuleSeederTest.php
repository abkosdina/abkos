<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Banks\Database\Seeders\BanksModuleSeeder;
use Tests\TestCase;

class BanksModuleSeederTest extends TestCase
{
    public function test_banks_module_seeder_inserts_banks_and_loan_plans(): void
    {
        $connection = config('database.default');
        $migrationPath = database_path('migrations/2026_07_07_000002_create_kyc_and_banking_tables.php');

        $this->assertFileExists($migrationPath, 'Expected bank migration file to exist for the test.');

        Artisan::call('migrate', [
            '--path' => $migrationPath,
            '--database' => $connection,
            '--realpath' => true,
            '--force' => true,
        ]);

        $this->assertTrue(Schema::hasTable('banks'));
        $this->assertTrue(Schema::hasTable('loan_products'));
        $this->assertTrue(Schema::hasTable('bank_loan_products'));

        (new BanksModuleSeeder())->run();

        $this->assertDatabaseCount('banks', 10);
        $this->assertDatabaseCount('loan_products', 3);
        $this->assertDatabaseCount('bank_loan_products', 29);

        $this->assertTrue(DB::table('loan_products')->where('name', 'اعتبار ملی')->exists());
        $this->assertTrue(DB::table('bank_loan_products')
            ->where('name', 'اعتبار ملی')
            ->where('duration_months', 12)
            ->where('interest_rate', 14.0)
            ->exists());
    }
}
