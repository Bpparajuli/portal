<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function list()
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403);
        }

        $admins = User::where('is_admin', 1)->get();
        $agents = User::where('is_agent', 1)->get();

        return view('user.list', compact('admins', 'agents'));
    }
    public function profile($id)
    {
        $user = User::findOrFail($id);

        if (!auth()->user()->is_admin && auth()->id() != $user->id) {
            abort(403, 'Unauthorized');
        }

        // $students = Student::where('user_id', $user->id)->get();
        $notifications = $user->notifications()->latest()->get();

        return view('user.profile', compact('user',  'notifications'));
    }
    public function create()
    {
        return view('user.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_name' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'contact' => 'required',
            'address' => 'nullable',
            'role' => 'required|in:admin,agent',
            'status' => 'required|in:Active,Inactive',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = new User();
        $user->business_name = $request->business_name;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->contact = $request->contact;
        $user->address = $request->address;
        $user->is_admin = $request->role === 'admin';
        $user->is_agent = $request->role === 'agent';
        $user->active = $request->status === 'Active';
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('user.list')->with('success', 'User added.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('user.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'business_name' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'contact' => 'required',
            'address' => 'nullable',
            'is_admin' => 'nullable|in:0,1',
            'is_agent' => 'nullable|in:0,1',
            'active' => 'nullable|in:0,1',
            'password' => 'nullable|min:6|confirmed',
            'business_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

        ]);

        $user->business_name = $request->business_name;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->contact = $request->contact;
        $user->address = $request->address;
        $user->is_admin = $request->role === 'admin';
        $user->is_agent = $request->role === 'agent';
        $user->active = $request->status === 'Active';
        // Handle logo upload
        if ($request->hasFile('business_logo')) {
            $extension = $request->file('business_logo')->getClientOriginalExtension();
            $safeBusinessName = str_replace(' ', '_', strtolower($request->business_name ?? 'agent'));
            $logoName = $safeBusinessName . '.' . $extension;

            $destinationPath = public_path('images/Agents_logo');

            // Delete old logo if it exists
            if ($user->business_logo && file_exists($destinationPath . '/' . $user->business_logo)) {
                unlink($destinationPath . '/' . $user->business_logo);
            }

            // Move new logo to folder
            $request->file('business_logo')->move($destinationPath, $logoName);
            $user->business_logo = $logoName;
        }
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('user.list')->with('success', 'User updated Successfully.');
    }

    public function destroy($id)
    {
        if (Auth::id() !== 1) {
            return back()->with('error', 'Only root admin can delete.');
        }

        $user = User::findOrFail($id);

        if ($user->id == 1) {
            return back()->with('error', 'Cannot delete root admin.');
        }

        $user->delete();
        return redirect()->route('user.list')->with('success', 'User deleted successfully.');
    }

    public function waitingList()
    {
        $users = User::where('active', 0)->get();
        return view('user.waiting', compact('users'));
    }

    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->active = true;
        $user->save();

        return back()->with('success', 'User approved.');
    }
}
