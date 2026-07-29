<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('roles') && ! Schema::hasColumn('roles', 'slug_fa')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('slug_fa')->nullable()->after('display_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'slug_fa')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('slug_fa');
            });
        }
    }
};
