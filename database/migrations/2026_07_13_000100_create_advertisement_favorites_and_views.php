<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('advertisement_favorites')) {
            Schema::create('advertisement_favorites', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users');
                $table->string('advertisement_uuid')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('advertisement_views')) {
            Schema::create('advertisement_views', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users');
                $table->unsignedBigInteger('advertisement_id')->nullable()->index();
                $table->string('ip')->nullable();
                $table->string('device')->nullable();
                $table->string('session_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_views');
        Schema::dropIfExists('advertisement_favorites');
    }
};
