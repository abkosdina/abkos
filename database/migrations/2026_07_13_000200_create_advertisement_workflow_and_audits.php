<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('advertisement_workflow_states')) {
            Schema::create('advertisement_workflow_states', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('name');
                $table->text('meta')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('advertisement_workflow_transitions')) {
            Schema::create('advertisement_workflow_transitions', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('from_state');
                $table->string('to_state');
                $table->string('role_required')->nullable();
                $table->text('rules')->nullable();
                $table->text('meta')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('advertisement_workflow_audits')) {
            Schema::create('advertisement_workflow_audits', function (Blueprint $table) {
                $table->id();
                $table->uuid('advertisement_uuid');
                $table->string('old_state')->nullable();
                $table->string('new_state');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_role')->nullable();
                $table->string('action');
                $table->text('reason')->nullable();
                $table->text('comment')->nullable();
                $table->string('ip')->nullable();
                $table->string('device')->nullable();
                $table->json('extra')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_workflow_audits');
        Schema::dropIfExists('advertisement_workflow_transitions');
        Schema::dropIfExists('advertisement_workflow_states');
    }
};
