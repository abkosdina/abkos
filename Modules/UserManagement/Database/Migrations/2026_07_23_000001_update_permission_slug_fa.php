<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasColumn('permissions', 'display_name')) {
            return;
        }

        $permissions = DB::table('permissions')->get(['id', 'display_name']);

        foreach ($permissions as $permission) {
            if (! is_string($permission->display_name) || trim($permission->display_name) === '') {
                continue;
            }

            $slugFa = trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', $permission->display_name), '-');
            if ($slugFa === '') {
                continue;
            }

            DB::table('permissions')
                ->where('id', $permission->id)
                ->update(['slug_fa' => $slugFa]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasColumn('permissions', 'slug_fa')) {
            return;
        }

        DB::table('permissions')->update(['slug_fa' => null]);
    }
};
