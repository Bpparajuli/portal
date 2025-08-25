<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsAgent
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check() || Auth::user()->is_agent != 1) {
            abort(403, 'Unauthorized. Agents only.');
        }
        return $next($request);
    }
}
