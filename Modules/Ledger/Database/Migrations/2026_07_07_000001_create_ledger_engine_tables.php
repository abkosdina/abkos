<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_types', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('code', 100)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('code', 100)->unique();
            $table->string('name', 150);
            $table->foreignId('account_type_id')->constrained('account_types')->cascadeOnDelete();
            $table->string('owner_type')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('currency', 10)->default('USD');
            $table->string('status', 50)->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ledger_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('type', 50)->nullable();
            $table->string('reference_number', 100)->nullable()->unique();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('transaction_type', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 50)->default('pending');
            $table->decimal('total_debit', 24, 8)->default(0);
            $table->decimal('total_credit', 24, 8)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('ledger_transaction_id')->constrained('ledger_transactions')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->decimal('debit', 24, 8)->default(0);
            $table->decimal('credit', 24, 8)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->decimal('running_balance', 24, 8)->nullable();
            $table->string('description')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('ledger_transaction_id')->constrained('ledger_transactions')->cascadeOnDelete();
            $table->string('entry_type', 50)->default('journal');
            $table->text('description')->nullable();
            $table->string('status', 50)->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('financial_periods', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('code', 100)->unique();
            $table->string('name', 150);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 50)->default('open');
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('balance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('period_id')->nullable()->constrained('financial_periods')->nullOnDelete();
            $table->decimal('available_balance', 24, 8)->default(0);
            $table->decimal('blocked_balance', 24, 8)->default(0);
            $table->decimal('pending_balance', 24, 8)->default(0);
            $table->decimal('total_balance', 24, 8)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->date('snapshot_date');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('account_balances', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->decimal('available_balance', 24, 8)->default(0);
            $table->decimal('blocked_balance', 24, 8)->default(0);
            $table->decimal('pending_balance', 24, 8)->default(0);
            $table->decimal('total_balance', 24, 8)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_balances');
        Schema::dropIfExists('balance_snapshots');
        Schema::dropIfExists('financial_periods');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('ledger_transactions');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('account_types');
    }
};
