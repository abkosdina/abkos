<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('advertisements')) {
            Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('advertisement_number')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description');
            $table->string('status')->default('Draft');
            $table->string('visibility')->default('Public');
            $table->integer('priority')->default(0);
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            });
        }

        if (! Schema::hasTable('loan_offers')) {
            Schema::create('loan_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')->constrained('advertisements')->cascadeOnDelete();
            $table->unsignedBigInteger('bank_id');
            $table->unsignedBigInteger('loan_plan_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('loan_type_id')->nullable();
            $table->decimal('loan_amount', 15, 2)->nullable();
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->decimal('interest_rate', 8, 2)->nullable();
            $table->integer('installment_count')->nullable();
            $table->decimal('monthly_installment', 15, 2)->nullable();
            $table->decimal('total_repayment', 15, 2)->nullable();
            $table->integer('remaining_installments')->nullable();
            $table->boolean('guarantor_required')->default(false);
            $table->integer('guarantor_count')->default(0);
            $table->boolean('check_required')->default(false);
            $table->boolean('promissory_note_required')->default(false);
            $table->boolean('collateral_required')->default(false);
            $table->decimal('transfer_fee', 15, 2)->default(0);
            $table->decimal('additional_cost', 15, 2)->default(0);
            $table->boolean('is_negotiable')->default(false);
            $table->boolean('escrow_enabled')->default(false);
            $table->boolean('vip_guarantee')->default(false);
            $table->boolean('contract_ready')->default(false);
            $table->boolean('is_online')->default(true);
            $table->boolean('is_in_person')->default(true);
            $table->timestamps();
            });
        }

        if (! Schema::hasTable('advertisement_images')) {
            Schema::create('advertisement_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')->constrained('advertisements')->cascadeOnDelete();
            $table->unsignedBigInteger('media_id');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_cover')->default(false);
            });
        }

        if (! Schema::hasTable('advertisement_documents')) {
            Schema::create('advertisement_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')->constrained('advertisements')->cascadeOnDelete();
            $table->unsignedBigInteger('document_id');
            $table->string('document_type')->nullable();
            });
        }

        if (! Schema::hasTable('advertisement_logs')) {
            Schema::create('advertisement_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')->constrained('advertisements')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('action');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip')->nullable();
            $table->string('device')->nullable();
            $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_logs');
        Schema::dropIfExists('advertisement_documents');
        Schema::dropIfExists('advertisement_images');
        Schema::dropIfExists('loan_offers');
        Schema::dropIfExists('advertisements');
    }
};
