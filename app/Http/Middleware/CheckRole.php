<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        try {
            $required = UserRole::from($role);
        } catch (\ValueError $e) {
            abort(403);
        }

        if ($user->role !== $required) {
            abort(403);
        }

        return $next($request);
    }
}
