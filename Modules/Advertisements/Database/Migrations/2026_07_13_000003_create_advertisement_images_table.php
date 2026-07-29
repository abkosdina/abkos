<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('advertisement_images')) {
            Schema::create('advertisement_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('advertisement_id')->index();
            $table->unsignedBigInteger('media_id')->nullable()->index();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_cover')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_images');
    }
};
