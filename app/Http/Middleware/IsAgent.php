<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAgent
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('auth.login');
        }

        $user = Auth::user();

        if ($user->role !== 'agent' && !$user->is_admin) {
            abort(403, 'Unauthorized. Agent access required.');
        }

        if ($user->role === 'agent' && $user->agreement_status !== 'verified') {
            return redirect()->route('auth.waiting-dash')
                ->with('warning', 'Your account is pending verification.');
        }

        return $next($request);
    }
}
