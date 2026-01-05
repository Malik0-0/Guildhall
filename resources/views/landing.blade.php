@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto mt-8 sm:mt-16 space-y-24 sm:space-y-32 px-4 sm:px-6">

    {{-- HERO SECTION --}}
    <section class="relative text-center space-y-8 pt-8 sm:pt-16">
        {{-- Animated background gradient --}}
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-to-r from-indigo-600/20 via-purple-600/20 to-pink-600/20 rounded-full blur-3xl animate-pulse"></div>
        </div>

        {{-- Main heading with RPG flair --}}
        <div class="space-y-4">
            <div class="inline-block">
                <span class="text-6xl sm:text-7xl md:text-8xl">🏰</span>
            </div>
            <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent leading-tight">
                Where Quests Meet Skills
            </h1>
            <p class="text-gray-300 text-lg sm:text-xl max-w-3xl mx-auto leading-relaxed">
                <span class="text-yellow-400 font-semibold">Guildhall</span> is a gamified service marketplace inspired by RPG guilds.
                Post quests, hire adventurers, and build reputation through completed missions.
            </p>
        </div>

        {{-- CTA Buttons with hover effects --}}
        <div class="flex flex-col sm:flex-row justify-center gap-4 mt-10">
            <a href="/register"
               class="group relative bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 px-8 py-4 rounded-lg font-bold text-lg shadow-lg shadow-indigo-500/50 hover:shadow-xl hover:shadow-indigo-500/50 transition-all duration-300 transform hover:scale-105">
                <span class="relative z-10 flex items-center justify-center gap-2">
                    ⚔️ Join the Guild
                </span>
                <div class="absolute inset-0 rounded-lg bg-gradient-to-r from-indigo-400 to-purple-400 opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
            </a>
            <a href="/quests"
               class="group border-2 border-indigo-500/50 hover:border-indigo-400 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-indigo-500/10 transition-all duration-300 transform hover:scale-105">
                <span class="flex items-center justify-center gap-2">
                    📜 View Quest Board
                </span>
            </a>
        </div>

        {{-- Quick stats --}}
        <div class="grid grid-cols-3 gap-4 sm:gap-8 max-w-2xl mx-auto pt-8">
            <div class="text-center">
                <div class="text-2xl sm:text-3xl font-bold text-yellow-400">🪙</div>
                <div class="text-sm sm:text-base text-gray-400 mt-2">Gold Economy</div>
            </div>
            <div class="text-center">
                <div class="text-2xl sm:text-3xl font-bold text-blue-400">⭐</div>
                <div class="text-sm sm:text-base text-gray-400 mt-2">XP & Levels</div>
            </div>
            <div class="text-center">
                <div class="text-2xl sm:text-3xl font-bold text-purple-400">🛡️</div>
                <div class="text-sm sm:text-base text-gray-400 mt-2">Trust Tiers</div>
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section class="relative">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-4">
                How <span class="text-indigo-400">Guildhall</span> Works
            </h2>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                Three simple steps to start your adventure
            </p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-6 sm:gap-8">
            <div class="group relative border border-gray-800 bg-gradient-to-br from-gray-900/50 to-gray-950/50 p-8 rounded-xl hover:border-indigo-500/50 transition-all duration-300 transform hover:scale-105 hover:shadow-lg hover:shadow-indigo-500/20">
                <div class="absolute top-4 right-4 text-4xl opacity-20 group-hover:opacity-40 transition-opacity">📋</div>
                <div class="relative z-10">
                    <div class="text-5xl mb-4">1️⃣</div>
                    <h3 class="text-xl font-bold mb-3 text-indigo-400">Post a Quest</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Patrons post service requests as quests with clear requirements and gold rewards.
                    </p>
                </div>
            </div>
            
            <div class="group relative border border-gray-800 bg-gradient-to-br from-gray-900/50 to-gray-950/50 p-8 rounded-xl hover:border-purple-500/50 transition-all duration-300 transform hover:scale-105 hover:shadow-lg hover:shadow-purple-500/20">
                <div class="absolute top-4 right-4 text-4xl opacity-20 group-hover:opacity-40 transition-opacity">⚔️</div>
                <div class="relative z-10">
                    <div class="text-5xl mb-4">2️⃣</div>
                    <h3 class="text-xl font-bold mb-3 text-purple-400">Adventurers Accept</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Skilled adventurers submit proposals and accept quests that match their abilities.
                    </p>
                </div>
            </div>
            
            <div class="group relative border border-gray-800 bg-gradient-to-br from-gray-900/50 to-gray-950/50 p-8 rounded-xl hover:border-pink-500/50 transition-all duration-300 transform hover:scale-105 hover:shadow-lg hover:shadow-pink-500/20">
                <div class="absolute top-4 right-4 text-4xl opacity-20 group-hover:opacity-40 transition-opacity">🏆</div>
                <div class="relative z-10">
                    <div class="text-5xl mb-4">3️⃣</div>
                    <h3 class="text-xl font-bold mb-3 text-pink-400">Complete & Level Up</h3>
                    <p class="text-gray-400 leading-relaxed">
                        Completed quests grant XP, levels, gold, and automatically increase your trust tier.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ROLES SECTION --}}
    <section class="relative">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-4">
                Choose Your <span class="text-yellow-400">Path</span>
            </h2>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                Whether you're seeking services or offering them, there's a role for you
            </p>
        </div>
        
        <div class="grid md:grid-cols-2 gap-6 sm:gap-8">
            {{-- Patron Card --}}
            <div class="group relative border-2 border-blue-500/30 bg-gradient-to-br from-blue-950/30 to-indigo-950/30 p-8 rounded-xl hover:border-blue-400/50 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/20">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl"></div>
                <div class="relative z-10">
                    <div class="text-6xl mb-4">👑</div>
                    <h3 class="text-2xl font-bold mb-3 text-blue-400">Patron</h3>
                    <p class="text-gray-300 mb-4 leading-relaxed">
                        Hire skilled & trusted adventurers to complete your quests efficiently. Post your service needs and watch skilled adventurers compete for your quest.
                    </p>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li class="flex items-center gap-2">
                            <span class="text-green-400">✓</span> Post unlimited quests
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-green-400">✓</span> Review proposals from adventurers
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-green-400">✓</span> Secure payment system
                        </li>
                    </ul>
                </div>
            </div>
            
            {{-- Adventurer Card --}}
            <div class="group relative border-2 border-purple-500/30 bg-gradient-to-br from-purple-950/30 to-pink-950/30 p-8 rounded-xl hover:border-purple-400/50 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-purple-500/20">
                <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500/10 rounded-full blur-3xl"></div>
                <div class="relative z-10">
                    <div class="text-6xl mb-4">⚔️</div>
                    <h3 class="text-2xl font-bold mb-3 text-purple-400">Adventurer</h3>
                    <p class="text-gray-300 mb-4 leading-relaxed">
                        Complete quests, earn gold, and build your reputation. Level up your skills and climb the trust tiers through successful quest completions.
                    </p>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li class="flex items-center gap-2">
                            <span class="text-green-400">✓</span> Earn gold & XP from quests
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-green-400">✓</span> Build reputation & trust tiers
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="text-green-400">✓</span> Showcase your skills
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- GAMIFICATION FEATURES --}}
    <section class="relative">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-4">
                Progress Like an <span class="text-green-400">RPG</span>
            </h2>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                Experience a unique gamified marketplace with automatic progression
            </p>
        </div>
        
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- XP System --}}
            <div class="border border-gray-800 bg-gradient-to-br from-gray-900/50 to-gray-950/50 p-6 rounded-xl hover:border-yellow-500/50 transition-all duration-300 text-center">
                <div class="text-5xl mb-4">⭐</div>
                <h3 class="text-lg font-bold mb-2 text-yellow-400">Experience Points</h3>
                <p class="text-gray-400 text-sm">
                    Earn XP with every completed quest
                </p>
            </div>
            
            {{-- Leveling --}}
            <div class="border border-gray-800 bg-gradient-to-br from-gray-900/50 to-gray-950/50 p-6 rounded-xl hover:border-blue-500/50 transition-all duration-300 text-center">
                <div class="text-5xl mb-4">📈</div>
                <h3 class="text-lg font-bold mb-2 text-blue-400">Level System</h3>
                <p class="text-gray-400 text-sm">
                    Level up automatically as you progress
                </p>
            </div>
            
            {{-- Trust Tiers --}}
            <div class="border border-gray-800 bg-gradient-to-br from-gray-900/50 to-gray-950/50 p-6 rounded-xl hover:border-purple-500/50 transition-all duration-300 text-center">
                <div class="text-5xl mb-4">🛡️</div>
                <h3 class="text-lg font-bold mb-2 text-purple-400">Trust Tiers</h3>
                <p class="text-gray-400 text-sm">
                    Bronze → Silver → Gold → Platinum → Diamond
                </p>
            </div>
            
            {{-- Gold Economy --}}
            <div class="border border-gray-800 bg-gradient-to-br from-gray-900/50 to-gray-950/50 p-6 rounded-xl hover:border-green-500/50 transition-all duration-300 text-center">
                <div class="text-5xl mb-4">🪙</div>
                <h3 class="text-lg font-bold mb-2 text-green-400">Gold Currency</h3>
                <p class="text-gray-400 text-sm">
                    Real monetary value in virtual gold
                </p>
            </div>
        </div>
        
        <div class="mt-12 text-center">
            <p class="text-gray-300 text-lg max-w-3xl mx-auto leading-relaxed">
                <span class="text-indigo-400 font-semibold">No manual ratings, no complex reviews.</span>
                Your reputation grows automatically through your quest completion history. 
                The more quests you complete successfully, the higher you climb.
            </p>
        </div>
    </section>

    {{-- FINAL CTA --}}
    <section class="relative py-16 sm:py-20">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600/10 via-purple-600/10 to-pink-600/10 rounded-3xl"></div>
        <div class="relative z-10 text-center space-y-6">
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold">
                Ready to Start Your <span class="text-yellow-400">Adventure</span>?
            </h2>
            <p class="text-gray-300 text-lg sm:text-xl max-w-2xl mx-auto">
                Join thousands of adventurers and patrons building their reputation in the Guildhall
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 mt-8">
                <a href="/register"
                   class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 px-8 py-4 rounded-lg font-bold text-lg shadow-lg shadow-indigo-500/50 hover:shadow-xl hover:shadow-indigo-500/50 transition-all duration-300 transform hover:scale-105">
                    🚀 Join the Guild Now
                </a>
                <a href="/quests"
                   class="border-2 border-indigo-500/50 hover:border-indigo-400 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-indigo-500/10 transition-all duration-300 transform hover:scale-105">
                    Explore Quest Board
                </a>
            </div>
        </div>
    </section>

</div>
@endsection