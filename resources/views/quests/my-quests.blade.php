@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-3xl font-bold text-white">
                @if(Auth::user()->role === \App\Models\User::ROLE_QUEST_GIVER)
                    My Posted Quests
                @else
                    My Accepted Quests
                @endif
            </h2>
            <p class="text-gray-400 mt-1">
                @if(Auth::user()->role === \App\Models\User::ROLE_QUEST_GIVER)
                    Manage and track all quests you've created
                @else
                    Track your progress on accepted quests
                @endif
            </p>
        </div>
        <a href="{{ route('quests.index') }}" class="text-indigo-400 hover:text-indigo-300 text-sm inline-flex items-center">
            Browse Quest Board →
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-600 text-white px-4 py-3 rounded-lg mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-600 text-white px-4 py-3 rounded-lg mb-6 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @if($quests->isEmpty())
        <div class="bg-gray-800 rounded-lg p-12 text-center border border-gray-700">
            <div class="max-w-md mx-auto">
                <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-300 mb-2">
                    @if(Auth::user()->role === \App\Models\User::ROLE_QUEST_GIVER)
                        No Quests Posted Yet
                    @else
                        No Quests Accepted Yet
                    @endif
                </h3>
                <p class="text-gray-400 mb-6">
                    @if(Auth::user()->role === \App\Models\User::ROLE_QUEST_GIVER)
                        Start by creating your first quest to find skilled adventurers.
                    @else
                        Browse the quest board to find quests that match your skills.
                    @endif
                </p>
                <a href="{{ route('quests.index') }}" 
                   class="inline-block bg-indigo-600 hover:bg-indigo-500 px-6 py-3 rounded-lg text-white font-medium transition-colors">
                    @if(Auth::user()->role === \App\Models\User::ROLE_QUEST_GIVER)
                        Create Your First Quest
                    @else
                        Browse Quest Board
                    @endif
                </a>
            </div>
        </div>
    @else
        {{-- Quest Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            @php
                $stats = [
                    'open' => $quests->where('status', \App\Models\Quest::STATUS_OPEN)->count(),
                    'accepted' => $quests->where('status', \App\Models\Quest::STATUS_ACCEPTED)->count(),
                    'completed' => $quests->where('status', \App\Models\Quest::STATUS_COMPLETED)->count(),
                ];
            @endphp
            <div class="bg-gray-800 p-4 rounded-lg border border-gray-700">
                <div class="text-2xl font-bold text-blue-400">{{ $stats['open'] }}</div>
                <div class="text-sm text-gray-400 mt-1">Open</div>
            </div>
            <div class="bg-gray-800 p-4 rounded-lg border border-gray-700">
                <div class="text-2xl font-bold text-yellow-400">{{ $stats['accepted'] }}</div>
                <div class="text-sm text-gray-400 mt-1">In Progress</div>
            </div>
            <div class="bg-gray-800 p-4 rounded-lg border border-gray-700">
                <div class="text-2xl font-bold text-green-400">{{ $stats['completed'] }}</div>
                <div class="text-sm text-gray-400 mt-1">Completed</div>
            </div>
            <div class="bg-gray-800 p-4 rounded-lg border border-gray-700">
                <div class="text-2xl font-bold text-yellow-400">🪙 {{ number_format($quests->sum('price')) }}</div>
                <div class="text-sm text-gray-400 mt-1">Total Gold</div>
            </div>
        </div>

        {{-- Quest Cards --}}
        <div class="space-y-4">
            @foreach($quests as $quest)
                <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden hover:border-gray-600 transition-colors">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-xl font-semibold text-white">{{ $quest->title }}</h3>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        {{ $quest->isCompleted() ? 'bg-green-900 text-green-300' : 
                                           ($quest->isCancelled() ? 'bg-red-900 text-red-300' :
                                           ($quest->isPendingApproval() ? 'bg-yellow-700 text-yellow-200' :
                                           ($quest->isAccepted() ? 'bg-blue-900 text-blue-300' : 
                                           'bg-gray-700 text-gray-300'))) }}">
                                        {{ ucfirst(str_replace('_', ' ', $quest->status)) }}
                                    </span>
                                </div>
                                <p class="text-gray-300 line-clamp-2">{{ $quest->description }}</p>
                            </div>
                            <div class="ml-4 text-right">
                                <div class="text-2xl font-bold text-yellow-400">🪙 {{ number_format($quest->price) }}</div>
                                <div class="text-xs text-gray-400 mt-1">Gold Reward</div>
                            </div>
                        </div>

                        {{-- Categories and Tags --}}
                        @if($quest->categories->count() > 0 || $quest->tags->count() > 0)
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach($quest->categories->take(3) as $category)
                                    <span class="bg-indigo-900/50 text-indigo-300 px-2 py-1 rounded text-xs">
                                        {{ $category->name }}
                                    </span>
                                @endforeach
                                @foreach($quest->tags->take(3) as $tag)
                                    <span class="bg-gray-700 text-gray-300 px-2 py-1 rounded text-xs">
                                        #{{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        {{-- Quest Details --}}
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm text-gray-400 mb-4">
                            <div>
                                <span class="block text-xs text-gray-500 mb-1">Posted</span>
                                <span>{{ $quest->created_at->format('M d, Y') }}</span>
                            </div>
                            @if(Auth::user()->role === \App\Models\User::ROLE_QUEST_GIVER)
                                @if($quest->adventurer)
                                    <div>
                                        <span class="block text-xs text-gray-500 mb-1">Accepted By</span>
                                        <a href="{{ route('profile.show.user', $quest->adventurer->name) }}" 
                                           class="text-indigo-400 hover:text-indigo-300 font-medium">
                                            {{ $quest->adventurer->name }}
                                        </a>
                                    </div>
                                @else
                                    <div>
                                        <span class="block text-xs text-gray-500 mb-1">Status</span>
                                        <span class="text-yellow-400">Waiting for adventurer</span>
                                    </div>
                                @endif
                            @else
                                @if($quest->patron)
                                    <div>
                                        <span class="block text-xs text-gray-500 mb-1">Posted By</span>
                                        <a href="{{ route('profile.show.user', $quest->patron->name) }}" 
                                           class="text-indigo-400 hover:text-indigo-300 font-medium">
                                            {{ $quest->patron->name }}
                                        </a>
                                    </div>
                                @endif
                            @endif
                            <div>
                                <span class="block text-xs text-gray-500 mb-1">Updated</span>
                                <span>{{ $quest->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        {{-- Evidence Section (for completed quests) --}}
                        @if($quest->isCompleted() && $quest->evidence)
                            <div class="mt-4 p-4 bg-gray-900 rounded-lg border border-gray-700">
                                <p class="text-sm font-semibold text-gray-300 mb-2 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    @if(Auth::user()->role === \App\Models\User::ROLE_QUEST_GIVER)
                                        Completion Evidence
                                    @else
                                        Your Submitted Evidence
                                    @endif
                                </p>
                                <p class="text-gray-300 text-sm whitespace-pre-wrap">{{ $quest->evidence }}</p>
                            </div>
                        @endif

                        {{-- Action Buttons --}}
                        <div class="flex gap-3 mt-4 pt-4 border-t border-gray-700">
                            <a href="{{ route('quests.show', $quest->id) }}" 
                               class="flex-1 bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg text-center text-white text-sm font-medium transition-colors">
                                View Details
                            </a>
                            @if($quest->adventurer_id && ($quest->patron_id === Auth::id() || $quest->adventurer_id === Auth::id()))
                                @php
                                    $unreadCount = \App\Models\Message::where('quest_id', $quest->id)
                                        ->where('receiver_id', Auth::id())
                                        ->where('is_read', false)
                                        ->count();
                                @endphp
                                <a href="{{ route('messages.thread', $quest->id) }}" 
                                   class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded-lg text-center text-white text-sm font-medium transition-colors relative">
                                    💬 Messages
                                    @if($unreadCount > 0)
                                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                        </span>
                                    @endif
                                </a>
                            @endif
                            @if(Auth::user()->role === \App\Models\User::ROLE_ADVENTURER && $quest->isAccepted())
                                <a href="{{ route('quests.complete', $quest->id) }}" 
                                   class="flex-1 bg-green-600 hover:bg-green-500 px-4 py-2 rounded-lg text-center text-white text-sm font-medium transition-colors flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Submit Evidence
                                </a>
                            @elseif(Auth::user()->role === \App\Models\User::ROLE_QUEST_GIVER && $quest->isPendingApproval())
                                <div class="flex-1">
                                    <a href="{{ route('quests.show', $quest->id) }}" 
                                       class="w-full bg-yellow-600 hover:bg-yellow-500 px-4 py-2 rounded-lg text-center text-white text-sm font-medium transition-colors block">
                                        Review Evidence
                                    </a>
                                    <p class="text-xs text-yellow-400 mt-1 text-center">Auto-approves in 72h</p>
                                </div>
                            @elseif(Auth::user()->role === \App\Models\User::ROLE_QUEST_GIVER && $quest->canBeCancelled())
                                <form method="POST" action="{{ route('quests.cancel', $quest->id) }}" class="flex-1"
                                      onsubmit="return confirm('Are you sure you want to cancel this quest? Your gold will be refunded.');">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full bg-red-600 hover:bg-red-500 px-4 py-2 rounded-lg text-center text-white text-sm font-medium transition-colors">
                                        Cancel Quest
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $quests->links() }}
        </div>
    @endif
</div>
@endsection
