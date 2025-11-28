<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsAgent
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // Not logged in or not an agent
        if (!$user || $user->is_agent != 1) {
            abort(403, 'Unauthorized. Agents only.');
        }

        // Check agreement status
        if ($user->agreement_status !== 'verified') {
            // Redirect unverified agents to waiting page
            return redirect()->route('auth.waiting-dash');
        }

        return $next($request);
    }
}
