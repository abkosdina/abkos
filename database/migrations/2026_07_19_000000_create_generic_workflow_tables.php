<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create generic workflow infrastructure tables that support
     * any business entity type (Advertisement, KYC, Order, etc.)
     */
    public function up(): void
    {
        if (!Schema::hasTable('workflow_definitions')) {
            Schema::create('workflow_definitions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->nullable()->unique();
                $table->string('name');
                $table->string('key')->index();
                $table->text('description')->nullable();
                $table->string('entity_type')->index();
                $table->integer('version')->default(1);
                $table->boolean('is_active')->default(true)->index();
                $table->boolean('is_default')->default(false);
                $table->json('configuration')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                $table->unique(['key', 'version']);
            });
        }

        if (Schema::hasTable('workflow_definitions')) {
            Schema::table('workflow_definitions', function (Blueprint $table) {
                if (! Schema::hasColumn('workflow_definitions', 'slug')) {
                    $table->string('slug')->nullable()->index();
                }
                if (! Schema::hasColumn('workflow_definitions', 'key')) {
                    $table->string('key')->nullable()->index();
                }
                if (! Schema::hasColumn('workflow_definitions', 'is_default')) {
                    $table->boolean('is_default')->default(false);
                }
                if (! Schema::hasColumn('workflow_definitions', 'configuration')) {
                    $table->json('configuration')->nullable();
                }
                if (! Schema::hasColumn('workflow_definitions', 'created_by')) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                }
                if (! Schema::hasColumn('workflow_definitions', 'updated_by')) {
                    $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                }
            });
        }

        if (!Schema::hasTable('workflow_states')) {
            Schema::create('workflow_states', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->nullable()->unique();
                $table->foreignId('workflow_definition_id')
                    ->constrained('workflow_definitions')
                    ->onDelete('cascade');
                $table->string('name');
                $table->string('key')->index();
                $table->text('description')->nullable();
                $table->boolean('is_initial')->default(false)->index();
                $table->boolean('is_final')->default(false)->index();
                $table->boolean('is_rejection')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['workflow_definition_id', 'key']);
            });
        }

        if (!Schema::hasTable('workflow_transitions')) {
            Schema::create('workflow_transitions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_definition_id')
                    ->constrained('workflow_definitions')
                    ->onDelete('cascade');
                $table->foreignId('from_state_id')
                    ->constrained('workflow_states')
                    ->onDelete('cascade');
                $table->foreignId('to_state_id')
                    ->constrained('workflow_states')
                    ->onDelete('cascade');
                $table->uuid('uuid')->nullable()->unique();
                $table->string('action')->nullable();
                $table->string('name')->nullable();
                $table->string('key')->index();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('required_role')->nullable();
                $table->string('required_permission')->nullable();
                $table->json('configuration')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['workflow_definition_id', 'key']);
                $table->index(['from_state_id', 'to_state_id']);
            });
        }

        if (!Schema::hasTable('workflow_instances')) {
            Schema::create('workflow_instances', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('workflow_definition_id')
                    ->constrained('workflow_definitions')
                    ->onDelete('cascade');
                $table->string('entity_type');
                $table->unsignedBigInteger('entity_id');
                $table->foreignId('current_state_id')
                    ->constrained('workflow_states')
                    ->onDelete('restrict');
                $table->string('status')->default('active')->index();
                $table->integer('version')->default(1);
                $table->timestamp('started_at')->useCurrent();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['entity_type', 'entity_id', 'workflow_definition_id']);
                $table->index(['entity_type', 'entity_id']);
                $table->index(['workflow_definition_id']);
                $table->index(['current_state_id']);
            });
        } else {
            Schema::table('workflow_instances', function (Blueprint $table) {
                if (! Schema::hasColumn('workflow_instances', 'current_state_id')) {
                    $table->foreignId('current_state_id')->nullable()->constrained('workflow_states')->onDelete('restrict');
                }
                if (! Schema::hasColumn('workflow_instances', 'status')) {
                    $table->string('status')->default('active')->index();
                }
                if (! Schema::hasColumn('workflow_instances', 'version')) {
                    $table->integer('version')->default(1);
                }
                if (! Schema::hasColumn('workflow_instances', 'started_at')) {
                    $table->timestamp('started_at')->useCurrent();
                }
                if (! Schema::hasColumn('workflow_instances', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable();
                }
                if (! Schema::hasColumn('workflow_instances', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable();
                }
                if (! Schema::hasColumn('workflow_instances', 'metadata')) {
                    $table->json('metadata')->nullable();
                }
            });
        }

        if (!Schema::hasTable('workflow_instance_steps')) {
            Schema::create('workflow_instance_steps', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->nullable()->unique();
                $table->foreignId('workflow_instance_id')
                    ->constrained('workflow_instances')
                    ->onDelete('cascade');
                $table->foreignId('transition_id')
                    ->constrained('workflow_transitions')
                    ->onDelete('restrict');
                $table->foreignId('from_state_id')
                    ->constrained('workflow_states')
                    ->onDelete('restrict');
                $table->foreignId('to_state_id')
                    ->constrained('workflow_states')
                    ->onDelete('restrict');
                $table->foreignId('executed_by')->nullable()
                    ->constrained('users')
                    ->onDelete('set null');
                $table->string('idempotency_key')->nullable()->unique();
                $table->text('comment')->nullable();
                $table->text('reason')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('executed_at');
                $table->timestamps();
                $table->index(['workflow_instance_id']);
                $table->index(['executed_by']);
                $table->index(['executed_at']);
                $table->unique(['workflow_instance_id', 'idempotency_key']);
            });
        } else {
            Schema::table('workflow_instance_steps', function (Blueprint $table) {
                if (! Schema::hasColumn('workflow_instance_steps', 'transition_id')) {
                    $table->foreignId('transition_id')->nullable()->constrained('workflow_transitions')->onDelete('restrict');
                }
                if (! Schema::hasColumn('workflow_instance_steps', 'from_state_id')) {
                    $table->foreignId('from_state_id')->nullable()->constrained('workflow_states')->onDelete('restrict');
                }
                if (! Schema::hasColumn('workflow_instance_steps', 'to_state_id')) {
                    $table->foreignId('to_state_id')->nullable()->constrained('workflow_states')->onDelete('restrict');
                }
                if (! Schema::hasColumn('workflow_instance_steps', 'executed_by')) {
                    $table->foreignId('executed_by')->nullable()->constrained('users')->onDelete('set null');
                }
                if (! Schema::hasColumn('workflow_instance_steps', 'idempotency_key')) {
                    $table->string('idempotency_key')->nullable()->unique();
                }
                if (! Schema::hasColumn('workflow_instance_steps', 'comment')) {
                    $table->text('comment')->nullable();
                }
                if (! Schema::hasColumn('workflow_instance_steps', 'reason')) {
                    $table->text('reason')->nullable();
                }
                if (! Schema::hasColumn('workflow_instance_steps', 'metadata')) {
                    $table->json('metadata')->nullable();
                }
                if (! Schema::hasColumn('workflow_instance_steps', 'executed_at')) {
                    $table->timestamp('executed_at')->nullable();
                }
            });
        }

        if (!Schema::hasTable('workflow_idempotency_keys')) {
            Schema::create('workflow_idempotency_keys', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->foreignId('workflow_instance_id')
                    ->constrained('workflow_instances')
                    ->onDelete('cascade');
                $table->foreignId('transition_id')->nullable()
                    ->constrained('workflow_transitions')
                    ->onDelete('set null');
                $table->string('request_hash')->nullable();
                $table->foreignId('executed_by')->nullable()
                    ->constrained('users')
                    ->onDelete('set null');
                $table->timestamp('executed_at');
                $table->timestamps();
                $table->index(['workflow_instance_id']);
                $table->index(['executed_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_idempotency_keys');
        Schema::dropIfExists('workflow_instance_steps');
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('workflow_transitions');
        Schema::dropIfExists('workflow_states');
        Schema::dropIfExists('workflow_definitions');
    }
};
