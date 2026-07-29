<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('advertisement_views') || ! Schema::hasColumn('advertisement_views', 'advertisement_id')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $existingFk = DB::selectOne(
                "SELECT CONSTRAINT_NAME
                 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?
                   AND COLUMN_NAME = ?
                   AND REFERENCED_TABLE_NAME = 'advertisements'",
                ['advertisement_views', 'advertisement_id']
            );

            if ($existingFk?->CONSTRAINT_NAME) {
                DB::statement('ALTER TABLE advertisement_views DROP FOREIGN KEY ' . $existingFk->CONSTRAINT_NAME);
            }

            DB::statement('ALTER TABLE advertisement_views ADD CONSTRAINT advertisement_views_advertisement_id_foreign FOREIGN KEY (advertisement_id) REFERENCES advertisements (id) ON DELETE SET NULL');

            return;
        }

        if ($driver === 'sqlite') {
            try {
                Schema::table('advertisement_views', function (Blueprint $table) {
                    $table->dropForeign('advertisement_views_advertisement_id_foreign');
                });
            } catch (Throwable $e) {
                // Ignore if the foreign key does not exist or the driver does not support named drops.
            }

            Schema::table('advertisement_views', function (Blueprint $table) {
                $table->foreign('advertisement_id')
                    ->references('id')
                    ->on('advertisements')
                    ->nullOnDelete();
            });

            return;
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('advertisement_views') || ! Schema::hasColumn('advertisement_views', 'advertisement_id')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $existingFk = DB::selectOne(
                "SELECT CONSTRAINT_NAME
                 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?
                   AND COLUMN_NAME = ?
                   AND REFERENCED_TABLE_NAME = 'advertisements'",
                ['advertisement_views', 'advertisement_id']
            );

            if ($existingFk?->CONSTRAINT_NAME) {
                DB::statement('ALTER TABLE advertisement_views DROP FOREIGN KEY ' . $existingFk->CONSTRAINT_NAME);
            }

            return;
        }

        if ($driver === 'sqlite') {
            try {
                Schema::table('advertisement_views', function (Blueprint $table) {
                    $table->dropForeign('advertisement_views_advertisement_id_foreign');
                });
            } catch (Throwable $e) {
                // Ignore if the foreign key is absent.
            }
        }
    }
};
