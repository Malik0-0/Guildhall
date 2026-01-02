<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Console\Command;

class FixUserProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-user-profiles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix user profiles with missing ratings and ensure all users have profiles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing user profiles...');
        
        // Get all users
        $users = User::all();
        
        foreach ($users as $user) {
            // Ensure profile exists
            $profile = $user->getOrCreateProfile();
            
            // Update rating statistics
            $averageRating = $user->reviews()->avg('rating') ?? 0;
            $totalReviews = $user->reviews()->count();
            
            $profile->average_rating = $averageRating;
            $profile->total_reviews = $totalReviews;
            $profile->save();
            
            $this->line("Updated profile for user: {$user->name} (Rating: {$averageRating}, Reviews: {$totalReviews})");
        }
        
        $this->info('User profiles fixed successfully!');
    }
}
