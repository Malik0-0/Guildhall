@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-16">
    <h2 class="text-2xl font-bold mb-4">Enter the Guild</h2>

    <form class="space-y-4">
        <input type="email" placeholder="Email"
            class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2">

        <input type="password" placeholder="Password"
            class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2">

        <button class="w-full bg-indigo-600 hover:bg-indigo-500 rounded py-2">
            Login
        </button>
    </form>
</div>
@endsection