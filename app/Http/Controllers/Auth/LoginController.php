<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'active' => 1
        ])) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials or account not active.'],
            ]);
        }

        $request->session()->regenerate();

        // Call the authenticated method manually after successful login
        $user = Auth::user();
        $this->authenticated($request, $user);

        return $this->redirectToDashboard($user);
    }

    /**
     * Centralized redirect logic
     */
    protected function redirectToDashboard($user)
    {
        if ($user->is_admin) {
            return redirect()->intended('/admin/dashboard');
        }

        if ($user->is_staff) {
            return redirect()->route('crm.dashboard', ['assignee_id' => $user->id]);
        }

        if ($user->is_agent) {
            return redirect()->intended('/agent/dashboard');
        }

        // fallback
        return redirect('/');
    }

    public function logout(Request $request)
    {
        // Set user as offline before logout
        $user = Auth::user();
        if ($user && $user->status) {
            $user->status->update([
                'is_online' => false,
                'last_seen' => now()
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Handle post-login actions (update last login and online status)
     */
    protected function authenticated(Request $request, $user)
    {
        // Get the real IP address
        $ipAddress = $request->ip();

        // If behind a proxy, get the real IP
        if ($request->server->has('HTTP_X_FORWARDED_FOR')) {
            $ipAddress = $request->server->get('HTTP_X_FORWARDED_FOR');
        }

        // Update or create status record
        $status = $user->status()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'is_online' => true,
                'last_seen' => now(),
                'last_login_at' => now(),
                'last_login_ip' => $ipAddress
            ]
        );

        // Update existing status
        $status->is_online = true;
        $status->last_seen = now();
        $status->last_login_at = now();
        $status->last_login_ip = $ipAddress;
        $status->save();
    }
}
