<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('negotiations') && ! Schema::hasColumn('negotiations', 'agreed_price')) {
            Schema::table('negotiations', function (Blueprint $table) {
                $table->decimal('agreed_price', 15, 2)->nullable()->after('selected_offer_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('negotiations') && Schema::hasColumn('negotiations', 'agreed_price')) {
            Schema::table('negotiations', function (Blueprint $table) {
                $table->dropColumn('agreed_price');
            });
        }
    }
};
