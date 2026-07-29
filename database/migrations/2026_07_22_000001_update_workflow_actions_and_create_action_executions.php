<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workflow_actions')) {
            Schema::table('workflow_actions', function (Blueprint $table) {
                if (! Schema::hasColumn('workflow_actions', 'uuid')) {
                    $table->string('uuid', 36)->nullable()->unique();
                }
                if (! Schema::hasColumn('workflow_actions', 'workflow_transition_id')) {
                    $table->unsignedBigInteger('workflow_transition_id')->nullable();
                }
                if (! Schema::hasColumn('workflow_actions', 'handler')) {
                    $table->string('handler', 100)->nullable();
                }
                if (! Schema::hasColumn('workflow_actions', 'key')) {
                    $table->string('key', 100)->nullable();
                }
                if (! Schema::hasColumn('workflow_actions', 'name')) {
                    $table->string('name')->nullable();
                }
                if (! Schema::hasColumn('workflow_actions', 'description')) {
                    $table->text('description')->nullable();
                }
                if (! Schema::hasColumn('workflow_actions', 'configuration')) {
                    $table->json('configuration')->nullable();
                }
                if (! Schema::hasColumn('workflow_actions', 'version')) {
                    $table->unsignedInteger('version')->default(1);
                }
                if (! Schema::hasColumn('workflow_actions', 'blocking')) {
                    $table->boolean('blocking')->default(true);
                }
                if (! Schema::hasColumn('workflow_actions', 'priority')) {
                    $table->unsignedInteger('priority')->default(100);
                }
                if (! Schema::hasColumn('workflow_actions', 'execution_order')) {
                    $table->unsignedInteger('execution_order')->default(1);
                }
                if (! Schema::hasColumn('workflow_actions', 'failure_policy')) {
                    $table->string('failure_policy', 50)->default('stop');
                }
                if (! Schema::hasColumn('workflow_actions', 'max_attempts')) {
                    $table->unsignedInteger('max_attempts')->default(3);
                }
                if (! Schema::hasColumn('workflow_actions', 'backoff_seconds')) {
                    $table->unsignedInteger('backoff_seconds')->default(60);
                }
                if (! Schema::hasColumn('workflow_actions', 'metadata')) {
                    $table->json('metadata')->nullable();
                }
            });
        }

        if (! Schema::hasTable('workflow_action_executions')) {
            Schema::create('workflow_action_executions', function (Blueprint $table) {
                $table->id();
                $table->string('uuid', 36)->unique();
                $table->unsignedBigInteger('workflow_action_id')->nullable();
                $table->unsignedBigInteger('workflow_definition_id')->nullable();
                $table->unsignedBigInteger('workflow_transition_id')->nullable();
                $table->unsignedBigInteger('workflow_instance_id')->nullable();
                $table->unsignedBigInteger('approval_instance_id')->nullable();
                $table->unsignedBigInteger('actor_id')->nullable();
                $table->string('business_entity_type')->nullable();
                $table->unsignedBigInteger('business_entity_id')->nullable();
                $table->string('action_key')->nullable();
                $table->unsignedInteger('action_version')->default(1);
                $table->string('handler')->nullable();
                $table->string('status', 30)->default('pending');
                $table->string('idempotency_key')->nullable()->unique();
                $table->unsignedInteger('attempts')->default(0);
                $table->unsignedInteger('max_attempts')->default(3);
                $table->unsignedInteger('retry_count')->default(0);
                $table->unsignedInteger('backoff_seconds')->default(60);
                $table->timestamp('next_retry_at')->nullable();
                $table->string('error_code')->nullable();
                $table->text('error_message')->nullable();
                $table->json('result')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->timestamps();
                $table->index(['workflow_action_id']);
                $table->index(['workflow_instance_id']);
                $table->index(['status']);
                $table->index(['next_retry_at']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workflow_action_executions')) {
            Schema::dropIfExists('workflow_action_executions');
        }

        if (Schema::hasTable('workflow_actions')) {
            Schema::table('workflow_actions', function (Blueprint $table) {
                foreach ([
                    'uuid',
                    'workflow_transition_id',
                    'handler',
                    'key',
                    'name',
                    'description',
                    'configuration',
                    'version',
                    'blocking',
                    'priority',
                    'execution_order',
                    'failure_policy',
                    'max_attempts',
                    'backoff_seconds',
                    'metadata',
                ] as $column) {
                    if (Schema::hasColumn('workflow_actions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};