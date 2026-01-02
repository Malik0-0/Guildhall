<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserSkill;
use App\Models\UserReview;
use App\Models\Quest;
use App\Helpers\SanitizeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show($username = null)
    {
        $user = $username 
            ? User::with(['profile', 'reviews.reviewer', 'reviews.quest'])->where('name', $username)->firstOrFail() 
            : Auth::user()->load(['profile', 'reviews.reviewer', 'reviews.quest']);
        
        // Ensure profile exists and update rating
        $profile = $user->getOrCreateProfile();
        if ($profile->wasRecentlyCreated) {
            // Update rating if profile was just created
            $averageRating = $user->reviews()->avg('rating') ?? 0;
            $totalReviews = $user->reviews()->count();
            $profile->average_rating = $averageRating;
            $profile->total_reviews = $totalReviews;
            $profile->save();
        }
        $skills = $user->userSkills()->orderBy('level', 'desc')->get();
        $reviews = $user->reviews()->with('reviewer', 'quest')->latest()->paginate(5);
        
        // Get role-specific quest statistics
        if ($user->role === User::ROLE_ADVENTURER) {
            // Adventurer stats
            $questStats = [
                'accepted' => Quest::where('adventurer_id', $user->id)->count(),
                'completed' => Quest::where('adventurer_id', $user->id)->where('status', Quest::STATUS_COMPLETED)->count(),
                'total_earned' => Quest::where('adventurer_id', $user->id)->where('status', Quest::STATUS_COMPLETED)->sum('price'),
            ];
        } else {
            // Patron stats
            $questStats = [
                'created' => Quest::where('patron_id', $user->id)->count(),
                'open' => Quest::where('patron_id', $user->id)->where('status', Quest::STATUS_OPEN)->count(),
                'in_progress' => Quest::where('patron_id', $user->id)->where('status', Quest::STATUS_ACCEPTED)->count(),
                'completed' => Quest::where('patron_id', $user->id)->where('status', Quest::STATUS_COMPLETED)->count(),
                'total_spent' => Quest::where('patron_id', $user->id)->where('status', Quest::STATUS_COMPLETED)->sum('price'),
            ];
        }

        $isOwnProfile = Auth::id() === $user->id;

        return view('profile.show', compact('user', 'profile', 'skills', 'reviews', 'questStats', 'isOwnProfile'));
    }

    /**
     * Show the form for editing the user's profile.
     */
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->profile ?? new UserProfile(['user_id' => $user->id]);
        $skills = $user->userSkills;

        return view('profile.edit', compact('user', 'profile', 'skills'));
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'bio' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'skills' => 'nullable|array',
            'skills.*.name' => 'required|string|max:50',
            'skills.*.level' => 'required|integer|min:1|max:5',
        ]);

        DB::transaction(function () use ($user, $validated, $request) {
            // Get or create profile
            $profile = $user->profile()->firstOrCreate(
                ['user_id' => $user->id],
                ['completed_quests' => 0, 'cancelled_quests' => 0, 'total_earned' => 0, 'total_spent' => 0]
            );

            // Update profile fields (sanitize input)
            $profile->bio = isset($validated['bio']) ? SanitizeHelper::sanitizeHtml($validated['bio']) : null;
            $profile->location = isset($validated['location']) ? SanitizeHelper::sanitizeText($validated['location']) : null;
            $profile->website = isset($validated['website']) ? SanitizeHelper::sanitizeUrl($validated['website']) : null;

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $avatarPath = $avatar->store('avatars', 'public');
                
                // Delete old avatar if exists
                if ($profile->avatar) {
                    Storage::disk('public')->delete($profile->avatar);
                }
                
                $profile->avatar = $avatarPath;
            }

            $profile->save();

            // Update skills
            if (isset($validated['skills'])) {
                // Delete existing skills
                $user->userSkills()->delete();
                
                // Create new skills
                foreach ($validated['skills'] as $skillData) {
                    if (!empty($skillData['name'])) {
                        UserSkill::create([
                            'user_id' => $user->id,
                            'name' => $skillData['name'],
                            'level' => $skillData['level'],
                        ]);
                    }
                }
            }
        });

        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Display the user's reviews.
     */
    public function reviews($username = null)
    {
        $user = $username ? User::where('name', $username)->firstOrFail() : Auth::user();
        
        $reviews = $user->reviews()
            ->with('reviewer', 'quest')
            ->latest()
            ->paginate(10);

        $averageRating = $user->profile->average_rating ?? 0;
        $totalReviews = $user->profile->total_reviews ?? 0;

        return view('profile.reviews', compact('user', 'reviews', 'averageRating', 'totalReviews'));
    }

    /**
     * Update profile statistics.
     */
    public function updateStats()
    {
        $user = Auth::user();
        $profile = $user->profile()->firstOrCreate(
            ['user_id' => $user->id],
            ['completed_quests' => 0, 'cancelled_quests' => 0, 'total_earned' => 0, 'total_spent' => 0]
        );

        // Update quest statistics
        $profile->completed_quests = Quest::where('adventurer_id', $user->id)
            ->where('status', Quest::STATUS_COMPLETED)
            ->count();
        
        $profile->cancelled_quests = Quest::where('adventurer_id', $user->id)
            ->where('status', Quest::STATUS_CANCELLED)
            ->count();

        // Calculate total earned and spent
        $profile->total_earned = Quest::where('adventurer_id', $user->id)
            ->where('status', Quest::STATUS_COMPLETED)
            ->sum('price');
        
        $profile->total_spent = Quest::where('patron_id', $user->id)
            ->where('status', Quest::STATUS_COMPLETED)
            ->sum('price');

        $profile->updateSuccessRate();
        $profile->updateLastActive();

        return response()->json(['success' => true]);
    }
}
