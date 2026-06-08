<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AgreementSubmitted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Contracts\FileUploadServiceInterface;

class WaitingController extends Controller
{
    public function __construct(
        private readonly FileUploadServiceInterface $fileUploadService,
    ) {}

    public function show()
    {
        $user = Auth::user();

        if ($user->agreement_status === 'verified') {
            return redirect()->route('agent.dashboard')->with('info', 'Your account is already verified.');
        }

        if (!$user->is_agent) {
            if ($user->is_admin) {
                return redirect()->route('admin.dashboard');
            }
            return redirect('/')->with('error', 'Unauthorized access.');
        }

        return view('auth.waiting-dash', compact('user'));
    }

    /**
     * Upload agreement using FileUploadService
     */
    public function upload(Request $request)
    {
        $user = Auth::user();

        // Quick validation
        if (!$user->is_agent) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        if ($user->agreement_status === 'verified') {
            return redirect()->route('agent.dashboard')->with('error', 'Your account is already verified.');
        }

        // Validate file
        $validator = validator($request->all(), [
            'agreement_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', $validator->errors()->first('agreement_file'))
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Store old file path for cleanup (if needed)
            $oldFile = $user->agreement_file;

            // Use FileUploadService - this will store at: agents/{user->slug}/agreement.{ext}
            $uploadedPath = $this->fileUploadService->uploadAgentFile($request, $user, 'agreement_file', 'agreement');

            if (!$uploadedPath) {
                throw new \Exception('File upload failed');
            }

            // Update user record
            $user->agreement_file = $uploadedPath;
            $user->agreement_status = 'uploaded';
            $user->agreement_uploaded_at = now();
            $user->save();

            DB::commit();

            // Send notifications in background
            $this->notifyAdminsAsync($user);

            Log::info("Agreement uploaded successfully", [
                'user_id' => $user->id,
                'user_slug' => $user->slug,
                'file_path' => $uploadedPath
            ]);

            return redirect()->route('auth.waiting-dash')
                ->with('success', '✓ Agreement uploaded successfully! Your document is under review.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Upload failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Upload failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Async notifications - doesn't block the response
     */
    private function notifyAdminsAsync($user)
    {
        try {
            // Get admin users
            $admins = User::admins()->get(['id', 'email', 'name']);

            if ($admins->isNotEmpty()) {
                foreach ($admins as $admin) {
                    try {
                        Notification::send($admin, new AgreementSubmitted($user));
                    } catch (\Exception $e) {
                        Log::warning("Admin notification failed for admin ID: {$admin->id}");
                    }
                }
            } else {
                // Fallback: try to find any admin
                $defaultAdmin = User::admins()->first();
                if ($defaultAdmin) {
                    Notification::send($defaultAdmin, new AgreementSubmitted($user));
                }
            }
        } catch (\Exception $e) {
            Log::warning('Notification system error: ' . $e->getMessage());
            // Don't rethrow - we don't want to fail the upload
        }
    }

    /**
     * Re-upload agreement (same as upload but allows overwriting existing file)
     */
    public function reupload(Request $request)
    {
        $user = Auth::user();

        if (!$user->is_agent) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        if ($user->agreement_status === 'verified') {
            return redirect()->route('agent.dashboard')->with('error', 'Your account is already verified.');
        }

        $validator = validator($request->all(), [
            'agreement_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', $validator->errors()->first('agreement_file'))
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Delete old file if exists
            if ($user->agreement_file && Storage::disk('public')->exists($user->agreement_file)) {
                Storage::disk('public')->delete($user->agreement_file);
            }

            $uploadedPath = $this->fileUploadService->uploadAgentFile($request, $user, 'agreement_file', 'agreement');

            if (!$uploadedPath) {
                throw new \Exception('File upload failed');
            }

            $user->agreement_file = $uploadedPath;
            $user->agreement_status = 'uploaded';
            $user->agreement_uploaded_at = now();
            $user->save();

            DB::commit();

            $this->notifyAdminsAsync($user);

            return redirect()->route('auth.waiting-dash')
                ->with('success', 'Agreement re-uploaded successfully! Your document is under review.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Re-upload failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Optional: Method to view/download the agreement
     */
    public function viewAgreement()
    {
        $user = Auth::user();

        if (!$user->agreement_file || !Storage::disk('public')->exists($user->agreement_file)) {
            return redirect()->back()->with('error', 'Agreement file not found.');
        }

        return response()->file(Storage::disk('public')->path($user->agreement_file));
    }

    /**
     * Optional: Method to delete agreement (if user wants to re-upload)
     */
    public function deleteAgreement()
    {
        $user = Auth::user();

        if (!$user->is_agent) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        try {
            DB::beginTransaction();

            // Delete file if exists
            if ($user->agreement_file && Storage::disk('public')->exists($user->agreement_file)) {
                Storage::disk('public')->delete($user->agreement_file);
            }

            // Reset user agreement fields
            $user->agreement_file = null;
            $user->agreement_status = 'not_uploaded';
            $user->agreement_uploaded_at = null;
            $user->save();

            DB::commit();

            return redirect()->route('auth.waiting-dash')
                ->with('success', 'Agreement deleted. You can upload a new one.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Agreement deletion failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to delete agreement: ' . $e->getMessage());
        }
    }
}
