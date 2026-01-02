@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-16">
    <h2 class="text-2xl font-bold mb-4">Join the Guild</h2>

    @if ($errors->any())
        <div class="bg-red-600 text-white px-4 py-2 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="space-y-4" method="POST" action="/register">
    @csrf
        <div>
            <input type="text" placeholder="Name" name="name" value="{{ old('name') }}"
                class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2 @error('name') border-red-500 @enderror">
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <input type="email" placeholder="Email" name="email" value="{{ old('email') }}"
                class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2 @error('email') border-red-500 @enderror">
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <select class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2 @error('role') border-red-500 @enderror" name="role">
                <option value="">Select Role</option>
                <option value="quest_giver" {{ old('role') == 'quest_giver' ? 'selected' : '' }}>Patron</option>
                <option value="adventurer" {{ old('role') == 'adventurer' ? 'selected' : '' }}>Adventurer</option>
            </select>
            @error('role')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <input type="password" placeholder="Password (min. 8 characters)" name="password"
                class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2 @error('password') border-red-500 @enderror">
            @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 rounded py-2">
            Register
        </button>
    </form>
</div>
@endsection