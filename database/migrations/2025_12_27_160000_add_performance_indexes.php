<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes for quests table
        DB::statement('CREATE INDEX IF NOT EXISTS quests_status_created_at_index ON quests (status, created_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS quests_patron_id_status_index ON quests (patron_id, status)');
        DB::statement('CREATE INDEX IF NOT EXISTS quests_adventurer_id_status_index ON quests (adventurer_id, status)');

        // Add indexes for users table
        DB::statement('CREATE INDEX IF NOT EXISTS users_role_index ON users (role)');
        DB::statement('CREATE INDEX IF NOT EXISTS users_gold_index ON users (gold)');

        // Add indexes for orders table
        DB::statement('CREATE INDEX IF NOT EXISTS orders_user_id_status_index ON orders (user_id, status)');
        DB::statement('CREATE INDEX IF NOT EXISTS orders_status_index ON orders (status)');
        DB::statement('CREATE INDEX IF NOT EXISTS orders_created_at_index ON orders (created_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quests', function (Blueprint $table) {
            $table->dropIndex('quests_status_created_at_index');
            $table->dropIndex('quests_patron_id_status_index');
            $table->dropIndex('quests_adventurer_id_status_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_index');
            $table->dropIndex('users_gold_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_id_status_index');
            $table->dropIndex('orders_status_index');
            $table->dropIndex('orders_created_at_index');
        });
    }
};
