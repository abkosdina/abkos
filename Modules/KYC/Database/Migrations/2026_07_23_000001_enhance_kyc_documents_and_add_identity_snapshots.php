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
        // Add missing columns to kyc_documents for secure document handling
        if (Schema::hasTable('kyc_documents')) {
            Schema::table('kyc_documents', function (Blueprint $table) {
                // Add original_filename if not exists
                if (!Schema::hasColumn('kyc_documents', 'original_filename')) {
                    $table->string('original_filename')->nullable()->after('document_type');
                }

                // Add file_size if not exists
                if (!Schema::hasColumn('kyc_documents', 'file_size')) {
                    $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
                }

                // Add file_hash if not exists
                if (!Schema::hasColumn('kyc_documents', 'file_hash')) {
                    $table->string('file_hash', 64)->nullable()->after('file_size');
                }

                // Add document_status if not exists (or rename status)
                if (!Schema::hasColumn('kyc_documents', 'document_status')) {
                    // If 'status' exists, copy to 'document_status', then drop 'status'
                    if (Schema::hasColumn('kyc_documents', 'status')) {
                        $table->string('document_status', 50)->nullable()->after('file_hash');
                    } else {
                        $table->string('document_status', 50)->default('uploaded')->after('file_hash');
                    }
                }

                // Add reviewed_by if not exists
                if (!Schema::hasColumn('kyc_documents', 'reviewed_by')) {
                    $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
                }

                // Add rejection_reason if not exists
                if (!Schema::hasColumn('kyc_documents', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('reviewed_by');
                }

                // Add metadata if not exists
                if (!Schema::hasColumn('kyc_documents', 'metadata')) {
                    $table->json('metadata')->nullable()->after('rejection_reason');
                }

                // Add indexes
                if (!Schema::hasIndex('kyc_documents', 'idx_kyc_documents_type')) {
                    $table->index(['document_type'], 'idx_kyc_documents_type');
                }

                if (!Schema::hasIndex('kyc_documents', 'idx_kyc_documents_status')) {
                    $table->index(['document_status'], 'idx_kyc_documents_status');
                }

                if (!Schema::hasIndex('kyc_documents', 'idx_kyc_documents_hash')) {
                    $table->index(['file_hash'], 'idx_kyc_documents_hash');
                }
            });

            // Copy status to document_status if status exists and document_status is empty
            if (Schema::hasColumn('kyc_documents', 'status') && Schema::hasColumn('kyc_documents', 'document_status')) {
                \Illuminate\Support\Facades\DB::table('kyc_documents')
                    ->whereNull('document_status')
                    ->update(['document_status' => \Illuminate\Support\Facades\DB::raw('`status`')]);
            }
        }

        // Create kyc_identity_snapshots to preserve submitted data
        if (!Schema::hasTable('kyc_identity_snapshots')) {
            Schema::create('kyc_identity_snapshots', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('kyc_request_id')->constrained('kyc_requests')->cascadeOnDelete();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('father_name')->nullable();
                $table->string('national_code', 20)->nullable();
                $table->date('birth_date')->nullable();
                $table->string('birth_place')->nullable();
                $table->string('mobile_number', 20)->nullable();
                $table->string('postal_code', 20)->nullable();
                $table->text('address')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['kyc_request_id'], 'idx_identity_snapshots_request');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kyc_documents')) {
            Schema::table('kyc_documents', function (Blueprint $table) {
                // Drop foreign key if exists
                try {
                    $table->dropForeign(['reviewed_by']);
                } catch (\Exception $e) {
                    // Foreign key may not exist
                }

                // Drop columns
                $columns = ['original_filename', 'file_size', 'file_hash', 'document_status', 'rejection_reason', 'metadata'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('kyc_documents', $column)) {
                        $table->dropColumn($column);
                    }
                }

                // Drop indexes
                try {
                    $table->dropIndex('idx_kyc_documents_type');
                } catch (\Exception $e) {
                    // Index may not exist
                }

                try {
                    $table->dropIndex('idx_kyc_documents_status');
                } catch (\Exception $e) {
                    // Index may not exist
                }

                try {
                    $table->dropIndex('idx_kyc_documents_hash');
                } catch (\Exception $e) {
                    // Index may not exist
                }
            });
        }

        Schema::dropIfExists('kyc_identity_snapshots');
    }
};
