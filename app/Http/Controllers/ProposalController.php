<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\Quest;
use App\Models\User;
use App\Events\QuestStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProposalController extends Controller
{
    /**
     * Store a new proposal for a quest.
     */
    public function apply(Request $request, $questId)
    {
        // Only adventurers can apply
        if (Auth::user()->role !== User::ROLE_ADVENTURER) {
            return redirect()->back()->with('error', 'Only adventurers can apply for quests.');
        }

        $quest = Quest::findOrFail($questId);

        // Only allow proposals for open quests
        if (!$quest->isOpen()) {
            return redirect()->back()->with('error', 'This quest is not open for proposals.');
        }

        // Check if user already has a proposal for this quest
        $existingProposal = Proposal::where('quest_id', $questId)
            ->where('adventurer_id', Auth::id())
            ->first();

        if ($existingProposal) {
            return redirect()->back()->with('error', 'You have already submitted a proposal for this quest.');
        }

        $validated = $request->validate([
            'message' => 'required|string|max:300',
            'estimated_completion_time' => 'required|string|max:100',
        ]);

        try {
            DB::transaction(function () use ($validated, $quest) {
                Proposal::create([
                    'message' => $validated['message'],
                    'estimated_completion_time' => $validated['estimated_completion_time'],
                    'quest_id' => $quest->id,
                    'adventurer_id' => Auth::id(),
                    'status' => Proposal::STATUS_PENDING,
                ]);
            });

            return redirect()->back()->with('success', 'Proposal submitted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to submit proposal: ' . $e->getMessage());
        }
    }

    /**
     * Display proposals for a quest (for patrons).
     */
    public function index($questId)
    {
        $quest = Quest::with(['proposals.adventurer.profile'])->findOrFail($questId);

        // Only the quest patron can view proposals
        if ($quest->patron_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You can only view proposals for your own quests.');
        }

        // Only show proposals for open quests
        if (!$quest->isOpen()) {
            return redirect()->back()->with('error', 'Proposals are only available for open quests.');
        }

        $proposals = $quest->proposals()->pending()->with('adventurer.profile')->get();

        return view('proposals.index', compact('quest', 'proposals'));
    }

    /**
     * Accept a proposal and reject others.
     */
    public function accept($questId, $proposalId)
    {
        $quest = Quest::findOrFail($questId);
        $proposal = Proposal::with('adventurer')->findOrFail($proposalId);

        // Only the quest patron can accept proposals
        if ($quest->patron_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You can only accept proposals for your own quests.');
        }

        // Verify proposal belongs to this quest
        if ($proposal->quest_id !== $quest->id) {
            return redirect()->back()->with('error', 'Invalid proposal.');
        }

        // Only accept pending proposals
        if (!$proposal->isPending()) {
            return redirect()->back()->with('error', 'This proposal cannot be accepted.');
        }

        // Only accept proposals for open quests
        if (!$quest->isOpen()) {
            return redirect()->back()->with('error', 'This quest is no longer open for proposals.');
        }

        try {
            $previousStatus = $quest->status;
            $proposal->accept();

            // Broadcast status change
            $quest->refresh();
            broadcast(new QuestStatusChanged($quest, 'accepted', $previousStatus));

            return redirect()->route('quests.show', $quest->id)
                ->with('success', 'Proposal accepted! The quest has been assigned to the adventurer.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to accept proposal: ' . $e->getMessage());
        }
    }
}
