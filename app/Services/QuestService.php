<?php

namespace App\Services;

use App\Models\Quest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
// Email notifications disabled for MVP - uncomment when ready
// use App\Notifications\QuestAcceptedNotification;
// use App\Notifications\QuestEvidenceSubmittedNotification;
// use App\Notifications\QuestApprovedNotification;
// use App\Notifications\QuestRejectedNotification;

class QuestService
{
    /**
     * XP awarded per quest completion base amount.
     */
    const BASE_XP_PER_QUEST = 50;

    /**
     * XP multiplier based on quest price.
     */
    const XP_PER_GOLD = 0.1;

    /**
     * Submit evidence for quest completion (sets status to PENDING_APPROVAL).
     * Payment happens only after patron approval.
     * 
     * @return void
     */
    public function submitEvidence(Quest $quest, string $evidence, ?array $evidenceFiles = null): void
    {
        DB::transaction(function () use ($quest, $evidence, $evidenceFiles) {
            // Update quest with evidence and set status to pending approval
            $quest->update([
                'evidence' => \App\Helpers\SanitizeHelper::sanitizeHtml($evidence),
                'evidence_files' => $evidenceFiles,
                'status' => Quest::STATUS_PENDING_APPROVAL,
                'submitted_at' => now(),
                'rejection_reason' => null, // Clear any previous rejection
                'rejected_at' => null,
            ]);

            // Email notifications disabled for MVP
            // $quest->load(['patron', 'adventurer']);
            // $quest->patron->notify(new QuestEvidenceSubmittedNotification($quest));
        });
    }

    /**
     * Approve quest completion and process payment.
     * Called by patron after reviewing evidence.
     * 
     * @return array Returns ['leveled_up' => bool, 'new_level' => int|null]
     */
    public function approveQuest(Quest $quest): array
    {
        $result = ['leveled_up' => false, 'new_level' => null];
        
        DB::transaction(function () use ($quest, &$result) {
            // Refresh quest to get latest status
            $quest->refresh();
            
            // Must be pending approval
            if ($quest->status !== Quest::STATUS_PENDING_APPROVAL) {
                throw new \Exception('Only quests pending approval can be approved.');
            }

            // Get the adventurer
            $adventurer = User::findOrFail($quest->adventurer_id);
            $oldLevel = $adventurer->level ?? 1;
            
            // Transfer gold to adventurer (gold was already deducted from patron during quest creation)
            $adventurer->increment('gold', $quest->price);

            // Award XP to adventurer (this will update level if needed)
            $this->awardXP($adventurer, $quest->price);
            
            // Check if leveled up
            $adventurer->refresh();
            if (($adventurer->level ?? 1) > $oldLevel) {
                $result['leveled_up'] = true;
                $result['new_level'] = $adventurer->level;
            }

            // Update quest status to completed
            $quest->update([
                'status' => Quest::STATUS_COMPLETED,
                'approved_at' => now(),
            ]);

            // Update user profile stats
            $adventurer->updateQuestStats(true, $quest->price);
            
            // Get patron for stats update (no gold transaction needed)
            $patron = User::findOrFail($quest->patron_id);
            $patron->updateQuestStats(true, $quest->price);
        });

        // Email notifications disabled for MVP
        // $quest->load(['adventurer']);
        // $quest->adventurer->notify(new QuestApprovedNotification($quest, $result['leveled_up'], $result['new_level']));
        
        return $result;
    }

    /**
     * Reject quest evidence and request revision.
     * 
     * Rules:
     * - Only pending approval quests can be rejected
     * - Requires detailed rejection reason (min 20 chars) - protects adventurer from fake rejections
     * - Status returns to ACCEPTED so adventurer can resubmit
     */
    public function rejectEvidence(Quest $quest, string $rejectionReason): void
    {
        DB::transaction(function () use ($quest, $rejectionReason) {
            // Must be pending approval
            if ($quest->status !== Quest::STATUS_PENDING_APPROVAL) {
                throw new \Exception('Only quests pending approval can be rejected.');
            }

            // Require detailed rejection reason (min 20 chars) to prevent fake rejections
            if (strlen(trim($rejectionReason)) < 20) {
                throw new \Exception('Rejection reason must be at least 20 characters. Please provide detailed feedback.');
            }

            // Update quest: set status back to accepted (adventurer can resubmit)
            $quest->update([
                'status' => Quest::STATUS_ACCEPTED,
                'rejection_reason' => \App\Helpers\SanitizeHelper::sanitizeHtml($rejectionReason),
                'rejected_at' => now(),
                'submitted_at' => null, // Reset for resubmission
            ]);

            // Email notifications disabled for MVP
            // $quest->load(['adventurer']);
            // $quest->adventurer->notify(new QuestRejectedNotification($quest));
        });
    }

    /**
     * Auto-approve quests that have been pending approval for the specified hours.
     * Protects adventurer from being scammed by non-responsive patrons.
     * 
     * @param int $hoursTimeout Hours to wait before auto-approval (default: 72)
     */
    public function autoApprovePendingQuests(int $hoursTimeout = 72): void
    {
        $cutoffTime = now()->subHours($hoursTimeout);
        
        Quest::where('status', Quest::STATUS_PENDING_APPROVAL)
            ->where('submitted_at', '<=', $cutoffTime)
            ->each(function ($quest) {
                try {
                    $this->approveQuest($quest);
                } catch (\Exception $e) {
                    // Log error but continue with other quests
                    \Log::error('Auto-approval failed for quest ' . $quest->id . ': ' . $e->getMessage());
                }
            });
    }

    /**
     * Award XP to a user based on quest completion.
     */
    public function awardXP(User $user, int $questPrice): void
    {
        // Calculate XP: base XP + (price * XP_PER_GOLD)
        $xpGained = self::BASE_XP_PER_QUEST + (int)($questPrice * self::XP_PER_GOLD);
        
        $user->addXP($xpGained);
    }

    /**
     * Calculate level based on XP.
     */
    public static function calculateLevel(int $xp): int
    {
        // Level formula: level = floor(sqrt(xp / 100)) + 1
        // This gives progressive leveling (harder to level up as you go)
        return (int)floor(sqrt($xp / 100)) + 1;
    }

    /**
     * Calculate XP required for next level.
     */
    public static function xpForNextLevel(int $currentLevel): int
    {
        // XP required = ((level)^2 - 1) * 100
        return (($currentLevel * $currentLevel) - 1) * 100;
    }

    /**
     * Cancel a quest and handle refunds.
     * 
     * Rules:
     * - Only patron can cancel their own quest (checked in controller)
     * - Only OPEN quests can be cancelled (checked via canBeCancelled())
     * - Full gold refund to patron
     */
    public function cancelQuest(Quest $quest): void
    {
        DB::transaction(function () use ($quest) {
            // Refresh quest to get latest status
            $quest->refresh();
            
            // Only OPEN quests can be cancelled
            if (!$quest->isOpen()) {
                throw new \Exception('Only open quests can be cancelled.');
            }

            // Refund gold to patron
            $patron = User::findOrFail($quest->patron_id);
            $patron->increment('gold', $quest->price);

            // Mark quest as cancelled
            $quest->update([
                'status' => Quest::STATUS_CANCELLED,
            ]);
        });
    }
}
