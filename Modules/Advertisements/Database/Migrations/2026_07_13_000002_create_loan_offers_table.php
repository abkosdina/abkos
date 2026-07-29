<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('loan_offers')) {
            Schema::create('loan_offers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('advertisement_id')->index();
            $table->unsignedBigInteger('bank_id')->nullable()->index();
            $table->unsignedBigInteger('loan_plan_id')->nullable()->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('loan_type_id')->nullable()->index();

            $table->decimal('loan_amount', 18, 4)->nullable();
            $table->decimal('sale_price', 18, 4)->nullable();
            $table->decimal('interest_rate', 8, 4)->nullable();
            $table->integer('installment_count')->nullable();
            $table->decimal('monthly_installment', 18, 4)->nullable();
            $table->decimal('total_repayment', 18, 4)->nullable();
            $table->integer('remaining_installments')->nullable();

            $table->boolean('guarantor_required')->default(false);
            $table->integer('guarantor_count')->default(0);
            $table->boolean('check_required')->default(false);
            $table->boolean('promissory_note_required')->default(false);
            $table->boolean('collateral_required')->default(false);

            $table->decimal('transfer_fee', 18, 4)->default(0);
            $table->decimal('additional_cost', 18, 4)->default(0);

            $table->boolean('is_negotiable')->default(false);
            $table->boolean('escrow_enabled')->default(false);
            $table->boolean('vip_guarantee')->default(false);
            $table->boolean('contract_ready')->default(false);
            $table->boolean('is_online')->default(true);
            $table->boolean('is_in_person')->default(true);

            $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_offers');
    }
};
