@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto mt-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-white">My Skills</h1>
        <a href="{{ route('skills.create') }}" 
           class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded text-white">
            Add New Skill
        </a>
    </div>

    @if($skills->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($skills as $skill)
                <div class="bg-gray-800 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-white">{{ $skill->name }}</h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-yellow-400">{{ $skill->level_dots }}</span>
                            <span class="text-sm text-gray-400">Lv.{{ $skill->level }}</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-400">
                            {{ $skill->level === 5 ? 'Expert' : 
                               $skill->level === 4 ? 'Advanced' : 
                               $skill->level === 3 ? 'Intermediate' : 
                               $skill->level === 2 ? 'Beginner' : 'Novice' }}
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('skills.edit', $skill->id) }}" 
                               class="text-indigo-400 hover:text-indigo-300 text-sm">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('skills.destroy', $skill->id) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-red-400 hover:text-red-300 text-sm"
                                        onclick="return confirm('Are you sure you want to delete this skill?')">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-gray-800 rounded-lg p-12 text-center">
            <div class="text-gray-400 text-lg mb-4">No skills added yet</div>
            <p class="text-gray-500 mb-6">Add your skills to showcase your expertise to potential quest partners.</p>
            <a href="{{ route('skills.create') }}" 
               class="bg-indigo-600 hover:bg-indigo-500 px-6 py-2 rounded text-white">
                Add Your First Skill
            </a>
        </div>
    @endif
</div>
@endsection
