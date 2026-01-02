@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto mt-8">
    <h1 class="text-3xl font-bold text-white mb-6">Edit Skill</h1>

    <form method="POST" action="{{ route('skills.update', $skill->id) }}">
        @csrf

        <div class="bg-gray-800 rounded-lg p-6">
            <!-- Skill Name -->
            <div class="mb-6">
                <label for="name" class="block text-gray-300 mb-2">Skill Name</label>
                <input type="text" id="name" name="name" maxlength="50" required
                       value="{{ old('name', $skill->name) }}"
                       class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       placeholder="e.g., Web Development, Graphic Design, Writing">
                <p class="text-xs text-gray-500 mt-1">Maximum 50 characters</p>
            </div>

            <!-- Skill Level -->
            <div class="mb-6">
                <label class="block text-gray-300 mb-2">Skill Level</label>
                <div class="space-y-3">
                    @for($level = 1; $level <= 5; $level++)
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="radio" name="level" value="{{ $level }}" 
                                   @if(old('level', $skill->level) == $level) checked @endif
                                   class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 focus:ring-indigo-500">
                            <div class="flex items-center space-x-2">
                                <span class="text-yellow-400">{{ str_repeat('●', $level) }}{{ str_repeat('○', 5 - $level) }}</span>
                                <span class="text-white">
                                    Level {{ $level }} - 
                                    {{ $level === 5 ? 'Expert' : 
                                       $level === 4 ? 'Advanced' : 
                                       $level === 3 ? 'Intermediate' : 
                                       $level === 2 ? 'Beginner' : 'Novice' }}
                                </span>
                            </div>
                        </label>
                    @endfor
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-between">
                <a href="{{ route('skills.index') }}" 
                   class="text-gray-400 hover:text-gray-300">
                    Cancel
                </a>
                <div class="space-x-3">
                    <form method="POST" action="{{ route('skills.destroy', $skill->id) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-600 hover:bg-red-500 px-4 py-2 rounded text-white font-semibold"
                                onclick="return confirm('Are you sure you want to delete this skill?')">
                            Delete Skill
                        </button>
                    </form>
                    <button type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-500 px-6 py-2 rounded text-white font-semibold">
                        Update Skill
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
