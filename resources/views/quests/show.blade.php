@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-0">
        <!-- Back Button -->
        <a href="/quests" class="inline-flex items-center text-gray-400 hover:text-white mb-6">
            ← Back to Quest Board
        </a>

        <!-- Quest Header -->
        <div class="bg-gray-900 border border-gray-800 rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-start mb-4 gap-4">
                <div class="flex-1">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">{{ $quest->title }}</h1>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-xs sm:text-sm text-gray-400">
                        <span>Posted by {{ $quest->patron->name }}</span>
                        <span class="hidden sm:inline">•</span>
                        <span>{{ $quest->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="text-left sm:text-right">
                    <div class="text-xl sm:text-2xl font-bold text-yellow-400">{{ number_format($quest->price) }}</div>
                    <div class="text-xs sm:text-sm text-gray-400">Gold Coins</div>
                </div>
            </div>

            <!-- Categories and Tags -->
            @if($quest->categories->isNotEmpty() || $quest->tags->isNotEmpty())
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($quest->categories as $category)
                        <span class="px-3 py-1 rounded-full text-xs font-medium" style="background-color: {{ $category->color }}20; color: {{ $category->color }}">
                            {{ $category->name }}
                        </span>
                    @endforeach
                    @foreach($quest->tags as $tag)
                        <span class="px-3 py-1 bg-gray-800 rounded-full text-xs text-gray-300">
                            #{{ $tag->name }}
                        </span>
                    @endforeach
                </div>
            @endif

            <!-- Status Badge -->
            <div class="mb-4" id="quest-status-badge">
                @switch($quest->status)
                    @case(\App\Models\Quest::STATUS_OPEN)
                        <span class="px-3 py-1 bg-green-600 text-white rounded-full text-sm font-medium">Open</span>
                        @break
                    @case(\App\Models\Quest::STATUS_ACCEPTED)
                        <span class="px-3 py-1 bg-blue-600 text-white rounded-full text-sm font-medium">Accepted</span>
                        @break
                    @case(\App\Models\Quest::STATUS_PENDING_APPROVAL)
                        <span class="px-3 py-1 bg-yellow-600 text-white rounded-full text-sm font-medium">Pending Approval</span>
                        @break
                    @case(\App\Models\Quest::STATUS_COMPLETED)
                        <span class="px-3 py-1 bg-gray-600 text-white rounded-full text-sm font-medium">Completed</span>
                        @break
                    @case(\App\Models\Quest::STATUS_CANCELLED)
                        <span class="px-3 py-1 bg-red-600 text-white rounded-full text-sm font-medium">Cancelled</span>
                        @break
                @endswitch
            </div>

            <!-- Description -->
            <div class="prose prose-invert max-w-none">
                <p class="text-gray-300 whitespace-pre-wrap">{{ $quest->description }}</p>
            </div>
        </div>

        <!-- Messages Section -->
        @auth
            @if($quest->adventurer_id && ($quest->patron_id === Auth::id() || $quest->adventurer_id === Auth::id()))
                @php
                    $unreadCount = \App\Models\Message::where('quest_id', $quest->id)
                        ->where('receiver_id', Auth::id())
                        ->where('is_read', false)
                        ->count();
                @endphp
                <div class="bg-gray-900 border border-gray-800 rounded-lg p-4 sm:p-6 mb-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold mb-1">Quest Messages</h3>
                            <p class="text-sm text-gray-400">Communicate with {{ $quest->patron_id === Auth::id() ? $quest->adventurer->name : $quest->patron->name }}</p>
                        </div>
                        <a href="{{ route('messages.thread', $quest->id) }}" 
                           class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded font-medium transition-colors text-sm sm:text-base relative">
                            💬 View Messages
                            @if($unreadCount > 0)
                                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </a>
                    </div>
                </div>
            @endif
        @endauth

        <!-- Quest Actions -->
        @auth
            <div class="bg-gray-900 border border-gray-800 rounded-lg p-4 sm:p-6 mb-6">
                @if($quest->isOpen())
                    @if(Auth::user()->role === \App\Models\User::ROLE_ADVENTURER)
                        @if($userProposal)
                            <!-- Already Proposed -->
                            <div class="bg-blue-900/30 border border-blue-700 rounded p-4">
                                <h3 class="text-blue-300 font-semibold mb-2">✅ Proposal Submitted</h3>
                                <p class="text-gray-300 mb-2">{{ $userProposal->message }}</p>
                                <p class="text-sm text-gray-400">Estimated completion: {{ $userProposal->estimated_completion_time }}</p>
                            </div>
                        @else
                            <!-- Proposal Form -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-white">Apply for this Quest</h3>
                                <form method="POST" action="{{ route('proposals.apply', $quest->id) }}" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label for="message" class="block text-sm font-medium text-gray-300 mb-2">
                                            Proposal Message <span class="text-red-400">*</span>
                                            <span class="text-xs text-gray-400 block">Max 300 characters - Explain why you're the best fit</span>
                                        </label>
                                        <textarea 
                                            id="message"
                                            name="message" 
                                            placeholder="Describe your skills and experience relevant to this quest..."
                                            rows="3"
                                            maxlength="300"
                                            required
                                            class="w-full bg-gray-800 border border-gray-700 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-vertical"
                                        >{{ old('message') }}</textarea>
                                        <p class="text-xs text-gray-400 mt-1">
                                            <span id="char-count">0</span>/300 characters
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <label for="estimated_completion_time" class="block text-sm font-medium text-gray-300 mb-2">
                                            Estimated Completion Time <span class="text-red-400">*</span>
                                            <span class="text-xs text-gray-400 block">e.g., "2-3 days", "1 week", "3 business days"</span>
                                        </label>
                                        <input 
                                            type="text"
                                            id="estimated_completion_time"
                                            name="estimated_completion_time" 
                                            placeholder="e.g., 2-3 days"
                                            maxlength="100"
                                            required
                                            class="w-full bg-gray-800 border border-gray-700 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                            value="{{ old('estimated_completion_time') }}"
                                        >
                                    </div>
                                    
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 px-6 py-2 rounded font-medium transition-colors">
                                        Submit Proposal
                                    </button>
                                </form>
                            </div>
                        @endif
                    @elseif($quest->patron_id === Auth::id())
                        <!-- Patron Actions -->
                        <div class="flex gap-3">
                            <a href="{{ route('proposals.index', $quest->id) }}" class="bg-indigo-600 hover:bg-indigo-500 px-6 py-2 rounded font-medium transition-colors">
                                📋 View Proposals ({{ $quest->proposals->count() }})
                            </a>
                            <form method="POST" action="{{ route('quests.cancel', $quest->id) }}" class="inline" 
                                  onsubmit="return confirm('Are you sure you want to cancel this quest? Your gold will be refunded.');">
                                @csrf
                                <button type="submit" class="bg-red-600 hover:bg-red-500 px-6 py-2 rounded font-medium transition-colors">
                                    Cancel Quest
                                </button>
                            </form>
                        </div>
                    @else
                        <p class="text-gray-400">Only adventurers can apply for quests.</p>
                    @endif
                @elseif($quest->isAccepted())
                    @if($quest->adventurer_id === Auth::id())
                        <a href="/quests/{{ $quest->id }}/complete" class="bg-green-600 hover:bg-green-500 px-6 py-2 rounded font-medium transition-colors inline-block">
                            Submit Evidence
                        </a>
                    @elseif($quest->patron_id === Auth::id())
                        <p class="text-gray-400">Quest has been accepted by an adventurer. Waiting for evidence submission.</p>
                    @endif
                @elseif($quest->isPendingApproval())
                    @if($quest->patron_id === Auth::id())
                        {{-- Patron can approve or reject --}}
                        <div class="space-y-4">
                            @if($quest->evidence)
                                <div class="bg-gray-800 rounded p-4 mb-4">
                                    <h3 class="font-semibold mb-2">Submitted Evidence:</h3>
                                    <p class="text-gray-300 whitespace-pre-wrap mb-4">{{ $quest->evidence }}</p>
                                    
                                    @if($quest->evidence_files && count($quest->evidence_files) > 0)
                                        <div class="mt-4">
                                            <h4 class="text-sm font-semibold mb-2">Supporting Files:</h4>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                @foreach($quest->evidence_files as $file)
                                                    <a href="{{ asset('storage/' . $file['path']) }}" target="_blank" 
                                                       class="flex items-center gap-2 p-2 bg-gray-700 rounded hover:bg-gray-600 transition-colors">
                                                        <span class="text-sm">📎</span>
                                                        <span class="text-sm text-gray-300 truncate">{{ $file['name'] }}</span>
                                                        <span class="text-xs text-gray-400 ml-auto">{{ number_format($file['size'] / 1024, 1) }} KB</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if($quest->submitted_at)
                                        <p class="text-xs text-gray-400 mt-4">Submitted: {{ $quest->submitted_at->diffForHumans() }}</p>
                                    @endif
                                </div>
                            @endif
                            
                            <div class="flex gap-3">
                                <form method="POST" action="{{ route('quests.approve', $quest->id) }}" class="inline"
                                      onsubmit="return confirm('Approve this quest? Payment of {{ number_format($quest->price) }} gold coins will be processed immediately.');">
                                    @csrf
                                    <button type="submit" class="bg-green-600 hover:bg-green-500 px-6 py-2 rounded font-medium transition-colors">
                                        ✅ Approve & Pay
                                    </button>
                                </form>
                                
                                <button onclick="document.getElementById('reject-form').classList.toggle('hidden')" 
                                        class="bg-red-600 hover:bg-red-500 px-6 py-2 rounded font-medium transition-colors">
                                    ❌ Reject
                                </button>
                            </div>
                            
                            <form id="reject-form" method="POST" action="{{ route('quests.reject', $quest->id) }}" class="hidden space-y-3 bg-red-900/20 border border-red-700 rounded p-4">
                                @csrf
                                <div>
                                    <label for="rejection_reason" class="block text-sm font-medium mb-2">
                                        Rejection Reason <span class="text-red-400">*</span>
                                        <span class="text-xs text-gray-400">(Minimum 20 characters - Required to prevent abuse)</span>
                                    </label>
                                    <textarea 
                                        id="rejection_reason"
                                        name="rejection_reason" 
                                        placeholder="Please provide detailed feedback on what needs to be improved. This helps the adventurer understand how to better meet your requirements."
                                        rows="4"
                                        required
                                        minlength="20"
                                        class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent resize-vertical"
                                    >{{ old('rejection_reason') }}</textarea>
                                    <p class="text-xs text-gray-400 mt-1">The adventurer will see this feedback and can resubmit improved evidence.</p>
                                </div>
                                <div class="flex gap-3">
                                    <button type="submit" class="bg-red-600 hover:bg-red-500 px-4 py-2 rounded font-medium transition-colors text-sm">
                                        Submit Rejection
                                    </button>
                                    <button type="button" onclick="document.getElementById('reject-form').classList.add('hidden')" 
                                            class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded font-medium transition-colors text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                            
                            <div class="bg-yellow-900/20 border border-yellow-700 rounded p-3 text-sm">
                                <p class="text-yellow-300">⏰ This quest will be automatically approved after 72 hours if you don't respond. This protects the adventurer from delayed payments.</p>
                            </div>
                        </div>
                    @elseif($quest->adventurer_id === Auth::id())
                        {{-- Adventurer waiting for approval --}}
                        <div class="space-y-3">
                            <div class="bg-yellow-900/20 border border-yellow-700 rounded p-4">
                                <p class="text-yellow-300 font-medium mb-2">⏳ Evidence Submitted - Waiting for Patron Approval</p>
                                <p class="text-gray-300 text-sm">Your evidence has been submitted. The patron will review it. If no action is taken within 72 hours, the quest will be automatically approved and you will be paid.</p>
                            </div>
                            @if($quest->evidence)
                                <div class="bg-gray-800 rounded p-4">
                                    <h3 class="font-semibold mb-2">Your Submitted Evidence:</h3>
                                    <p class="text-gray-300 whitespace-pre-wrap">{{ $quest->evidence }}</p>
                                    
                                    @if($quest->evidence_files && count($quest->evidence_files) > 0)
                                        <div class="mt-4">
                                            <h4 class="text-sm font-semibold mb-2">Your Files:</h4>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                @foreach($quest->evidence_files as $file)
                                                    <a href="{{ asset('storage/' . $file['path']) }}" target="_blank" 
                                                       class="flex items-center gap-2 p-2 bg-gray-700 rounded hover:bg-gray-600 transition-colors">
                                                        <span class="text-sm">📎</span>
                                                        <span class="text-sm text-gray-300 truncate">{{ $file['name'] }}</span>
                                                        <span class="text-xs text-gray-400 ml-auto">{{ number_format($file['size'] / 1024, 1) }} KB</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if($quest->submitted_at)
                                        <p class="text-xs text-gray-400 mt-2">Submitted: {{ $quest->submitted_at->diffForHumans() }}</p>
                                    @endif
                                </div>
                            @endif
                            @if($quest->rejection_reason)
                                <div class="bg-red-900/20 border border-red-700 rounded p-4">
                                    <h3 class="font-semibold mb-2 text-red-300">Previous Rejection Feedback:</h3>
                                    <p class="text-gray-300 whitespace-pre-wrap">{{ $quest->rejection_reason }}</p>
                                    <p class="text-xs text-gray-400 mt-2">Please improve your evidence based on this feedback and resubmit.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                @elseif($quest->isCompleted())
                    @if($quest->evidence)
                        <div class="bg-gray-800 rounded p-4">
                            <h3 class="font-semibold mb-2">Submitted Evidence:</h3>
                            <p class="text-gray-300">{{ $quest->evidence }}</p>
                        </div>
                    @endif
                @elseif($quest->isCancelled())
                    <div class="bg-red-900/30 border border-red-700 rounded p-4">
                        <p class="text-red-300 font-medium">This quest has been cancelled.</p>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-gray-900 border border-gray-800 rounded-lg p-6 mb-6">
                <p class="text-gray-400">
                    <a href="/login" class="text-indigo-400 hover:text-indigo-300">Login</a> to participate in quests.
                </p>
            </div>
        @endauth

        <!-- Participants -->
        @if($quest->adventurer)
            <div class="bg-gray-900 border border-gray-800 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Quest Participants</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center">
                            {{ substr($quest->patron->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="font-medium">{{ $quest->patron->name }}</div>
                            <div class="text-sm text-gray-400">Quest Patron</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                            {{ substr($quest->adventurer->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="font-medium">{{ $quest->adventurer->name }}</div>
                            <div class="text-sm text-gray-400">Adventurer</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Reviews Section -->
        @if($quest->isCompleted())
            <div class="bg-gray-900 border border-gray-800 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Quest Reviews</h3>
                
                @auth
                    @if(Auth::id() === $quest->patron_id && Auth::user()->role === \App\Models\User::ROLE_QUEST_GIVER)
                        @if(!$quest->reviews()->where('reviewer_id', Auth::id())->exists())
                            <a href="/reviews/{{ $quest->id }}/create" class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded text-sm font-medium transition-colors inline-block mb-4">
                                Review Adventurer
                            </a>
                        @endif
                    @endif
                @endauth

                @if($quest->reviews->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($quest->reviews as $review)
                            <div class="bg-gray-800 rounded p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <div class="font-medium">{{ $review->reviewer->name }}</div>
                                        <div class="text-yellow-400 text-sm">{{ $review->stars }}</div>
                                    </div>
                                    <div class="text-sm text-gray-400">{{ $review->created_at->diffForHumans() }}</div>
                                </div>
                                <p class="text-gray-300">{{ $review->comment }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400">No reviews yet for this quest.</p>
                @endif
            </div>
        @endif

        <!-- Related Quests -->
        @if($relatedQuests->isNotEmpty())
            <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Related Quests</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($relatedQuests as $relatedQuest)
                        <div class="bg-gray-800 rounded p-4 hover:bg-gray-750 transition-colors">
                            <a href="/quests/{{ $relatedQuest->id }}" class="block">
                                <h4 class="font-medium mb-2">{{ $relatedQuest->title }}</h4>
                                <div class="text-yellow-400 font-semibold">{{ number_format($relatedQuest->price) }} gold</div>
                                <div class="text-sm text-gray-400">{{ $relatedQuest->patron->name }}</div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <script>
        // Character counter for proposal message
        document.addEventListener('DOMContentLoaded', function() {
            const messageTextarea = document.getElementById('message');
            const charCount = document.getElementById('char-count');
            
            if (messageTextarea && charCount) {
                messageTextarea.addEventListener('input', function() {
                    charCount.textContent = this.value.length;
                });
            }

            // Real-time quest status updates
            if (window.Echo) {
                const questId = {{ $quest->id }};
                
                // Listen for status changes on this specific quest
                window.Echo.private(`quest.${questId}`)
                    .listen('.quest.status.changed', (e) => {
                        if (e.id === questId) {
                            updateQuestStatus(e);
                        }
                    });
            }

            function updateQuestStatus(data) {
                // Update status badge
                const statusBadge = document.getElementById('quest-status-badge');
                if (statusBadge) {
                    const statusMap = {
                        'open': '<span class="px-3 py-1 bg-green-600 text-white rounded-full text-sm font-medium">Open</span>',
                        'accepted': '<span class="px-3 py-1 bg-blue-600 text-white rounded-full text-sm font-medium">Accepted</span>',
                        'pending_approval': '<span class="px-3 py-1 bg-yellow-600 text-white rounded-full text-sm font-medium">Pending Approval</span>',
                        'completed': '<span class="px-3 py-1 bg-gray-600 text-white rounded-full text-sm font-medium">Completed</span>',
                        'cancelled': '<span class="px-3 py-1 bg-red-600 text-white rounded-full text-sm font-medium">Cancelled</span>',
                    };
                    statusBadge.innerHTML = statusMap[data.status] || '';
                }

                // Show notification
                showNotification(`Quest status changed to: ${data.status.replace('_', ' ')}`);

                // Reload page after a short delay to show updated content
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }

            function showNotification(message) {
                // Create a temporary notification
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-indigo-600 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                notification.textContent = message;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.style.transition = 'opacity 0.3s';
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            }
        });
    </script>
@endsection