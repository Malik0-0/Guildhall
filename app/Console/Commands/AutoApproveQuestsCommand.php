<?php

namespace App\Console\Commands;

use App\Jobs\AutoApprovePendingQuestsJob;
use Illuminate\Console\Command;

class AutoApproveQuestsCommand extends Command
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
    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        
        $this->info("Dispatching auto-approval job for quests pending more than {$hours} hours...");
        
        AutoApprovePendingQuestsJob::dispatch($hours);
        
        $this->info('Auto-approval job dispatched successfully!');
        
        return Command::SUCCESS;
    }
}

