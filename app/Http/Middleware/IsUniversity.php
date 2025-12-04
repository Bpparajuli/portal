<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsUniversity
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->is_university) {
            abort(403, 'Unauthorized. University only.');
        }

        return $next($request);
    }
}
