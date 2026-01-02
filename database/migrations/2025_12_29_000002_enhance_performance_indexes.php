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
        // Add full-text search index for quests (if using MySQL/MariaDB)
        // For SQLite, we'll add regular indexes
        if (config('database.default') === 'sqlite') {
            // SQLite doesn't support full-text search easily, so we add composite indexes
            DB::statement('CREATE INDEX IF NOT EXISTS quests_title_search_index ON quests (title)');
            DB::statement('CREATE INDEX IF NOT EXISTS quests_description_search_index ON quests (description)');
        } else {
            // For MySQL/MariaDB, add full-text indexes
            try {
                DB::statement('CREATE FULLTEXT INDEX quests_title_fulltext ON quests (title)');
                DB::statement('CREATE FULLTEXT INDEX quests_description_fulltext ON quests (description)');
            } catch (\Exception $e) {
                // If full-text not supported, use regular indexes
                DB::statement('CREATE INDEX IF NOT EXISTS quests_title_search_index ON quests (title)');
                DB::statement('CREATE INDEX IF NOT EXISTS quests_description_search_index ON quests (description)');
            }
        }

        // Add price index for filtering
        DB::statement('CREATE INDEX IF NOT EXISTS quests_price_index ON quests (price)');
        
        // Add submitted_at index for auto-approval queries
        DB::statement('CREATE INDEX IF NOT EXISTS quests_submitted_at_index ON quests (submitted_at)');
        
        // Add composite index for pending approval queries
        DB::statement('CREATE INDEX IF NOT EXISTS quests_status_submitted_at_index ON quests (status, submitted_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quests', function (Blueprint $table) {
            try {
                $table->dropIndex('quests_title_fulltext');
                $table->dropIndex('quests_description_fulltext');
            } catch (\Exception $e) {
                // Index might not exist
            }
        });

        DB::statement('DROP INDEX IF EXISTS quests_title_search_index');
        DB::statement('DROP INDEX IF EXISTS quests_description_search_index');
        DB::statement('DROP INDEX IF EXISTS quests_price_index');
        DB::statement('DROP INDEX IF EXISTS quests_submitted_at_index');
        DB::statement('DROP INDEX IF EXISTS quests_status_submitted_at_index');
    }
};

