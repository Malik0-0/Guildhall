@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto mt-8">
    <h1 class="text-3xl font-bold text-white mb-6">Edit Profile</h1>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <!-- Profile Information -->
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-white mb-4">Profile Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Avatar -->
                <div class="md:col-span-2">
                    <label class="block text-gray-300 mb-2">Profile Avatar</label>
                    <div class="flex items-center space-x-6">
                        @if($profile->avatar)
                            <img src="{{ Storage::url($profile->avatar) }}" alt="Current avatar" 
                                 class="w-20 h-20 rounded-full object-cover border-2 border-gray-700">
                        @else
                            <div class="w-20 h-20 rounded-full bg-gray-700 flex items-center justify-center">
                                <span class="text-xl font-bold text-gray-400">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            </div>
                        @endif
                        <div>
                            <input type="file" name="avatar" id="avatar" 
                                   class="block w-full text-sm text-gray-400
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-full file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-indigo-600 file:text-white
                                          hover:file:bg-indigo-700
                                          file:cursor-pointer">
                            <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF up to 2MB</p>
                        </div>
                    </div>
                </div>

                <!-- Bio -->
                <div class="md:col-span-2">
                    <label for="bio" class="block text-gray-300 mb-2">Bio</label>
                    <textarea id="bio" name="bio" rows="4" maxlength="500"
                              class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('bio', $profile->bio) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Maximum 500 characters</p>
                </div>

                <!-- Location -->
                <div>
                    <label for="location" class="block text-gray-300 mb-2">Location</label>
                    <input type="text" id="location" name="location" maxlength="100"
                           value="{{ old('location', $profile->location) }}"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Website -->
                <div>
                    <label for="website" class="block text-gray-300 mb-2">Website</label>
                    <input type="url" id="website" name="website" maxlength="255"
                           value="{{ old('website', $profile->website) }}"
                           placeholder="https://example.com"
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!-- Skills -->
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white">Skills</h2>
                <button type="button" id="addSkill" 
                        class="bg-indigo-600 hover:bg-indigo-500 px-3 py-1 rounded text-white text-sm">
                    Add Skill
                </button>
            </div>
            
            <div id="skillsContainer" class="space-y-3">
                <!-- Existing Skills -->
                @if($skills->count() > 0)
                    @foreach($skills as $index => $skill)
                        <div class="skill-item flex items-center space-x-3">
                            <input type="text" name="skills[{{ $index }}][name]" 
                                   value="{{ $skill->name }}" maxlength="50"
                                   placeholder="Skill name"
                                   class="flex-1 px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <select name="skills[{{ $index }}][level]" 
                                    class="px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="1" {{ $skill->level == 1 ? 'selected' : '' }}>Level 1</option>
                                <option value="2" {{ $skill->level == 2 ? 'selected' : '' }}>Level 2</option>
                                <option value="3" {{ $skill->level == 3 ? 'selected' : '' }}>Level 3</option>
                                <option value="4" {{ $skill->level == 4 ? 'selected' : '' }}>Level 4</option>
                                <option value="5" {{ $skill->level == 5 ? 'selected' : '' }}>Level 5</option>
                            </select>
                            <button type="button" class="remove-skill text-red-400 hover:text-red-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                @endif
            </div>
            
            <p class="text-xs text-gray-500 mt-3">Add your skills with proficiency levels (1-5)</p>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-between">
            <a href="{{ route('profile.show') }}" 
               class="text-gray-400 hover:text-gray-300">
                Cancel
            </a>
            <button type="submit" 
                    class="bg-indigo-600 hover:bg-indigo-500 px-6 py-2 rounded text-white font-semibold">
                Save Changes
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let skillIndex = {{ $skills->count() }};
    
    // Add new skill
    document.getElementById('addSkill').addEventListener('click', function() {
        const container = document.getElementById('skillsContainer');
        const skillItem = document.createElement('div');
        skillItem.className = 'skill-item flex items-center space-x-3';
        skillItem.innerHTML = `
            <input type="text" name="skills[${skillIndex}][name]" 
                   maxlength="50" placeholder="Skill name"
                   class="flex-1 px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <select name="skills[${skillIndex}][level]" 
                    class="px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="1">Level 1</option>
                <option value="2">Level 2</option>
                <option value="3">Level 3</option>
                <option value="4">Level 4</option>
                <option value="5">Level 5</option>
            </select>
            <button type="button" class="remove-skill text-red-400 hover:text-red-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        container.appendChild(skillItem);
        skillIndex++;
    });
    
    // Remove skill
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-skill')) {
            e.target.closest('.skill-item').remove();
        }
    });
});
</script>
@endsection
