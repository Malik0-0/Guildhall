@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-4 sm:mt-8 px-4 sm:px-0">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 sm:mb-6 gap-4">
        <h2 class="text-2xl sm:text-3xl font-bold text-white">Quest Board</h2>
        @if(Auth::check() && Auth::user()->role === \App\Models\User::ROLE_QUEST_GIVER)
            <a href="{{ route('quests.create') }}" 
               class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded text-white text-sm sm:text-base whitespace-nowrap">
                Create Quest
            </a>
        @endif
    </div>

    {{-- Status Tabs --}}
    @auth
        <div class="mb-6">
            <div class="flex gap-2 border-b border-gray-700">
                <a href="{{ route('quests.index', ['status' => 'all'] + request()->except('status', 'page')) }}" 
                   class="px-4 py-2 text-sm font-medium transition-colors {{ ($status ?? 'all') === 'all' ? 'border-b-2 border-indigo-500 text-indigo-400' : 'text-gray-400 hover:text-gray-300' }}">
                    All Quests
                    @if(isset($stats['all']))
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs {{ ($status ?? 'all') === 'all' ? 'bg-indigo-900 text-indigo-300' : 'bg-gray-700 text-gray-400' }}">
                            {{ $stats['all'] }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('quests.index', ['status' => 'open'] + request()->except('status', 'page')) }}" 
                   class="px-4 py-2 text-sm font-medium transition-colors {{ ($status ?? 'all') === 'open' ? 'border-b-2 border-indigo-500 text-indigo-400' : 'text-gray-400 hover:text-gray-300' }}">
                    @if(Auth::user()->role === \App\Models\User::ROLE_ADVENTURER)
                        Open Quests
                    @else
                        My Open
                    @endif
                    @if(isset($stats['open']))
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs {{ ($status ?? 'all') === 'open' ? 'bg-indigo-900 text-indigo-300' : 'bg-gray-700 text-gray-400' }}">
                            {{ $stats['open'] }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('quests.index', ['status' => 'in_progress'] + request()->except('status', 'page')) }}" 
                   class="px-4 py-2 text-sm font-medium transition-colors {{ ($status ?? 'all') === 'in_progress' ? 'border-b-2 border-indigo-500 text-indigo-400' : 'text-gray-400 hover:text-gray-300' }}">
                    In Progress
                    @if(isset($stats['in_progress']))
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs {{ ($status ?? 'all') === 'in_progress' ? 'bg-indigo-900 text-indigo-300' : 'bg-gray-700 text-gray-400' }}">
                            {{ $stats['in_progress'] }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('quests.index', ['status' => 'completed'] + request()->except('status', 'page')) }}" 
                   class="px-4 py-2 text-sm font-medium transition-colors {{ ($status ?? 'all') === 'completed' ? 'border-b-2 border-indigo-500 text-indigo-400' : 'text-gray-400 hover:text-gray-300' }}">
                    Completed
                    @if(isset($stats['completed']))
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs {{ ($status ?? 'all') === 'completed' ? 'bg-indigo-900 text-indigo-300' : 'bg-gray-700 text-gray-400' }}">
                            {{ $stats['completed'] }}
                        </span>
                    @endif
                </a>
            </div>
            @if(Auth::user()->role === \App\Models\User::ROLE_ADVENTURER && ($status ?? 'all') === 'all')
                <p class="text-sm text-gray-400 mt-2">
                    All open quests + your accepted quests
                </p>
            @elseif(Auth::user()->role === \App\Models\User::ROLE_ADVENTURER && ($status ?? 'all') === 'open')
                <p class="text-sm text-gray-400 mt-2">
                    Browse all available quests you can apply for
                </p>
            @elseif(Auth::user()->role === \App\Models\User::ROLE_ADVENTURER)
                <p class="text-sm text-gray-400 mt-2">
                    Your quests that are in progress or completed
                </p>
            @else
                <p class="text-sm text-gray-400 mt-2">
                    Your quests organized by status
                </p>
            @endif
        </div>
    @endauth

    @if(session('success'))
        <div class="bg-green-600 text-white px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-600 text-white px-4 py-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Quest Statistics --}}
    @if(isset($stats) && !Auth::check())
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-800 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-blue-400">{{ $stats['open'] ?? 0 }}</div>
                <div class="text-sm text-gray-400">Open Quests</div>
            </div>
            <div class="bg-gray-800 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-yellow-400">{{ $stats['in_progress'] ?? 0 }}</div>
                <div class="text-sm text-gray-400">In Progress</div>
            </div>
            <div class="bg-gray-800 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-green-400">{{ $stats['completed'] ?? 0 }}</div>
                <div class="text-sm text-gray-400">Completed</div>
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-gray-800 rounded-lg p-4 sm:p-6 mb-6">
        <form method="GET" action="{{ route('quests.index') }}" class="space-y-4">
            @if(Auth::check())
                <input type="hidden" name="status" value="{{ $status ?? 'open' }}">
            @endif
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Search -->
                <div class="sm:col-span-2 lg:col-span-1">
                    <label for="search" class="block text-gray-300 mb-2 text-sm">Search</label>
                    <input type="text" id="search" name="search" 
                           value="{{ request('search') }}"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                           placeholder="Search quests...">
                </div>

                <!-- Category Filter -->
                <div>
                    <label for="category" class="block text-gray-300 mb-2 text-sm">Category</label>
                    <select id="category" name="category" 
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                    {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }} ({{ $category->quests_count }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tag Filter -->
                <div>
                    <label for="tag" class="block text-gray-300 mb-2 text-sm">Tag</label>
                    <select id="tag" name="tag" 
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="">All Tags</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}" 
                                    {{ request('tag') == $tag->id ? 'selected' : '' }}>
                                {{ $tag->name }} ({{ $tag->quests_count }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Price Range -->
                <div>
                    <label for="min_price" class="block text-gray-300 mb-2 text-sm">Min Price</label>
                    <input type="number" id="min_price" name="min_price" 
                           value="{{ request('min_price') }}"
                           min="0"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                           placeholder="Min gold">
                </div>

                <div>
                    <label for="max_price" class="block text-gray-300 mb-2 text-sm">Max Price</label>
                    <input type="number" id="max_price" name="max_price" 
                           value="{{ request('max_price') }}"
                           min="0"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                           placeholder="Max gold">
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="text-sm text-gray-400">
                    {{ $quests->total() }} quest{{ $quests->total() !== 1 ? 's' : '' }} found
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="button" onclick="clearFilters()" 
                            class="text-gray-400 hover:text-gray-300 text-sm px-3 py-1">
                        Clear Filters
                    </button>
                    <button type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded text-white text-sm">
                        Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Quests List --}}
    @if($quests->count() > 0)
        <div id="quests-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @foreach($quests as $quest)
                <div class="quest-card bg-gray-800 rounded-lg p-6 hover:border-gray-600 border border-gray-700 transition-colors" data-quest-id="{{ $quest->id }}">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-white mb-2">{{ $quest->title }}</h3>
                        <p class="text-gray-300 text-sm line-clamp-3">{{ $quest->description }}</p>
                    </div>

                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach($quest->categories as $category)
                            <span class="bg-indigo-900 text-indigo-300 px-2 py-1 rounded text-xs">
                                {{ $category->name }}
                            </span>
                        @endforeach
                        @foreach($quest->tags as $tag)
                            <span class="bg-gray-700 text-gray-300 px-2 py-1 rounded text-xs">
                                #{{ $tag->name }}
                            </span>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between mb-4">
                        <div class="text-2xl font-bold text-yellow-400">{{ $quest->price }}</div>
                        <div class="text-sm text-gray-400">Gold</div>
                    </div>

                    <!-- Status Badge -->
                    <div class="mb-4">
                        @switch($quest->status)
                            @case(\App\Models\Quest::STATUS_OPEN)
                                <span class="px-2 py-1 bg-green-600 text-white rounded text-xs font-medium">Open</span>
                                @break
                            @case(\App\Models\Quest::STATUS_ACCEPTED)
                                <span class="px-2 py-1 bg-blue-600 text-white rounded text-xs font-medium">Accepted</span>
                                @break
                            @case(\App\Models\Quest::STATUS_PENDING_APPROVAL)
                                <span class="px-2 py-1 bg-yellow-600 text-white rounded text-xs font-medium">Pending Approval</span>
                                @break
                            @case(\App\Models\Quest::STATUS_COMPLETED)
                                <span class="px-2 py-1 bg-gray-600 text-white rounded text-xs font-medium">Completed</span>
                                @break
                            @case(\App\Models\Quest::STATUS_CANCELLED)
                                <span class="px-2 py-1 bg-red-600 text-white rounded text-xs font-medium">Cancelled</span>
                                @break
                        @endswitch
                    </div>

                    <div class="flex items-center justify-between mb-4">
                        <div class="text-sm text-gray-400">
                            by <a href="{{ route('profile.show.user', $quest->patron->name) }}" 
                                 class="text-indigo-400 hover:text-indigo-300">
                                {{ $quest->patron->name }}
                            </a>
                        </div>
                        <div class="text-sm text-gray-400">
                            {{ $quest->created_at->diffForHumans() }}
                        </div>
                    </div>

                    <div class="flex space-x-2">
                        <a href="{{ route('quests.show', $quest->id) }}" 
                           class="flex-1 bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded text-center text-white text-sm">
                            View Details
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-gray-800 rounded-lg p-12 text-center">
            <div class="text-gray-400 text-lg mb-4">No quests found</div>
            <p class="text-gray-500 mb-6">
                @if(request()->hasAny(['search', 'category', 'tag']))
                    Try adjusting your filters or search terms.
                @else
                    Be the first to create a quest or check back later for new opportunities.
                @endif
            </p>
            @if(Auth::check() && Auth::user()->role === \App\Models\User::ROLE_QUEST_GIVER)
                <a href="{{ route('quests.create') }}" 
                   class="bg-indigo-600 hover:bg-indigo-500 px-6 py-2 rounded text-white">
                    Create First Quest
                </a>
            @endif
        </div>
    @endif

    <div class="mt-8">
        {{ $quests->links() }}
    </div>
</div>

<script>
function clearFilters() {
    window.location.href = '{{ route('quests.index') }}';
}

// Real-time quest updates
document.addEventListener('DOMContentLoaded', function() {
    if (!window.Echo) {
        return; // Echo not available
    }

    const currentStatus = '{{ $status ?? 'all' }}';
    const userRole = @json(Auth::check() ? Auth::user()->role : null);

    // Listen for new quest creation
    window.Echo.channel('quests')
        .listen('.quest.created', (e) => {
            // Only add if viewing all quests or open quests (for adventurers)
            if (currentStatus === 'all' || currentStatus === 'open') {
                // Check if user should see this quest
                if (!userRole || userRole === '{{ \App\Models\User::ROLE_ADVENTURER }}') {
                    addQuestToBoard(e);
                }
            }
        })
        .listen('.quest.status.changed', (e) => {
            // Update quest card if it exists
            updateQuestCard(e);
        });

    function addQuestToBoard(questData) {
        // Don't add if quest already exists
        const existingQuest = document.querySelector(`[data-quest-id="${questData.id}"]`);
        if (existingQuest) {
            return;
        }

        const questsGrid = document.getElementById('quests-grid');
        if (!questsGrid) {
            return;
        }

        // Create quest card HTML
        const questCard = createQuestCardHTML(questData);
        questsGrid.insertAdjacentHTML('afterbegin', questCard);

        // Remove "no quests" message if it exists
        const noQuests = document.querySelector('.bg-gray-800.rounded-lg.p-12.text-center');
        if (noQuests && questsGrid.children.length > 0) {
            const parent = noQuests.closest('.bg-gray-800');
            if (parent) {
                parent.remove();
            }
        }
    }

    function updateQuestCard(e) {
        const questCard = document.querySelector(`[data-quest-id="${e.id}"]`);
        if (!questCard) {
            return;
        }

        // Update status badge
        const statusBadge = questCard.querySelector('.status-badge');
        if (statusBadge) {
            const statusMap = {
                'open': '<span class="px-2 py-1 bg-green-600 text-white rounded text-xs font-medium">Open</span>',
                'accepted': '<span class="px-2 py-1 bg-blue-600 text-white rounded text-xs font-medium">Accepted</span>',
                'pending_approval': '<span class="px-2 py-1 bg-yellow-600 text-white rounded text-xs font-medium">Pending Approval</span>',
                'completed': '<span class="px-2 py-1 bg-gray-600 text-white rounded text-xs font-medium">Completed</span>',
                'cancelled': '<span class="px-2 py-1 bg-red-600 text-white rounded text-xs font-medium">Cancelled</span>',
            };
            statusBadge.innerHTML = statusMap[e.status] || '';
        }

        // If status changed to completed/cancelled and we're filtering, remove from view
        if ((e.status === 'completed' || e.status === 'cancelled') && 
            (currentStatus === 'open' || currentStatus === 'in_progress')) {
            questCard.style.transition = 'opacity 0.3s';
            questCard.style.opacity = '0';
            setTimeout(() => questCard.remove(), 300);
        }
    }

    function createQuestCardHTML(quest) {
        const categories = quest.categories.map(cat => 
            `<span class="bg-indigo-900 text-indigo-300 px-2 py-1 rounded text-xs">${escapeHtml(cat.name)}</span>`
        ).join('');
        
        const tags = quest.tags.map(tag => 
            `<span class="bg-gray-700 text-gray-300 px-2 py-1 rounded text-xs">#${escapeHtml(tag.name)}</span>`
        ).join('');

        const statusBadge = quest.status === 'open' 
            ? '<span class="px-2 py-1 bg-green-600 text-white rounded text-xs font-medium">Open</span>'
            : '';

        return `
            <div class="quest-card bg-gray-800 rounded-lg p-6 hover:border-gray-600 border border-gray-700 transition-colors" data-quest-id="${quest.id}">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-white mb-2">${escapeHtml(quest.title)}</h3>
                    <p class="text-gray-300 text-sm line-clamp-3">${escapeHtml(quest.description.substring(0, 200))}${quest.description.length > 200 ? '...' : ''}</p>
                </div>

                <div class="flex flex-wrap gap-2 mb-4">
                    ${categories}
                    ${tags}
                </div>

                <div class="flex items-center justify-between mb-4">
                    <div class="text-2xl font-bold text-yellow-400">${quest.price}</div>
                    <div class="text-sm text-gray-400">Gold</div>
                </div>

                <div class="mb-4 status-badge">
                    ${statusBadge}
                </div>

                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm text-gray-400">
                        by <a href="/profile/${escapeHtml(quest.patron.name)}" class="text-indigo-400 hover:text-indigo-300">
                            ${escapeHtml(quest.patron.name)}
                        </a>
                    </div>
                    <div class="text-sm text-gray-400">
                        ${quest.created_at_human}
                    </div>
                </div>

                <div class="flex space-x-2">
                    <a href="/quests/${quest.id}" class="flex-1 bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded text-center text-white text-sm">
                        View Details
                    </a>
                </div>
            </div>
        `;
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
});
</script>
@endsection