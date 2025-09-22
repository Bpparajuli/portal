<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Notifications\UserApproved;
use App\Models\Application;

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
        if (!Auth::user()->is_admin && Auth::id() !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $notifications = $user->notifications()->latest()->get();

        // Relationships
        $students = $user->students()->withCount('applications')->get();
        $applications = Application::whereIn('student_id', $students->pluck('id'))
            ->with(['student', 'course.university'])
            ->get();

        // Stats
        $user->students_count = $students->count();
        $user->applications_count = $applications->count();
        $user->pending_applications = $applications->where('status', 'pending')->count();

        return view('admin.users.show', compact('user', 'notifications', 'students', 'applications'));
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
            'business_name' => 'required|string|max:255',
            'owner_name'    => 'nullable|string|max:255',
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users',
            'contact'       => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:255',
            'role'          => 'required|in:admin,agent',
            'status'        => 'required|in:0,1',
            'password'      => 'required|string|min:6|confirmed',
            'business_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $user = new User();
        $this->assignUserFields($user, $request);
        $user->password = Hash::make($request->password);
        $this->handleBusinessLogoUpload($request, $user);
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User added successfully.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name'    => 'nullable|string|max:255',
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email,' . $user->id,
            'contact'       => 'nullable|string|max:20',
            'address'       => 'nullable|string|max:255',
            'role'          => 'required|in:admin,agent',
            'status'        => 'required|in:0,1',
            'password'      => 'nullable|string|min:6|confirmed',
            'business_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
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
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if (Auth::id() !== 1) {
            return back()->with('error', 'Only root admin can delete users.');
        }

        if ($user->id == 1) {
            return back()->with('error', 'Cannot delete root admin.');
        }

        // Delete logo from storage
        if ($user->business_logo && Storage::disk('public')->exists($user->business_logo)) {
            Storage::disk('public')->delete($user->business_logo);
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
        $totalWaitingUsers = User::where('active', 0)->count();

        return view('admin.users.waiting', compact('users', 'totalWaitingUsers'));
    }

    /**
     * Approve a user by activating their account.
     */
    public function approve(User $user)
    {
        $user->active = true;
        $user->save();

        $user->notify(new UserApproved());

        return back()->with('success', $user->business_name . ' has been approved and notified.');
    }

    /**
     * Assign common fields to a User model.
     */
    private function assignUserFields(User $user, Request $request)
    {
        $user->business_name = $request->business_name;
        $user->owner_name    = $request->owner_name;
        $user->name          = $request->name;
        $user->email         = $request->email;
        $user->contact       = $request->contact;
        $user->address       = $request->address;
        $user->is_admin      = $request->role === 'admin';
        $user->is_agent      = $request->role === 'agent';
        $user->active        = (int) $request->status;
    }

    /**
     * Handle business logo upload and replacement.
     */
    private function handleBusinessLogoUpload(Request $request, User $user)
    {
        if ($request->hasFile('business_logo')) {
            $safeBusinessName = str_replace(' ', '_', strtolower($request->business_name ?? 'agent'));
            $folderPath = 'agents/' . $safeBusinessName;
            $logoName = $safeBusinessName . '_logo.png';

            // Delete old logo if exists
            if ($user->business_logo && Storage::disk('public')->exists($user->business_logo)) {
                Storage::disk('public')->delete($user->business_logo);
            }

            // Store new logo in storage/app/public/agents/{business_name}/
            $path = $request->file('business_logo')->storeAs($folderPath, $logoName, 'public');

            // Save relative path (so we can retrieve with Storage::url)
            $user->business_logo = $path;
        }
    }
}
