<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;
        
        if ($role === 'quest_giver' && $userRole !== \App\Models\User::ROLE_QUEST_GIVER) {
            abort(403, 'Only patrons can access this page.');
        }

        if ($role === 'adventurer' && $userRole !== \App\Models\User::ROLE_ADVENTURER) {
            abort(403, 'Only adventurers can access this page.');
        }

        return $next($request);
    }
}

