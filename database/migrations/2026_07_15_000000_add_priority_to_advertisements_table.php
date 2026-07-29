<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('advertisements', 'priority')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->integer('priority')->default(0)->after('visibility');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('advertisements', 'priority')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->dropColumn('priority');
            });
        }
    }
};
