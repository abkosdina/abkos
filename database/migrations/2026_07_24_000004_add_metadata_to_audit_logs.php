<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('audit_logs') && ! Schema::hasColumn('audit_logs', 'metadata')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->json('metadata')->nullable()->after('user_agent');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('audit_logs') && Schema::hasColumn('audit_logs', 'metadata')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });
        }
    }
};
