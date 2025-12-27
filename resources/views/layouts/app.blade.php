<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen">
    <header class="border-b border-gray-800 px-6 py-4 flex justify-between items-center">
        <div class="font-bold text-lg">
        <a href="/" class="font-bold text-lg">Guildhall</a>
        </div>

        <nav class="space-x-4 text-sm text-gray-300">
            <a href="/quests" class="hover:text-white">Quest Board</a>
            <a href="/login" class="hover:text-white">Login</a>
            <a href="/register" class="hover:text-white">Register</a>
        </nav>
    </header>

    <main class="p-6">
        @yield('content')
    </main>
</body>
</html>