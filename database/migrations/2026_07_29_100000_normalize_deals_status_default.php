<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Change default value to lowercase 'pending' if column exists
        if (Schema::hasTable('deals') && Schema::hasColumn('deals', 'status')) {
            // This approach uses raw SQL to alter default in sqlite and MySQL/Postgres variations
            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                // For sqlite we cannot alter default easily; ignore default change but lowercase existing values
                DB::table('deals')->where('status', 'Pending')->orWhere('status', 'PENDING')->update(['status' => 'pending']);
            } else {
                DB::statement("UPDATE deals SET status = LOWER(status) WHERE status IS NOT NULL");
                // change default
                if ($driver === 'mysql') {
                    DB::statement("ALTER TABLE deals ALTER COLUMN status SET DEFAULT 'pending'");
                } elseif ($driver === 'pgsql') {
                    DB::statement("ALTER TABLE deals ALTER COLUMN status SET DEFAULT 'pending'");
                }
            }
        }
    }

    public function down(): void
    {
        // no-op
    }
};
