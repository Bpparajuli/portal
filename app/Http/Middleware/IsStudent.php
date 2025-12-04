<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsStudent
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->is_student) {
            abort(403, 'Unauthorized. Student only.');
        }

        return $next($request);
    }
}
