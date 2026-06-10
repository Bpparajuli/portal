<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Agent\DashboardController as AgentDashboardController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match (true) {
            in_array($user->role, ['admin', 'superadmin', 'admin_staff'])
                => app(AdminDashboardController::class)->index($request),
            in_array($user->role, ['agent', 'agent_staff'])
                => app(AgentDashboardController::class)->index($request),
            $user->role === 'staff' => $user->paid_crm
                ? redirect()->route('crm.dashboard')
                : app(StaffDashboardController::class)->index($request),
            default => redirect()->route('home'),
        };
    }
}
