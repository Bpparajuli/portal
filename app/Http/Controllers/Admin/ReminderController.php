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
    /**
     * Get email preview
     */
    public function previewEmail(Request $request)
    {
        try {
            $userIds = $request->input('user_ids', []);
            $isBulk = $request->input('is_bulk', false);

            if (is_string($userIds)) {
                $userIds = [$userIds];
            }

            $userIds = array_map('intval', $userIds);

            // Get first user for preview
            $user = User::find($userIds[0]);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ]);
            }

            // Generate the same custom content as your notification
            $userName = $user->business_name ?? $user->username ?? $user->name;

            $customContent = '
        <div>
            <p>Dear <strong>' . e($userName) . '</strong>,</p>
            
            <p>This is a friendly reminder to submit your agreement document to complete your registration process.
            
            <p>Please log in to your account and upload the required agreement document as soon as possible.</p>
            
            <div style="margin: 25px 0; padding: 15px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                <strong style="color: #bd951a;">⚠️ Important:</strong>
                <p style="margin: 5px 0 0 0; color: #bd951a;">Your account will not be fully activated until the agreement is verified.</p>
            </div>
            
            <div style="margin-top: 25px; text-align: center;">
                <a href="' . route('login') . '"
                    style="display: inline-block; background-color: #1a0262; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    Login to Upload Agreement
                </a>
            </div>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <p>Best regards,<br>
                <strong>' . e(config('app.name')) . ' Team</strong>
            </p>
        </div>';

            // Use your existing layout
            $html = view('emails.layout', [
                'subject' => 'Agreement Upload Reminder',
                'customContent' => $customContent
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            Log::error('Email preview error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate email preview: ' . $e->getMessage()
            ]);
        }
    }
}
