<?php

namespace App\Jobs;

use App\Services\QuestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoApprovePendingQuestsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $hoursTimeout = 72
    ) {}

    /**
     * Execute the job.
     */
    public function handle(QuestService $questService): void
    {
        try {
            $questService->autoApprovePendingQuests($this->hoursTimeout);
            Log::info('Auto-approval job completed successfully');
        } catch (\Exception $e) {
            Log::error('Auto-approval job failed: ' . $e->getMessage());
            throw $e;
        }
    }
}

