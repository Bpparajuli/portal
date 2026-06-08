<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['superadmin', 'admin'])) {
            abort(403, 'Unauthorized. Admin access required.');
        }
        return $next($request);
    }
}
