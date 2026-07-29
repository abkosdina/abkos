<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('advertisements')) {
            return;
        }

        if (! Schema::hasColumn('advertisements', 'uuid')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id')->unique();
            });
        }

        if (! Schema::hasColumn('advertisements', 'user_id')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
            });
        }

        if (! Schema::hasColumn('advertisements', 'seller_user_id')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->unsignedBigInteger('seller_user_id')->nullable()->after('user_id')->index();
            });
        }

        if (! Schema::hasColumn('advertisements', 'title')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->string('title')->nullable()->after('seller_user_id');
            });
        }

        if (! Schema::hasColumn('advertisements', 'slug')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('title');
            });
        }

        if (! Schema::hasColumn('advertisements', 'price')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->decimal('price', 15, 2)->nullable()->after('slug');
            });
        }

        if (! Schema::hasColumn('advertisements', 'currency')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->string('currency', 10)->nullable()->default('USD')->after('price');
            });
        }

        if (! Schema::hasColumn('advertisements', 'short_description')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->text('short_description')->nullable()->after('currency');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('advertisements')) {
            return;
        }

        if (Schema::hasColumn('advertisements', 'seller_user_id')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->dropColumn('seller_user_id');
            });
        }

        if (Schema::hasColumn('advertisements', 'user_id')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }
};
