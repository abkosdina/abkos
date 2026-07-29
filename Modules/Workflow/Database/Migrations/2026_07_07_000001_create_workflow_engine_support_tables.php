<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workflow_definitions')) {
            Schema::table('workflow_definitions', function (Blueprint $table) {
                if (! Schema::hasColumn('workflow_definitions', 'code')) {
                    $table->string('code')->unique()->nullable()->after('slug');
                }
                if (! Schema::hasColumn('workflow_definitions', 'module')) {
                    $table->string('module')->nullable()->after('description');
                }
                if (! Schema::hasColumn('workflow_definitions', 'status')) {
                    $table->string('status', 30)->default('active')->after('is_active');
                }
            });
        }

        if (Schema::hasTable('workflow_steps')) {
            Schema::table('workflow_steps', function (Blueprint $table) {
                if (! Schema::hasColumn('workflow_steps', 'role_id')) {
                    $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
                }
                if (! Schema::hasColumn('workflow_steps', 'permission_id')) {
                    $table->foreignId('permission_id')->nullable()->constrained('permissions')->nullOnDelete();
                }
                if (! Schema::hasColumn('workflow_steps', 'approval_required')) {
                    $table->boolean('approval_required')->default(true)->after('approval_type');
                }
                if (! Schema::hasColumn('workflow_steps', 'rejection_allowed')) {
                    $table->boolean('rejection_allowed')->default(true)->after('approval_required');
                }
                if (! Schema::hasColumn('workflow_steps', 'comment_required')) {
                    $table->boolean('comment_required')->default(false)->after('rejection_allowed');
                }
                if (! Schema::hasColumn('workflow_steps', 'attachment_required')) {
                    $table->boolean('attachment_required')->default(false)->after('comment_required');
                }
                if (! Schema::hasColumn('workflow_steps', 'automatic_action')) {
                    $table->string('automatic_action')->nullable()->after('timeout_hours');
                }
            });
        }

        if (! Schema::hasTable('workflow_versions')) {
            Schema::create('workflow_versions', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->unsignedBigInteger('workflow_definition_id')->nullable();
            $table->string('version', 50);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unique(['workflow_definition_id', 'version']);
            });
        }

        if (! Schema::hasTable('workflow_transitions')) {
            Schema::create('workflow_transitions', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->unsignedBigInteger('workflow_definition_id')->nullable();
            $table->unsignedBigInteger('from_step_id')->nullable();
            $table->unsignedBigInteger('to_step_id')->nullable();
            $table->string('action', 50);
            $table->unsignedBigInteger('condition_id')->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(1);
            $table->string('name')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unique(['workflow_definition_id', 'from_step_id', 'to_step_id', 'action'], 'uniq_workflow_transitions');
            });
        }

        if (! Schema::hasTable('workflow_instance_steps')) {
            Schema::create('workflow_instance_steps', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->unsignedBigInteger('workflow_instance_id')->nullable();
            $table->unsignedBigInteger('step_id')->nullable();
            $table->string('assigned_to_type')->nullable();
            $table->unsignedBigInteger('assigned_to_id')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unique(['workflow_instance_id', 'step_id'], 'uniq_workflow_instance_steps');
            });
        }

        if (! Schema::hasTable('workflow_actions')) {
            Schema::create('workflow_actions', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->unsignedBigInteger('workflow_definition_id')->nullable();
            $table->unsignedBigInteger('step_id')->nullable();
            $table->string('event_name', 100);
            $table->string('action_type', 50);
            $table->json('payload')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('workflow_conditions')) {
            Schema::create('workflow_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->unsignedBigInteger('workflow_definition_id')->nullable();
            $table->unsignedBigInteger('workflow_step_id')->nullable();
            $table->string('field');
            $table->string('operator', 20);
            $table->string('value')->nullable();
            $table->string('condition_type', 50)->default('attribute');
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('workflow_assignments')) {
            Schema::create('workflow_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->unsignedBigInteger('workflow_step_id')->nullable();
            $table->string('assignable_type');
            $table->unsignedBigInteger('assignable_id');
            $table->string('assignment_type', 50);
            $table->unsignedInteger('priority')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unique(['workflow_step_id', 'assignable_type', 'assignable_id', 'assignment_type'], 'uniq_workflow_assignments');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_assignments');
        Schema::dropIfExists('workflow_conditions');
        Schema::dropIfExists('workflow_actions');
        Schema::dropIfExists('workflow_instance_steps');
        Schema::dropIfExists('workflow_transitions');
        Schema::dropIfExists('workflow_versions');
    }
};
