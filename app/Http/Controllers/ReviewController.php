<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserReview;
use App\Models\Quest;
use App\Helpers\SanitizeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Show the form for creating a new review.
     */
    public function create($questId)
    {
        $quest = Quest::findOrFail($questId);
        
        // Check if user can review this quest
        if (!$this->canReviewQuest($quest)) {
            return redirect()->back()->with('error', 'You cannot review this quest.');
        }

        // Check if already reviewed
        $existingReview = UserReview::where('reviewer_id', Auth::id())
            ->where('quest_id', $questId)
            ->first();

        if ($existingReview) {
            return redirect()->back()->with('error', 'You have already reviewed this quest.');
        }

        return view('reviews.create', compact('quest'));
    }

    /**
     * Store a newly created review.
     */
    public function store(Request $request, $questId)
    {
        $quest = Quest::findOrFail($questId);
        
        // Check if user can review this quest
        if (!$this->canReviewQuest($quest)) {
            return redirect()->back()->with('error', 'You cannot review this quest.');
        }

        // Check if already reviewed
        $existingReview = UserReview::where('reviewer_id', Auth::id())
            ->where('quest_id', $questId)
            ->first();

        if ($existingReview) {
            return redirect()->back()->with('error', 'You have already reviewed this quest.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        // Only patrons review adventurers
        $reviewedUserId = $quest->adventurer_id;

        $review = UserReview::create([
            'reviewer_id' => Auth::id(),
            'reviewed_user_id' => $reviewedUserId,
            'quest_id' => $questId,
            'rating' => (int)$validated['rating'],
            'comment' => SanitizeHelper::sanitizeHtml($validated['comment']),
        ]);

        // Update the reviewed user's profile statistics
        $this->updateUserStats($reviewedUserId);

        return redirect()->route('quests.show', $questId)
            ->with('success', 'Review submitted successfully!');
    }

    /**
     * Show the form for editing a review.
     */
    public function edit($id)
    {
        $review = UserReview::findOrFail($id);
        
        // Only the reviewer can edit their review
        if ($review->reviewer_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You can only edit your own reviews.');
        }

        return view('reviews.edit', compact('review'));
    }

    /**
     * Update the specified review.
     */
    public function update(Request $request, $id)
    {
        $review = UserReview::findOrFail($id);
        
        // Only the reviewer can edit their review
        if ($review->reviewer_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You can only edit your own reviews.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $review->update([
            'rating' => (int)$validated['rating'],
            'comment' => SanitizeHelper::sanitizeHtml($validated['comment']),
        ]);

        // Update the reviewed user's profile statistics
        $this->updateUserStats($review->reviewed_user_id);

        return redirect()->route('quests.show', $review->quest_id)
            ->with('success', 'Review updated successfully!');
    }

    /**
     * Remove the specified review.
     */
    public function destroy($id)
    {
        $review = UserReview::findOrFail($id);
        
        // Only the reviewer can delete their review
        if ($review->reviewer_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You can only delete your own reviews.');
        }

        $reviewedUserId = $review->reviewed_user_id;
        $questId = $review->quest_id;

        $review->delete();

        // Update the reviewed user's profile statistics
        $this->updateUserStats($reviewedUserId);

        return redirect()->route('quests.show', $questId)
            ->with('success', 'Review deleted successfully!');
    }

    /**
     * Check if the current user can review the quest.
     * Only patrons (quest makers) can review adventurers.
     */
    private function canReviewQuest($quest)
    {
        $user = Auth::user();
        
        // Quest must be completed
        if (!$quest->isCompleted()) {
            return false;
        }

        // Only patrons (quest makers) can write reviews
        if ($user->role !== User::ROLE_QUEST_GIVER) {
            return false;
        }

        // User must be the patron of this quest
        if ($user->id !== $quest->patron_id) {
            return false;
        }

        return true;
    }

    /**
     * Update user profile statistics after review changes.
     */
    private function updateUserStats($userId)
    {
        $user = \App\Models\User::find($userId);
        if ($user) {
            // Ensure profile exists
            $profile = $user->getOrCreateProfile();
            
            // Update average rating and total reviews count
            $profile->average_rating = $user->reviews()->avg('rating') ?? 0;
            $profile->total_reviews = $user->reviews()->count();
            $profile->save();
        }
    }
}
