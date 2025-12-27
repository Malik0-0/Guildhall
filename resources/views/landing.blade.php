@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-16 space-y-32">

    {{-- HERO --}}
    <section class="text-center space-y-6">
        <h1 class="text-5xl font-extrabold">
            Where Quests Meet Skills
        </h1>
        <p class="text-gray-400 text-lg max-w-2xl mx-auto">
            Guildhall is a gamified service marketplace inspired by RPG guilds.
            Post quests, hire adventurers, and build reputation through completed missions.
        </p>

        <div class="flex justify-center gap-4 mt-8">
            <a href="/register"
               class="bg-indigo-600 hover:bg-indigo-500 px-6 py-3 rounded font-semibold">
                Join the Guild
            </a>
            <a href="/quests"
               class="border border-gray-700 px-6 py-3 rounded hover:bg-gray-900">
                View Quest Board
            </a>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section>
        <h2 class="text-3xl font-bold text-center mb-12">How Guildhall Works</h2>
        <div class="grid md:grid-cols-3 gap-8 text-center">
            <div class="border border-gray-800 p-6 rounded-lg">
                <h3 class="font-semibold mb-2">1. Post a Quest</h3>
                <p class="text-gray-400">
                    Patrons post service requests as quests.
                </p>
            </div>
            <div class="border border-gray-800 p-6 rounded-lg">
                <h3 class="font-semibold mb-2">2. Adventurers Accept</h3>
                <p class="text-gray-400">
                    Skilled adventurers accept quests that match their abilities.
                </p>
            </div>
            <div class="border border-gray-800 p-6 rounded-lg">
                <h3 class="font-semibold mb-2">3. Complete & Level Up</h3>
                <p class="text-gray-400">
                    Completed quests grant XP, levels, and trust.
                </p>
            </div>
        </div>
    </section>

    {{-- ROLES --}}
    <section>
        <h2 class="text-3xl font-bold text-center mb-12">Choose Your Path</h2>
        <div class="grid md:grid-cols-2 gap-8">
            <div class="border border-gray-800 p-6 rounded-lg">
                <h3 class="text-xl font-semibold mb-2">Patron</h3>
                <p class="text-gray-400">
                    Hire skilled & trusted adventurers to complete your quests efficiently.
                </p>
            </div>
            <div class="border border-gray-800 p-6 rounded-lg">
                <h3 class="text-xl font-semibold mb-2">Adventurer</h3>
                <p class="text-gray-400">
                    Complete quests, earn gold, and build your reputation.
                </p>
            </div>
        </div>
    </section>

    {{-- GAMIFICATION --}}
    <section class="text-center">
        <h2 class="text-3xl font-bold mb-4">Progress Like an RPG</h2>
        <p class="text-gray-400 max-w-2xl mx-auto">
            Guildhall features XP, levels, and trust tiers that automatically
            increase as you complete quests — no ratings, no reviews, just progress.
        </p>
    </section>

</div>
@endsection