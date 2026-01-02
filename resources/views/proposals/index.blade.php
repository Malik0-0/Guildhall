@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-0">
        <!-- Back Button -->
        <a href="{{ route('quests.show', $quest->id) }}" class="inline-flex items-center text-gray-400 hover:text-white mb-6">
            ← Back to Quest
        </a>

        <!-- Header -->
        <div class="bg-gray-900 border border-gray-800 rounded-lg p-6 mb-6">
            <h1 class="text-2xl font-bold text-white mb-2">Proposals for "{{ $quest->title }}"</h1>
            <p class="text-gray-400">Review and select the best proposal for your quest.</p>
        </div>

        @if($proposals->isEmpty())
            <div class="bg-gray-900 border border-gray-800 rounded-lg p-8 text-center">
                <div class="text-gray-400 mb-4">📝</div>
                <h3 class="text-lg font-semibold text-white mb-2">No Proposals Yet</h3>
                <p class="text-gray-400">Adventurers haven't submitted any proposals for this quest yet.</p>
            </div>
        @else
            <!-- Proposals List -->
            <div class="space-y-4">
                @foreach($proposals as $proposal)
                    <div class="bg-gray-900 border border-gray-800 rounded-lg p-6">
                        <!-- Adventurer Info -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($proposal->adventurer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('profile.show.user', $proposal->adventurer->name) }}" 
                                       class="font-semibold text-white hover:text-indigo-300 transition-colors">
                                        {{ $proposal->adventurer->name }}
                                    </a>
                                    <div class="flex items-center gap-4 text-sm text-gray-400">
                                        <span>Level {{ $proposal->adventurer->level ?? 1 }}</span>
                                        @if($proposal->adventurer->profile && $proposal->adventurer->profile->average_rating)
                                            <span>⭐ {{ number_format($proposal->adventurer->profile->average_rating, 1) }}</span>
                                        @else
                                            <span>No rating yet</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <div class="text-sm text-gray-400 mb-2">Est. Completion</div>
                                <div class="text-white font-medium">{{ $proposal->estimated_completion_time }}</div>
                            </div>
                        </div>

                        <!-- Proposal Message -->
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-gray-300 mb-2">Proposal Message:</h4>
                            <p class="text-gray-300">{{ $proposal->message }}</p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3">
                            <form method="POST" action="{{ route('proposals.accept', [$quest->id, $proposal->id]) }}" 
                                  onsubmit="return confirm('Accept this proposal? The quest will be assigned to {{ $proposal->adventurer->name }} and all other proposals will be rejected.');">
                                @csrf
                                <button type="submit" class="bg-green-600 hover:bg-green-500 px-6 py-2 rounded font-medium transition-colors">
                                    ✅ Accept Proposal
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
