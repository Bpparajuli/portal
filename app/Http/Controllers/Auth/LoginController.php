<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->is_admin) {
                return redirect()->intended('/admin/dashboard');
            } elseif ($user->is_agent) {
                return redirect()->intended('/agent/dashboard');
            }

            return redirect()->intended('/'); // fallback if no role
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Check if user exists by email
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        // Check if user is inactive
        if (!$user->active) {
            return back()->withErrors(['email' => 'Your account is not yet approved by admin.']);
        }

        // Attempt login
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'active' => 1,
        ])) {
            $request->session()->regenerate();

            if ($user->is_admin) {
                return redirect('/admin/dashboard');
            } elseif ($user->is_agent) {
                return redirect('/agent/dashboard');
            }
            return redirect('/');
        }
        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
    protected function authenticated($request, $user)
    {
        if ($user->is_admin) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->is_agent) {
            return redirect()->route('agent.dashboard');
        }

        return redirect()->route('guest.dashboard');
    }
}
