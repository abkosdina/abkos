<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('approval_definitions')) {
            Schema::create('approval_definitions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('workflow_definition_id')
                    ->constrained('workflow_definitions')
                    ->cascadeOnDelete();
                $table->string('name');
                $table->string('key');
                $table->text('description')->nullable();
                $table->string('approval_mode')->default('any');
                $table->unsignedInteger('required_approval_count')->default(1);
                $table->boolean('is_active')->default(true);
                $table->json('configuration')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['workflow_definition_id', 'key']);
                $table->index(['workflow_definition_id', 'is_active']);
                $table->index(['approval_mode']);
            });
        }

        if (! Schema::hasTable('approval_steps')) {
            Schema::create('approval_steps', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('approval_definition_id')
                    ->constrained('approval_definitions')
                    ->cascadeOnDelete();
                $table->string('name');
                $table->string('key');
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(1);
                $table->string('required_role')->nullable();
                $table->string('required_permission')->nullable();
                $table->foreignId('required_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedInteger('required_approval_count')->default(1);
                $table->boolean('is_mandatory')->default(true);
                $table->boolean('can_reject')->default(true);
                $table->boolean('can_return_for_correction')->default(false);
                $table->boolean('can_delegate')->default(false);
                $table->unsignedInteger('expires_after_minutes')->nullable();
                $table->json('configuration')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['approval_definition_id', 'key']);
                $table->index(['approval_definition_id', 'sort_order']);
                $table->index(['required_permission']);
                $table->index(['required_user_id']);
            });
        }

        if (! Schema::hasTable('approval_instances')) {
            Schema::create('approval_instances', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('workflow_instance_id')
                    ->constrained('workflow_instances')
                    ->cascadeOnDelete();
                $table->foreignId('approval_definition_id')
                    ->constrained('approval_definitions')
                    ->cascadeOnDelete();
                $table->string('status')->default('pending');
                $table->unsignedInteger('required_approval_count')->default(1);
                $table->unsignedInteger('received_approval_count')->default(0);
                $table->unsignedInteger('version')->default(1);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamp('returned_at')->nullable();
                $table->timestamp('expired_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['workflow_instance_id']);
                $table->index(['approval_definition_id']);
                $table->index(['status']);
                $table->index(['created_at']);
            });
        }

        if (! Schema::hasTable('approval_instance_steps')) {
            Schema::create('approval_instance_steps', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('approval_instance_id')
                    ->constrained('approval_instances')
                    ->cascadeOnDelete();
                $table->foreignId('approval_step_id')
                    ->constrained('approval_steps')
                    ->cascadeOnDelete();
                $table->string('status')->default('pending');
                $table->unsignedInteger('required_approval_count')->default(1);
                $table->unsignedInteger('received_approval_count')->default(0);
                $table->unsignedInteger('version')->default(1);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamp('returned_at')->nullable();
                $table->timestamp('expired_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['approval_instance_id']);
                $table->index(['approval_step_id']);
                $table->index(['status']);
            });
        }

        if (! Schema::hasTable('approval_decisions')) {
            Schema::create('approval_decisions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('approval_instance_id')
                    ->constrained('approval_instances')
                    ->cascadeOnDelete();
                $table->foreignId('approval_instance_step_id')
                    ->constrained('approval_instance_steps')
                    ->cascadeOnDelete();
                $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('approver_role')->nullable();
                $table->string('decision');
                $table->text('comment')->nullable();
                $table->text('reason')->nullable();
                $table->foreignId('delegated_from')->nullable()->constrained('users')->nullOnDelete();
                $table->string('idempotency_key')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('decided_at')->nullable();
                $table->timestamps();

                $table->unique(['idempotency_key']);
                $table->index(['approval_instance_id']);
                $table->index(['approval_instance_step_id']);
                $table->index(['approver_id']);
                $table->index(['decided_at']);
            });
        }

        if (! Schema::hasTable('approval_delegations')) {
            Schema::create('approval_delegations', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('approval_instance_id')
                    ->constrained('approval_instances')
                    ->cascadeOnDelete();
                $table->foreignId('approval_instance_step_id')
                    ->constrained('approval_instance_steps')
                    ->cascadeOnDelete();
                $table->foreignId('delegated_from')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('delegated_to')->nullable()->constrained('users')->nullOnDelete();
                $table->text('reason')->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->string('status')->default('pending');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['approval_instance_id']);
                $table->index(['approval_instance_step_id']);
                $table->index(['delegated_to']);
                $table->index(['status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_delegations');
        Schema::dropIfExists('approval_decisions');
        Schema::dropIfExists('approval_instance_steps');
        Schema::dropIfExists('approval_instances');
        Schema::dropIfExists('approval_steps');
        Schema::dropIfExists('approval_definitions');
    }
};
