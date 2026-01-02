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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->string('estimated_completion_time');
            $table->foreignId('quest_id')->constrained()->onDelete('cascade');
            $table->foreignId('adventurer_id')->constrained('users');
            $table->string('status')->default('pending'); // pending, accepted, rejected
            $table->timestamps();
            
            // Ensure one proposal per adventurer per quest
            $table->unique(['quest_id', 'adventurer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
