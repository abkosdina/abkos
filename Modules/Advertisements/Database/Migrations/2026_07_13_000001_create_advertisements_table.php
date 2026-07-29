<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('advertisements')) {
            Schema::create('advertisements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->string('advertisement_number')->unique();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('seller_user_id')->nullable()->index();
            $table->string('title');
            $table->string('slug')->index();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('status')->index();
            $table->string('visibility')->index()->default('Public');
            $table->integer('priority')->default(0);
            $table->unsignedBigInteger('loan_product_id')->nullable()->index();
            $table->unsignedBigInteger('province_id')->nullable()->index();
            $table->unsignedBigInteger('city_id')->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
