<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('wallet_type', 50)->default(config('wallet.default_type'));
            $table->foreignId('ledger_account_id')->constrained('accounts')->cascadeOnDelete();
            $table->string('currency', 10)->default('USD');
            $table->string('status', 50)->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('wallet_balances', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->decimal('available_balance', 24, 8)->default(0);
            $table->decimal('locked_balance', 24, 8)->default(0);
            $table->decimal('blocked_balance', 24, 8)->default(0);
            $table->decimal('pending_balance', 24, 8)->default(0);
            $table->decimal('total_balance', 24, 8)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('ledger_transaction_id')->constrained('ledger_transactions')->cascadeOnDelete();
            $table->unsignedBigInteger('financial_transaction_id')->nullable()->index();
            $table->string('transaction_type', 50);
            $table->decimal('amount', 24, 8)->default(0);
            $table->string('status', 50)->default('pending');
            $table->text('description')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('wallet_locks', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->string('reason', 100);
            $table->decimal('amount', 24, 8)->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->string('status', 50)->default('locked');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('wallet_limits', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->decimal('daily_deposit', 24, 8)->default(config('wallet.limits.daily_deposit'));
            $table->decimal('daily_withdrawal', 24, 8)->default(config('wallet.limits.daily_withdrawal'));
            $table->decimal('maximum_balance', 24, 8)->default(config('wallet.limits.maximum_balance'));
            $table->decimal('maximum_transaction', 24, 8)->default(config('wallet.limits.maximum_transaction'));
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('wallet_settings', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->string('key', 100);
            $table->json('value');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_settings');
        Schema::dropIfExists('wallet_limits');
        Schema::dropIfExists('wallet_locks');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallet_balances');
        Schema::dropIfExists('wallets');
    }
};
