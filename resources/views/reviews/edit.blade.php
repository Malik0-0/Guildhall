@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto mt-8">
    <h1 class="text-3xl font-bold text-white mb-6">Edit Review</h1>

    <!-- Quest Information -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-white mb-3">{{ $review->quest->title }}</h2>
        <div class="text-gray-300 mb-4">{{ $review->quest->description }}</div>
        <div class="flex items-center space-x-4 text-sm text-gray-400">
            <span>Price: {{ $review->quest->price }} gold</span>
            <span>•</span>
            <span>Status: {{ ucfirst($review->quest->status) }}</span>
            <span>•</span>
            <span>Original Review: {{ $review->created_at->format('M d, Y') }}</span>
        </div>
    </div>

    <!-- Edit Review Form -->
    <form method="POST" action="{{ route('reviews.update', $review->id) }}">
        @csrf

        <div class="bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-bold text-white mb-4">Edit Your Review</h3>
            
            <!-- Rating -->
            <div class="mb-6">
                <label class="block text-gray-300 mb-3">Rating</label>
                <div class="flex items-center space-x-2">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button" class="star-rating text-3xl {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-600' }} hover:text-yellow-400 transition-colors" 
                                data-rating="{{ $i }}">
                            ★
                        </button>
                    @endfor
                    <span id="rating-text" class="ml-3 text-gray-400">
                        {{ ['Poor', 'Fair', 'Good', 'Very Good', 'Excellent'][$review->rating - 1] }}
                    </span>
                </div>
                <input type="hidden" id="rating-input" name="rating" value="{{ $review->rating }}" required>
            </div>

            <!-- Comment -->
            <div class="mb-6">
                <label for="comment" class="block text-gray-300 mb-2">Comment</label>
                <textarea id="comment" name="comment" rows="6" maxlength="1000" required
                          class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('comment', $review->comment) }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Minimum 10 characters, maximum 1000 characters</p>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-between">
                <a href="{{ route('quests.show', $review->quest_id) }}" 
                   class="text-gray-400 hover:text-gray-300">
                    Cancel
                </a>
                <div class="space-x-3">
                    <form method="POST" action="{{ route('reviews.destroy', $review->id) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-600 hover:bg-red-500 px-4 py-2 rounded text-white font-semibold"
                                onclick="return confirm('Are you sure you want to delete this review?')">
                            Delete Review
                        </button>
                    </form>
                    <button type="submit" id="submit-review"
                            class="bg-indigo-600 hover:bg-indigo-500 px-6 py-2 rounded text-white font-semibold">
                        Update Review
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-rating');
    const ratingInput = document.getElementById('rating-input');
    const ratingText = document.getElementById('rating-text');
    const submitButton = document.getElementById('submit-review');
    const commentTextarea = document.getElementById('comment');

    const ratingTexts = {
        1: 'Poor',
        2: 'Fair', 
        3: 'Good',
        4: 'Very Good',
        5: 'Excellent'
    };

    // Handle star rating
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            ratingInput.value = rating;
            ratingText.textContent = ratingTexts[rating];
            
            // Update star display
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.classList.remove('text-gray-600');
                    s.classList.add('text-yellow-400');
                } else {
                    s.classList.remove('text-yellow-400');
                    s.classList.add('text-gray-600');
                }
            });
        });

        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            ratingText.textContent = ratingTexts[rating];
            
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.classList.add('text-yellow-400');
                } else {
                    s.classList.remove('text-yellow-400');
                }
            });
        });
    });

    // Reset stars on mouse leave
    document.querySelector('.flex.items-center.space-x-2').addEventListener('mouseleave', function() {
        const currentRating = parseInt(ratingInput.value) || 0;
        stars.forEach((s, index) => {
            if (index < currentRating) {
                s.classList.add('text-yellow-400');
            } else {
                s.classList.remove('text-yellow-400');
            }
        });
        
        if (currentRating > 0) {
            ratingText.textContent = ratingTexts[currentRating];
        }
    });
});
</script>
@endsection
