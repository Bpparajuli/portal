<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class UserController extends Controller
{
    /**
     * Display a listing of the users for the admin dashboard.
     */
    public function index()
    {
        $admins = User::where('is_admin', 1)->get();
        $agents = User::where('is_agent', 1)->get();

        return view('admin.users.index', compact('admins', 'agents'));
    }

    /**
     * Display the specified user's profile.
     */
    public function show(User $user)
    {
        // Only root admin or the user themselves can view
        if (!Auth::user()->is_admin && Auth::id() !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $notifications = $user->notifications()->latest()->get();

        return view('admin.users.show', compact('user', 'notifications'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'business_name' => 'required',
            'name'          => 'required',
            'email'         => 'required|email|unique:users',
            'contact'       => 'required',
            'address'       => 'nullable',
            'role'          => 'required|in:admin,agent',
            'status'        => 'required|in:Active,Inactive',
            'password'      => 'required|min:6|confirmed',
            'business_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = new User();
        $this->assignUserFields($user, $request);

        $user->password = Hash::make($request->password);
        $this->handleBusinessLogoUpload($request, $user);

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User added successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'business_name' => 'required',
            'name'          => 'required',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'contact'       => 'required',
            'address'       => 'nullable',
            'role'          => 'required|in:admin,agent',
            'status'        => 'required|in:Active,Inactive',
            'password'      => 'nullable|min:6|confirmed',
            'business_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $this->assignUserFields($user, $request);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $this->handleBusinessLogoUpload($request, $user);

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Only root admin (ID=1) can delete
        if (Auth::id() !== 1) {
            return back()->with('error', 'Only root admin can delete users.');
        }

        if ($user->id == 1) {
            return back()->with('error', 'Cannot delete root admin.');
        }

        // Delete logo file if exists
        if ($user->business_logo) {
            $logoPath = public_path('images/Agents_logo/' . $user->business_logo);
            if (File::exists($logoPath)) {
                File::delete($logoPath);
            }
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Show a list of waiting (inactive) users.
     */
    public function waiting()
    {
        $users = User::where('active', 0)->paginate(10);
        return view('admin.users.waiting', compact('users'));
    }

    /**
     * Approve a user by activating their account.
     */
    public function approve(User $user)
    {
        $user->active = true;
        $user->save();

        return back()->with('success', 'User approved successfully.');
    }

    /**
     * Assign common fields to a User model.
     */
    private function assignUserFields(User $user, Request $request)
    {
        $user->business_name = $request->business_name;
        $user->name          = $request->name;
        $user->email         = $request->email;
        $user->contact       = $request->contact;
        $user->address       = $request->address;
        $user->is_admin      = $request->role === 'admin';
        $user->is_agent      = $request->role === 'agent';
        $user->active        = $request->status === 'Active';
    }

    /**
     * Handle business logo upload and replacement.
     */
    private function handleBusinessLogoUpload(Request $request, User $user)
    {
        if ($request->hasFile('business_logo')) {
            $extension = $request->file('business_logo')->getClientOriginalExtension();
            $safeBusinessName = str_replace(' ', '_', strtolower($request->business_name ?? 'agent'));
            $logoName = $safeBusinessName . '.' . $extension;

            $destinationPath = public_path('images/Agents_logo');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            // Delete old logo if exists
            if ($user->business_logo && File::exists($destinationPath . '/' . $user->business_logo)) {
                File::delete($destinationPath . '/' . $user->business_logo);
            }

            $request->file('business_logo')->move($destinationPath, $logoName);
            $user->business_logo = $logoName;
        }
    }
}
