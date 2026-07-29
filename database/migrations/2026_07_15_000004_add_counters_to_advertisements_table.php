<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('advertisements')) {
            Schema::table('advertisements', function (Blueprint $table) {
                // Add counter columns if they don't exist
                if (!Schema::hasColumn('advertisements', 'views_count')) {
                    $table->unsignedBigInteger('views_count')->default(0)->after('expires_at')->comment('Total number of views');
                }

                if (!Schema::hasColumn('advertisements', 'contacts_count')) {
                    $table->unsignedBigInteger('contacts_count')->default(0)->after('views_count')->comment('Total number of contact inquiries');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('advertisements')) {
            Schema::table('advertisements', function (Blueprint $table) {
                if (Schema::hasColumn('advertisements', 'views_count')) {
                    $table->dropColumn('views_count');
                }

                if (Schema::hasColumn('advertisements', 'contacts_count')) {
                    $table->dropColumn('contacts_count');
                }
            });
        }
    }
};
