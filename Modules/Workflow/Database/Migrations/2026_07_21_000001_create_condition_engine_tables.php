<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('condition_definitions')) {
            Schema::create('condition_definitions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('name');
                $table->string('key')->unique();
                $table->text('description')->nullable();
                $table->unsignedInteger('version')->default(1);
                $table->boolean('is_active')->default(true);
                $table->json('configuration')->nullable();
                $table->json('metadata')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['is_active', 'version']);
            });
        }

        if (! Schema::hasTable('condition_groups')) {
            Schema::create('condition_groups', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('condition_definition_id')->constrained('condition_definitions')->cascadeOnDelete();
                $table->foreignId('parent_group_id')->nullable()->constrained('condition_groups')->nullOnDelete();
                $table->string('logical_operator', 20)->default('AND');
                $table->unsignedInteger('sort_order')->default(1);
                $table->text('description')->nullable();
                $table->json('metadata')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['condition_definition_id', 'parent_group_id']);
            });
        }

        if (! Schema::hasTable('condition_rules')) {
            Schema::create('condition_rules', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('condition_definition_id')->constrained('condition_definitions')->cascadeOnDelete();
                $table->foreignId('condition_group_id')->constrained('condition_groups')->cascadeOnDelete();
                $table->string('field_path');
                $table->string('provider', 50)->default('context');
                $table->string('operator', 50);
                $table->json('expected_value')->nullable();
                $table->unsignedInteger('sort_order')->default(1);
                $table->text('description')->nullable();
                $table->json('metadata')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['condition_group_id', 'sort_order']);
            });
        }

        if (! Schema::hasTable('condition_evaluations')) {
            Schema::create('condition_evaluations', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('condition_definition_id')->constrained('condition_definitions')->cascadeOnDelete();
                $table->string('subject_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->boolean('passed')->default(false);
                $table->string('status', 30)->default('failed');
                $table->text('explanation')->nullable();
                $table->json('metadata')->nullable();
                $table->json('result_payload')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['condition_definition_id', 'status']);
                $table->index(['subject_type', 'subject_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('condition_evaluations');
        Schema::dropIfExists('condition_rules');
        Schema::dropIfExists('condition_groups');
        Schema::dropIfExists('condition_definitions');
    }
};
