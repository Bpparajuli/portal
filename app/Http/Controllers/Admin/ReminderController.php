<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AgreementReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReminderController extends Controller
{
    // Show waiting users page
    public function showAgreementReminders()
    {
        $waitingUsers = User::where('agreement_status', 'not_uploaded')
            ->whereNull('agreement_file')
            ->get();

        $agreementUsers = User::where('agreement_status', 'not_uploaded')
            ->get();

        return view('admin.users.waiting', compact('waitingUsers', 'agreementUsers'));
    }

    // Send reminders
    public function sendAgreementReminder(Request $request)
    {
        if (Auth::id() != 2) {
            abort(403, 'Unauthorized action.');
        }

        $sendAll = $request->input('send_all', false);
        $selectedUserIds = $request->input('user_ids', []);

        if ($sendAll) {
            $users = User::where('agreement_status', 'not_uploaded')
                ->whereNull('agreement_file')
                ->get();
        } elseif (!empty($selectedUserIds)) {
            $users = User::whereIn('id', $selectedUserIds)
                ->where('agreement_status', 'not_uploaded')
                ->whereNull('agreement_file')
                ->get();
        } else {
            return back()->with('info', 'No users selected.');
        }

        if ($users->isEmpty()) {
            return back()->with('info', 'No users found to send reminders.');
        }

        foreach ($users as $user) {
            $user->notify(new AgreementReminder($user));
        }

        Log::info('Agreement reminder emails sent', [
            'admin_id' => Auth::id(),
            'count' => $users->count(),
            'user_ids' => $users->pluck('id')->toArray()
        ]);

        return back()->with('success', $users->count() . ' reminder emails sent successfully.');
    }
}
