<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('advertisement_documents')) {
            Schema::create('advertisement_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('advertisement_id')->index();
            $table->unsignedBigInteger('document_id')->nullable()->index();
            $table->string('document_type')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_documents');
    }
};
