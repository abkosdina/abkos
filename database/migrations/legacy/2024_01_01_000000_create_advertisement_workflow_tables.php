<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create advertisement_logs table
        Schema::create('advertisement_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')->constrained('advertisements')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // submitted, approved, rejected, etc.
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('advertisement_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });

        // Create advertisement_workflow_audits table
        Schema::create('advertisement_workflow_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')->constrained('advertisements')->onDelete('cascade');
            $table->uuid('uuid')->unique();
            $table->string('old_state');
            $table->string('new_state');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('role')->nullable();
            $table->string('action');
            $table->text('reason')->nullable();
            $table->text('comment')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('advertisement_id');
            $table->index('old_state');
            $table->index('new_state');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });

        // Create advertisement_workflow_states table (for state configuration)
        Schema::create('advertisement_workflow_states', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_final')->default(false);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_searchable')->default(false);
            $table->boolean('is_editable')->default(false);
            $table->boolean('is_deletable')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('is_final');
        });

        // Create advertisement_workflow_transitions table (for transition configuration)
        Schema::create('advertisement_workflow_transitions', function (Blueprint $table) {
            $table->id();
            $table->string('from_state');
            $table->string('to_state');
            $table->string('action')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->json('required_roles')->nullable();
            $table->boolean('requires_reason')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['from_state', 'to_state']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisement_workflow_transitions');
        Schema::dropIfExists('advertisement_workflow_states');
        Schema::dropIfExists('advertisement_workflow_audits');
        Schema::dropIfExists('advertisement_logs');
    }
};
