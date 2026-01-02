@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-0">
    <h2 class="text-2xl font-bold mb-6">Complete Quest: {{ $quest->title }}</h2>

    @if(session('error'))
        <div class="bg-red-600 text-white px-4 py-2 rounded mb-4">
            {{ session('error') }}
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

    <div class="border border-gray-700 p-4 mb-6 rounded bg-gray-900">
        <h3 class="font-semibold mb-2">Quest Details</h3>
        <p class="text-gray-400 mb-2">{{ $quest->description }}</p>
        <p class="text-sm text-gray-400">Gold Reward: <span class="text-yellow-400 font-medium">{{ $quest->price }} gold coins</span></p>
        @if($quest->patron)
            <p class="text-sm text-gray-400 mt-1">Posted by: {{ $quest->patron->name }}</p>
        @endif
    </div>

    <form method="POST" action="/quests/{{ $quest->id }}/complete" class="space-y-6" enctype="multipart/form-data">
        @csrf

        <div>
            <label for="evidence" class="block text-sm font-medium mb-2">
                Completion Evidence <span class="text-red-400">*</span>
            </label>
            <textarea 
                id="evidence"
                name="evidence" 
                placeholder="Provide detailed evidence that you have completed this quest. Include descriptions, links, screenshots details, or any other relevant information..."
                rows="8"
                required
                class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-vertical"
            >{{ old('evidence') }}</textarea>
            <p class="text-xs text-gray-400 mt-1">
                Your evidence will be reviewed by the patron. Payment will be processed upon approval. 
                If the patron does not respond within 72 hours, the quest will be auto-approved.
            </p>
        </div>

        <div>
            <label for="evidence_files" class="block text-sm font-medium mb-2">
                Supporting Files (Optional)
            </label>
            <input 
                type="file" 
                id="evidence_files"
                name="evidence_files[]" 
                multiple
                accept="image/*,.pdf,.doc,.docx,.txt"
                class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500"
            >
            <p class="text-xs text-gray-400 mt-1">
                You can upload images, PDFs, or documents (max 10MB per file). Supported formats: JPG, PNG, PDF, DOC, DOCX, TXT
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 pt-2">
            <button 
                type="submit" 
                class="bg-green-600 hover:bg-green-500 px-6 py-2 rounded font-medium transition-colors duration-200 text-sm sm:text-base"
            >
                Submit Evidence for Review
            </button>
            <a 
                href="{{ route('quests.my-quests') }}" 
                class="bg-gray-800 hover:bg-gray-700 px-6 py-2 rounded font-medium transition-colors duration-200 text-center text-sm sm:text-base"
            >
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

