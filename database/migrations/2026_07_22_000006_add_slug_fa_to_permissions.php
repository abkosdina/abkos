<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('permissions') && ! Schema::hasColumn('permissions', 'slug_fa')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('slug_fa')->nullable()->after('display_name');
            });
        }

        if (Schema::hasTable('permissions') && Schema::hasColumn('permissions', 'display_name')) {
            $permissions = DB::table('permissions')->whereNull('slug_fa')->whereNotNull('display_name')->get(['id', 'display_name']);
            foreach ($permissions as $permission) {
                $slugFa = trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', $permission->display_name), '-');
                if ($slugFa !== '') {
                    DB::table('permissions')->where('id', $permission->id)->update(['slug_fa' => $slugFa]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('permissions') && Schema::hasColumn('permissions', 'slug_fa')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropColumn('slug_fa');
            });
        }
    }
};
