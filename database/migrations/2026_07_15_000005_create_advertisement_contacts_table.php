<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('advertisement_contacts')) {
            Schema::create('advertisement_contacts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('advertisement_id')->constrained('advertisements')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('The user making contact (nullable for guest inquiries)');
                $table->string('name')->comment('Contact person name');
                $table->string('email')->comment('Contact email');
                $table->string('phone')->nullable()->comment('Contact phone number');
                $table->text('message')->comment('Inquiry message');
                $table->string('status')->default('pending')->index()->comment('Status: pending, responded, closed'); // pending, responded, closed
                $table->string('ip')->nullable()->comment('IP address of the person making contact');
                $table->string('session_id')->nullable()->comment('Session ID');
                $table->string('device')->nullable()->comment('Device information (User-Agent)');
                $table->timestamp('responded_at')->nullable()->comment('When the seller responded to this contact');
                $table->timestamps();
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_contacts');
    }
};
