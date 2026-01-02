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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('withdrawal_id')->unique(); // Unique withdrawal ID
            $table->integer('gold_amount'); // Amount of gold to withdraw
            $table->decimal('cash_amount', 15, 2); // Cash amount in IDR
            $table->string('bank_name'); // Bank name
            $table->string('account_number'); // Bank account number
            $table->string('account_holder_name'); // Account holder name
            $table->string('status')->default('pending'); // pending, processing, completed, rejected
            $table->text('rejection_reason')->nullable(); // Reason if rejected
            $table->timestamp('processed_at')->nullable(); // When withdrawal was processed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
