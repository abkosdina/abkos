<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('document_categories', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('document_type_id')->nullable()->constrained('document_types')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('document_categories')->nullOnDelete();
            $table->morphs('owner');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('storage_provider')->default('local');
            $table->string('mime_type', 100)->nullable();
            $table->string('extension', 20)->nullable();
            $table->string('original_name')->nullable();
            $table->string('generated_name')->nullable();
            $table->string('hash', 64)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('visibility', 30)->default('private');
            $table->string('status', 30)->default('uploaded');
            $table->string('current_version', 20)->default('v1');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('version', 20);
            $table->string('storage_provider')->default('local');
            $table->string('mime_type', 100)->nullable();
            $table->string('extension', 20)->nullable();
            $table->string('original_name')->nullable();
            $table->string('generated_name')->nullable();
            $table->string('hash', 64)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('status', 30)->default('uploaded');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('document_tags', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('document_metadata', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['document_id', 'key']);
        });

        Schema::create('document_links', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('link');
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('document_access_logs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device')->nullable();
            $table->timestamps();
        });

        Schema::create('document_download_logs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_download_logs');
        Schema::dropIfExists('document_access_logs');
        Schema::dropIfExists('document_links');
        Schema::dropIfExists('document_metadata');
        Schema::dropIfExists('document_tags');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_categories');
        Schema::dropIfExists('document_types');
    }
};
