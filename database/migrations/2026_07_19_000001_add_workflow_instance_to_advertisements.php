<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add workflow_instance_id to advertisements table
     * to link each advertisement to its workflow instance
     */
    public function up(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            // Add foreign key to workflow_instances table
            $table->foreignId('workflow_instance_id')
                ->nullable()
                ->constrained('workflow_instances')
                ->onDelete('set null')
                ->after('status');

            // Index for fast lookups
            $table->index('workflow_instance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropForeignKey('advertisements_workflow_instance_id_foreign');
            $table->dropColumn('workflow_instance_id');
            $table->dropIndex('advertisements_workflow_instance_id_index');
        });
    }
};
