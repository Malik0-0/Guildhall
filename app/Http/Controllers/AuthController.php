<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s\-_\.]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:' . User::ROLE_QUEST_GIVER . ',' . User::ROLE_ADVENTURER],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'], // Model will hash it automatically
            'role' => $validated['role'],
            'gold' => 100, // Default starting gold
            'xp' => 0,
            'level' => 1,
        ]);

        Auth::login($user);

        return $this->redirectByRole($user->role);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            return $this->redirectByRole($user->role);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Redirect to top-up page instead of direct gold addition.
     */
    public function topUp(Request $request)
    {
        return redirect('/top-up');
    }

    private function redirectByRole($role)
    {
        return match ($role) {
            User::ROLE_QUEST_GIVER => redirect('/quests'),
            User::ROLE_ADVENTURER => redirect('/quests'),
            default => redirect('/quests'),
        };
    }
}
