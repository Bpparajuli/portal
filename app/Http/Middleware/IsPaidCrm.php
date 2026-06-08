<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsPaidCrm
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('auth.login');
        }

        // Admin & superadmin always have access
        if ($user->is_admin) {
            return $next($request);
        }

        // Staff with paid_crm (via their agent's subscription)
        if ($user->is_staff) {
            $effectiveUser = $user->is_agent_staff ? $user->parent : $user;
            if ($effectiveUser && $effectiveUser->paid_crm) {
                return $next($request);
            }
            return redirect()->route('staff.dashboard')
                ->with('error', 'CRM access requires a paid subscription.');
        }

        // Agent with paid_crm
        if ($user->is_agent && $user->paid_crm) {
            return $next($request);
        }

        return redirect()->route('agent.dashboard')
            ->with('error', 'CRM access requires a paid subscription. Please upgrade your plan.');
    }
}
