<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_requests', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('workflow_instance_id')->nullable();
            $table->string('document_type')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('current_step_id')->nullable();
            $table->string('status', 30)->default('pending');
            $table->unsignedInteger('priority')->default(1);
            $table->timestamp('deadline_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('kyc_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('national_code', 20)->nullable();
            $table->string('father_name', 255)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('occupation', 100)->nullable();
            $table->string('risk_level', 50)->nullable();
            $table->unsignedInteger('kyc_score')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('kyc_review_logs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('kyc_request_id')->constrained('kyc_requests')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50);
            $table->string('role', 100)->nullable();
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->text('comment')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('kyc_rejection_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('kyc_request_id')->constrained('kyc_requests')->cascadeOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 255);
            $table->text('comment')->nullable();
            $table->json('required_corrections')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('kyc_status_history', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('kyc_request_id')->constrained('kyc_requests')->cascadeOnDelete();
            $table->string('old_status', 30);
            $table->string('new_status', 30);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role', 100)->nullable();
            $table->text('comment')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('kyc_fields', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('name', 100);
            $table->string('label', 150);
            $table->string('type', 50)->default('text');
            $table->json('validation_rules')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_sensitive')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('kyc_field_values', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('kyc_profile_id')->constrained('kyc_profiles')->cascadeOnDelete();
            $table->foreignId('kyc_field_id')->constrained('kyc_fields')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['kyc_profile_id', 'kyc_field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_field_values');
        Schema::dropIfExists('kyc_fields');
        Schema::dropIfExists('kyc_status_history');
        Schema::dropIfExists('kyc_rejection_reasons');
        Schema::dropIfExists('kyc_review_logs');
        Schema::dropIfExists('kyc_profiles');
        Schema::dropIfExists('kyc_requests');
    }
};
