@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto mt-8">
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-3xl font-bold text-white">Post a New Quest</h2>
                <p class="text-gray-400 mt-1">Create a quest and find skilled adventurers to complete it</p>
            </div>
            <div class="text-right bg-gray-800 px-4 py-3 rounded-lg border border-gray-700">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Your Gold Balance</p>
                <p class="text-yellow-400 font-bold text-2xl mt-1">🪙 {{ number_format(Auth::user()->gold ?? 0) }}</p>
                @if(Auth::user()->gold < 100)
                    <p class="text-xs text-red-400 mt-1">Low balance warning</p>
                @endif
            </div>
        </div>
        <a href="{{ route('quests.index') }}" class="text-indigo-400 hover:text-indigo-300 text-sm inline-flex items-center">
            ← Back to Quest Board
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

    @if ($errors->any())
        <div class="bg-red-600 text-white px-4 py-3 rounded-lg mb-6">
            <p class="font-semibold mb-2">Please fix the following errors:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('quests.store') }}" class="space-y-6 bg-gray-800 rounded-lg p-6 border border-gray-700">
        @csrf

        <div>
            <label for="title" class="block text-sm font-semibold mb-2 text-gray-300">
                Quest Title <span class="text-red-400">*</span>
            </label>
            <input 
                type="text" 
                id="title"
                name="title" 
                placeholder="e.g., Build a responsive website for my business"
                value="{{ old('title') }}"
                maxlength="255"
                required
                class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            >
            <p class="text-xs text-gray-400 mt-1">Make it clear and descriptive to attract the right adventurers</p>
        </div>

        <div>
            <label for="description" class="block text-sm font-semibold mb-2 text-gray-300">
                Quest Details <span class="text-red-400">*</span>
            </label>
            <textarea 
                id="description"
                name="description" 
                placeholder="Describe what needs to be done, requirements, timeline, and any important details..."
                rows="8"
                required
                maxlength="5000"
                class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-vertical transition"
            >{{ old('description') }}</textarea>
            <div class="flex justify-between items-center mt-1">
                <p class="text-xs text-gray-400">Be detailed and specific to help adventurers understand your needs</p>
                <span id="charCount" class="text-xs text-gray-500">0/5000</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="price" class="block text-sm font-semibold mb-2 text-gray-300">
                    Gold Reward <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-yellow-400 text-lg">🪙</span>
                    </div>
                    <input 
                        type="number" 
                        id="price"
                        name="price" 
                        placeholder="Enter amount"
                        value="{{ old('price') }}"
                        min="1"
                        step="1"
                        required
                        class="w-full bg-gray-900 border border-gray-700 rounded-lg pl-12 pr-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        oninput="updateGoldWarning(this.value)"
                    >
                </div>
                <div id="goldWarning" class="hidden mt-1">
                    <p class="text-xs text-red-400">⚠️ You don't have enough gold. Current balance: {{ number_format(Auth::user()->gold) }}</p>
                </div>
                <p class="text-xs text-gray-400 mt-1">Set a fair reward to attract skilled adventurers</p>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2 text-gray-300">
                    Estimated Cost
                </label>
                <div class="bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-gray-400">
                    <span id="estimatedCost">0</span> gold coins will be deducted from your balance
                </div>
            </div>
        </div>

        <div class="border-t border-gray-700 pt-6">
            <label class="block text-sm font-semibold mb-3 text-gray-300">
                Categories <span class="text-gray-400 font-normal">(Select relevant categories)</span>
            </label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @forelse($categories as $category)
                    <label class="flex items-center p-3 bg-gray-900 border border-gray-700 rounded-lg cursor-pointer hover:border-indigo-500 transition">
                        <input type="checkbox" name="categories[]" value="{{ $category->id }}" 
                               {{ in_array($category->id, old('categories', [])) ? 'checked' : '' }}
                               class="mr-3 bg-gray-900 border-gray-700 rounded text-indigo-600 focus:ring-indigo-500">
                        <span class="text-gray-300 text-sm">{{ $category->name }}</span>
                    </label>
                @empty
                    <p class="text-gray-400 text-sm col-span-full">No categories available</p>
                @endforelse
            </div>
        </div>

        <div class="border-t border-gray-700 pt-6">
            <label class="block text-sm font-semibold mb-3 text-gray-300">
                Tags <span class="text-gray-400 font-normal">(Help adventurers find your quest)</span>
            </label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @forelse($tags as $tag)
                    <label class="flex items-center p-2 bg-gray-900 border border-gray-700 rounded-lg cursor-pointer hover:border-indigo-500 transition">
                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}" 
                               {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
                               class="mr-2 bg-gray-900 border-gray-700 rounded text-indigo-600 focus:ring-indigo-500">
                        <span class="text-gray-300 text-xs">#{{ $tag->name }}</span>
                    </label>
                @empty
                    <p class="text-gray-400 text-sm col-span-full">No tags available</p>
                @endforelse
            </div>
        </div>

        <div class="flex gap-4 pt-4 border-t border-gray-700">
            <button 
                type="submit" 
                id="submitBtn"
                class="flex-1 bg-indigo-600 hover:bg-indigo-500 px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Post Quest
            </button>
            <a 
                href="{{ route('quests.index') }}" 
                class="px-6 py-3 bg-gray-700 hover:bg-gray-600 rounded-lg font-medium transition-colors duration-200"
            >
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
const userGold = {{ Auth::user()->gold }};
const descriptionTextarea = document.getElementById('description');
const charCount = document.getElementById('charCount');
const priceInput = document.getElementById('price');
const estimatedCost = document.getElementById('estimatedCost');
const goldWarning = document.getElementById('goldWarning');
const submitBtn = document.getElementById('submitBtn');

// Character counter
descriptionTextarea.addEventListener('input', function() {
    const length = this.value.length;
    charCount.textContent = length + '/5000';
    charCount.classList.toggle('text-red-400', length > 4500);
});

// Gold warning
function updateGoldWarning(value) {
    const price = parseInt(value) || 0;
    estimatedCost.textContent = price;
    
    if (price > userGold) {
        goldWarning.classList.remove('hidden');
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
        goldWarning.classList.add('hidden');
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

// Initial check
if (priceInput.value) {
    updateGoldWarning(priceInput.value);
}
</script>
@endsection
