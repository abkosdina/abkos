<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('negotiations')) {
            Schema::create('negotiations', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('advertisement_id')->constrained('advertisements')->cascadeOnDelete();
                $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('conversation_id')->nullable();
                $table->string('status')->default('Pending');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamp('expired_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->unsignedBigInteger('selected_offer_id')->nullable();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->timestamps();
                $table->index('status');
                $table->index(['advertisement_id', 'status']);
                $table->index(['buyer_id', 'status']);
                $table->index(['seller_id', 'status']);
            });
        }

        if (! Schema::hasTable('negotiation_offers')) {
            Schema::create('negotiation_offers', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('negotiation_id')->constrained('negotiations')->cascadeOnDelete();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('parent_offer_id')->nullable();
                $table->decimal('amount', 15, 2);
                $table->text('description')->nullable();
                $table->string('status')->default('Pending');
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamps();
                $table->index('status');
                $table->index(['negotiation_id', 'status']);
                $table->index(['created_by', 'status']);
            });
        }

        if (! Schema::hasTable('negotiation_histories')) {
            Schema::create('negotiation_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('negotiation_id')->constrained('negotiations')->cascadeOnDelete();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('event');
                $table->json('details')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['negotiation_id', 'event']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('negotiation_histories');
        Schema::dropIfExists('negotiation_offers');
        Schema::dropIfExists('negotiations');
    }
};
