<?php

namespace App\Console\Commands;

use App\Services\QuestService;
use Illuminate\Console\Command;

class AutoApprovePendingQuests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quests:auto-approve {--hours=72 : Hours to wait before auto-approval}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-approve quests that have been pending approval for the specified hours';

    /**
     * Execute the console command.
     */
    public function handle(QuestService $questService): int
    {
        $hours = (int) $this->option('hours');
        
        $this->info("Auto-approving quests pending approval for {$hours} hours or more...");
        
        $questService->autoApprovePendingQuests($hours);
        
        $this->info('Auto-approval process completed.');
        
        return Command::SUCCESS;
    }
}

