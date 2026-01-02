@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-8">
    <!-- Profile Header -->
    <div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-lg p-6 mb-6 border border-gray-700 shadow-xl">
        <div class="flex items-start space-x-6">
            <div class="flex-shrink-0">
                @if($profile->avatar)
                    <img src="{{ Storage::url($profile->avatar) }}" alt="{{ $user->name }}" 
                         class="w-32 h-32 rounded-full object-cover border-4 border-gray-700 shadow-lg">
                @else
                    <div class="w-32 h-32 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                        <span class="text-3xl font-bold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                @endif
            </div>
            
            <div class="flex-1">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-4xl font-bold text-white">{{ $user->name }}</h1>
                            {{-- Trust Tier Badge --}}
                            <span class="px-4 py-2 rounded-full text-sm font-bold border-2 {{ $user->trust_tier_color }} shadow-lg">
                                {{ strtoupper($user->trust_tier_name) }}
                            </span>
                        </div>
                        <p class="text-gray-300 text-lg">
                            {{ $user->role === 'quest_giver' ? '💼 Patron' : '⚔️ Adventurer' }}
                            @if($profile->location)
                                • 📍 {{ $profile->location }}
                            @endif
                        </p>
                    </div>
                    @if($isOwnProfile)
                        <a href="{{ route('profile.edit') }}" 
                           class="bg-indigo-600 hover:bg-indigo-500 px-6 py-3 rounded-lg text-white transition-all hover:shadow-lg font-semibold">
                            ✏️ Edit Profile
                        </a>
                    @endif
                </div>

                {{-- XP and Level Display (Only for Adventurers) --}}
                @if($user->role === 'adventurer')
                <div class="mt-4 bg-gray-900 rounded-lg p-6 border border-gray-700">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-6">
                            <div class="text-center">
                                <span class="text-sm text-gray-400 block mb-1">Level</span>
                                <div class="text-3xl font-bold text-indigo-400">{{ $user->level ?? 1 }}</div>
                            </div>
                            <div class="text-center">
                                <span class="text-sm text-gray-400 block mb-1">XP</span>
                                <div class="text-2xl font-semibold text-yellow-400">{{ number_format($user->xp ?? 0) }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-400 block mb-1">Next Level</span>
                            <div class="text-sm font-medium text-gray-300">{{ number_format($user->xp_for_next_level ?? 100) }} XP</div>
                        </div>
                    </div>
                    {{-- XP Progress Bar --}}
                    <div class="w-full bg-gray-700 rounded-full h-3 mb-2">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-3 rounded-full transition-all duration-500 shadow-lg" 
                             style="width: {{ min(100, $user->xp_progress ?? 0) }}%"></div>
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ number_format($user->xp ?? 0) }} / {{ number_format($user->xp_for_next_level ?? 100) }} XP
                    </div>
                </div>
                @endif
                
                @if($profile->bio)
                    <div class="mt-4 bg-gray-900 rounded-lg p-4 border border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-300 mb-2">📝 Bio</h3>
                        <p class="text-gray-300 leading-relaxed">{{ $profile->bio }}</p>
                    </div>
                @endif
                
                @if($profile->website)
                    <div class="mt-3">
                        <a href="{{ $profile->website }}" target="_blank" 
                           class="text-indigo-400 hover:text-indigo-300 text-sm flex items-center gap-2">
                            🔗 {{ $profile->website }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistics (Role-Specific) -->
    @if($user->role === 'adventurer')
        {{-- Adventurer Statistics --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gradient-to-br from-yellow-600 to-yellow-700 p-6 rounded-xl text-center border border-yellow-500 shadow-lg">
                <div class="text-3xl font-bold text-white mb-2">🪙 {{ number_format($user->gold) }}</div>
                <div class="text-sm text-yellow-100 font-medium">Gold Balance</div>
            </div>
            <div class="bg-gradient-to-br from-green-600 to-green-700 p-6 rounded-xl text-center border border-green-500 shadow-lg">
                <div class="text-3xl font-bold text-white mb-2">{{ $questStats['completed'] ?? 0 }}</div>
                <div class="text-sm text-green-100 font-medium">Completed Quests</div>
            </div>
            <div class="bg-gradient-to-br from-blue-600 to-blue-700 p-6 rounded-xl text-center border border-blue-500 shadow-lg">
                <div class="text-3xl font-bold text-white mb-2">🪙 {{ number_format($questStats['total_earned'] ?? 0) }}</div>
                <div class="text-sm text-blue-100 font-medium">Total Earned</div>
            </div>
            <div class="bg-gradient-to-br from-purple-600 to-purple-700 p-6 rounded-xl text-center border border-purple-500 shadow-lg">
                <div class="text-3xl font-bold text-white mb-2">{{ number_format($profile->average_rating ?? 0, 1) }} ⭐</div>
                <div class="text-sm text-purple-100 font-medium">Average Rating</div>
            </div>
        </div>
    @else
        {{-- Patron Statistics --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-800 p-4 rounded-lg text-center border border-gray-700">
                <div class="text-2xl font-bold text-yellow-400">🪙 {{ number_format($user->gold) }}</div>
                <div class="text-sm text-gray-400 mt-1">Gold Balance</div>
            </div>
            <div class="bg-gray-800 p-4 rounded-lg text-center border border-gray-700">
                <div class="text-2xl font-bold text-indigo-400">{{ $questStats['created'] ?? 0 }}</div>
                <div class="text-sm text-gray-400 mt-1">Quests Created</div>
            </div>
            <div class="bg-gray-800 p-4 rounded-lg text-center border border-gray-700">
                <div class="text-2xl font-bold text-blue-400">{{ $questStats['completed'] ?? 0 }}</div>
                <div class="text-sm text-gray-400 mt-1">Completed</div>
            </div>
            <div class="bg-gray-800 p-4 rounded-lg text-center border border-gray-700">
                <div class="text-2xl font-bold text-red-400">🪙 {{ number_format($questStats['total_spent'] ?? 0) }}</div>
                <div class="text-sm text-gray-400 mt-1">Total Spent</div>
            </div>
        </div>
        @php
            $openCount = $questStats['open'] ?? 0;
            $inProgressCount = $questStats['in_progress'] ?? 0;
        @endphp
        @if($openCount > 0 || $inProgressCount > 0)
        <div class="grid grid-cols-2 gap-4 mb-6">
            @if($openCount > 0)
            <div class="bg-gray-800 p-4 rounded-lg text-center border border-gray-700">
                <div class="text-xl font-bold text-green-400">{{ $openCount }}</div>
                <div class="text-sm text-gray-400 mt-1">Open Quests</div>
            </div>
            @endif
            @if($inProgressCount > 0)
            <div class="bg-gray-800 p-4 rounded-lg text-center border border-gray-700">
                <div class="text-xl font-bold text-blue-400">{{ $inProgressCount }}</div>
                <div class="text-sm text-gray-400 mt-1">In Progress</div>
            </div>
            @endif
        </div>
        @endif
    @endif

    {{-- Skills Section (Only for Adventurers) --}}
    @if($user->role === 'adventurer' && $skills && $skills->count() > 0)
        <div class="bg-gray-800 rounded-lg p-6 mb-6 border border-gray-700">
            <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                ⚔️ Skills & Expertise
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($skills as $skill)
                    <div class="bg-gray-900 rounded-lg p-4 border border-gray-600">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-white">{{ $skill->name }}</h4>
                            <div class="flex items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="text-lg {{ $i <= $skill->level ? 'text-yellow-400' : 'text-gray-600' }}">⭐</span>
                                @endfor
                            </div>
                        </div>
                        <div class="text-sm text-gray-400">
                            Level {{ $skill->level }}/5
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Reviews -->
        <div class="lg:col-span-3">
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-white">
                        @if($user->role === 'adventurer')
                            Reviews Received
                        @else
                            Quest Activity
                        @endif
                    </h2>
                    @if($user->role === 'adventurer' && $profile->total_reviews > 0)
                        <a href="{{ route('profile.reviews', $user->name) }}" 
                           class="text-indigo-400 hover:text-indigo-300 text-sm">
                            View All ({{ $profile->total_reviews }})
                        </a>
                    @endif
                </div>
                
                @if($user->role === 'adventurer')
                    {{-- Show reviews received for adventurers --}}
                    @if($reviews->count() > 0)
                        <div class="space-y-4">
                            @foreach($reviews as $review)
                                <div class="border-b border-gray-700 pb-4 last:border-b-0">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2 mb-2">
                                                <span class="text-yellow-400">{{ str_repeat('⭐', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                                                <span class="text-sm text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-gray-300">{{ $review->comment }}</p>
                                            <div class="mt-2">
                                                <span class="text-sm text-gray-400">by </span>
                                                <a href="{{ route('profile.show.user', $review->reviewer->name) }}" 
                                                   class="text-indigo-400 hover:text-indigo-300 text-sm">
                                                    {{ $review->reviewer->name }}
                                                </a>
                                                <span class="text-sm text-gray-400"> on quest </span>
                                                <a href="{{ route('quests.show', $review->quest_id) }}" 
                                                   class="text-indigo-400 hover:text-indigo-300 text-sm">
                                                    "{{ $review->quest->title }}"
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-400">No reviews yet. Complete quests to receive reviews from patrons!</p>
                    @endif
                @else
                    {{-- Show quest summary for patrons --}}
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                                <div class="text-sm text-gray-400 mb-1">Total Quests Created</div>
                                <div class="text-2xl font-bold text-indigo-400">{{ $questStats['created'] ?? 0 }}</div>
                            </div>
                            <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                                <div class="text-sm text-gray-400 mb-1">Total Spent</div>
                                <div class="text-2xl font-bold text-red-400">🪙 {{ number_format($questStats['total_spent'] ?? 0) }}</div>
                            </div>
                        </div>
                        <p class="text-gray-400 text-sm">
                            As a patron, you can post quests and hire skilled adventurers to complete them. 
                            View your quests from the "My Quests" page.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
