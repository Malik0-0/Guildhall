@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto mt-8">
    <!-- Profile Header -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <div class="flex items-center space-x-4">
            @if($user->profile->avatar)
                <img src="{{ Storage::url($user->profile->avatar) }}" alt="{{ $user->name }}" 
                     class="w-16 h-16 rounded-full object-cover border-2 border-gray-700">
            @else
                <div class="w-16 h-16 rounded-full bg-gray-700 flex items-center justify-center">
                    <span class="text-xl font-bold text-gray-400">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $user->name }}'s Reviews</h1>
                <div class="flex items-center space-x-4 mt-1">
                    <div class="flex items-center space-x-1">
                        <span class="text-yellow-400">{{ str_repeat('★', round($averageRating)) }}{{ str_repeat('☆', 5 - round($averageRating)) }}</span>
                        <span class="text-gray-400">{{ number_format($averageRating, 1) }}</span>
                    </div>
                    <span class="text-gray-400">•</span>
                    <span class="text-gray-400">{{ $totalReviews }} reviews</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="bg-gray-800 rounded-lg p-6">
        @if($reviews->count() > 0)
            <div class="space-y-6">
                @foreach($reviews as $review)
                    <div class="border-b border-gray-700 pb-6 last:border-b-0">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-3">
                                    <a href="{{ route('profile.show', $review->reviewer->name) }}" 
                                       class="flex items-center space-x-2 hover:opacity-80">
                                        @if($review->reviewer->profile->avatar)
                                            <img src="{{ Storage::url($review->reviewer->profile->avatar) }}" 
                                                 alt="{{ $review->reviewer->name }}" 
                                                 class="w-10 h-10 rounded-full object-cover">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center">
                                                <span class="text-sm font-bold text-gray-400">{{ strtoupper(substr($review->reviewer->name, 0, 1)) }}</span>
                                            </div>
                                        @endif
                                        <span class="text-white font-medium">{{ $review->reviewer->name }}</span>
                                    </a>
                                    <span class="text-yellow-400">{{ $review->stars }}</span>
                                    <span class="text-sm text-gray-400">{{ $review->created_at->format('M d, Y') }}</span>
                                </div>
                                
                                <p class="text-gray-300 mb-3">{{ $review->comment }}</p>
                                
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-400">Review for quest:</span>
                                    <a href="{{ route('quests.show', $review->quest_id) }}" 
                                       class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">
                                        "{{ $review->quest->title }}"
                                    </a>
                                    <span class="text-sm text-gray-400">• {{ $review->quest->price }} gold</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-6">
                {{ $reviews->links() }}
            </div>
        @else
            <div class="text-center py-8">
                <div class="text-gray-400 text-lg mb-2">No reviews yet</div>
                <p class="text-gray-500">This user hasn't received any reviews yet.</p>
            </div>
        @endif
    </div>
    
    <!-- Back to Profile -->
    <div class="mt-6 text-center">
        <a href="{{ route('profile.show', $user->name) }}" 
           class="text-indigo-400 hover:text-indigo-300">
            ← Back to Profile
        </a>
    </div>
</div>
@endsection
