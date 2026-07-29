<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('audit_logs') && ! Schema::hasColumn('audit_logs', 'action_label')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->string('action_label')->nullable()->after('action');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('audit_logs') && Schema::hasColumn('audit_logs', 'action_label')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropColumn('action_label');
            });
        }
    }
};
