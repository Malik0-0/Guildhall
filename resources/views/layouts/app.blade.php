<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen">
    <header class="border-b border-gray-800 px-4 sm:px-6 py-3 sm:py-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="font-bold text-base sm:text-lg">
                <a href="/" class="font-bold text-base sm:text-lg">Guildhall</a>
            </div>

            <nav class="flex flex-wrap items-center gap-2 sm:gap-4 text-xs sm:text-sm text-gray-300 w-full sm:w-auto">
                <a href="/quests" class="hover:text-white whitespace-nowrap">Quest Board</a>
                @auth
                    <span class="text-yellow-400 font-medium px-2 whitespace-nowrap">
                        🪙 {{ number_format(Auth::user()->gold ?? 0) }} Gold
                    </span>
                    @if(Auth::user()->role === \App\Models\User::ROLE_QUEST_GIVER)
                        <a href="{{ route('top-up.page') }}" class="hover:text-white text-xs bg-indigo-600 hover:bg-indigo-500 px-2 py-1 rounded transition-colors whitespace-nowrap">
                            Top Up
                        </a>
                    @elseif(Auth::user()->role === \App\Models\User::ROLE_ADVENTURER)
                        <a href="{{ route('withdrawal.index') }}" class="hover:text-white text-xs bg-green-600 hover:bg-green-500 px-2 py-1 rounded transition-colors whitespace-nowrap">
                            Withdraw
                        </a>
                    @endif
                    @if(Auth::user()->role === \App\Models\User::ROLE_QUEST_GIVER)
                        <a href="{{ route('quests.create') }}" class="hover:text-white whitespace-nowrap hidden sm:inline">Post Quest</a>
                        <a href="{{ route('quests.my-quests') }}" class="hover:text-white whitespace-nowrap">My Quests</a>
                    @elseif(Auth::user()->role === \App\Models\User::ROLE_ADVENTURER)
                        <a href="{{ route('quests.my-quests') }}" class="hover:text-white whitespace-nowrap">My Quests</a>
                    @endif
                    <a href="{{ route('profile.show') }}" class="hover:text-white whitespace-nowrap">Profile</a>
                    <a href="{{ route('payment.history') }}" class="hover:text-white whitespace-nowrap">Payment History</a>
                    <form method="POST" action="/logout" class="inline">
                        @csrf
                        <button type="submit" class="hover:text-white whitespace-nowrap">Logout</button>
                    </form>
                @else
                    <a href="/login" class="hover:text-white whitespace-nowrap">Login</a>
                    <a href="/register" class="hover:text-white whitespace-nowrap">Register</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="p-4 sm:p-6">
        @yield('content')
    </main>

    <!-- @auth
    Top Up Modal
    <div id="topup-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-gray-900 border border-gray-700 rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Top Up Gold</h3>
                <button onclick="document.getElementById('topup-modal').classList.add('hidden')" class="text-gray-400 hover:text-white">
                    ✕
                </button>
            </div>

            @if(session('success'))
                <div class="bg-green-600 text-white px-4 py-2 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-600 text-white px-4 py-2 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p class="text-gray-400 mb-4">Current Balance: <span class="text-yellow-400 font-medium">{{ number_format(Auth::user()->gold ?? 0) }} gold coins</span></p>

            <form method="POST" action="{{ route('top-up') }}">
                @csrf
                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium mb-2">
                        Amount (1 - 10,000 gold coins)
                    </label>
                    <input 
                        type="number" 
                        id="amount"
                        name="amount" 
                        placeholder="Enter amount"
                        min="1"
                        max="10000"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                </div>

                <div class="flex gap-4">
                    <button 
                        type="submit" 
                        class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded font-medium transition-colors duration-200"
                    >
                        Add Gold
                    </button>
                    <button 
                        type="button"
                        onclick="document.getElementById('topup-modal').classList.add('hidden')" 
                        class="bg-gray-800 hover:bg-gray-700 px-4 py-2 rounded font-medium transition-colors duration-200"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endauth -->
</body>
</html>