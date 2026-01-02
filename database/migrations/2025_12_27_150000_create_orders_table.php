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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique(); // Midtrans order ID
            $table->foreignId('user_id')->constrained('users');
            $table->integer('gold_amount');
            $table->decimal('price', 10, 2); // Real currency price
            $table->string('status')->default('pending'); // pending, paid, failed, cancelled
            $table->string('payment_type')->nullable(); // bank_transfer, e_wallet, credit_card
            $table->json('payment_details')->nullable(); // Midtrans response data
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
