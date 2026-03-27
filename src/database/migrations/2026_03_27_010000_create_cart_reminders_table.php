<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_reminders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('items');
            $table->integer('subtotal_cents')->default(0);
            $table->string('discount_code')->nullable();
            $table->timestamp('last_activity_at');
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['last_activity_at', 'reminder_sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_reminders');
    }
};
