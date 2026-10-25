<?php

namespace App\Http\Controllers;

use App\Models\Mailbox;
use App\Models\User;
use App\Services\MailboxAccess;
use App\Services\MailboxMonitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MailboxController extends Controller
{
    private function authorizeAccess(): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->is_admin || $user->is_staff), 403);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->is_admin, 403);
    }

    public function index()
    {
        $this->authorizeAccess();

        $user = Auth::user();

        $mailboxes = Mailbox::accessibleTo($user)->orderBy('label')->get();

        // Admins manage access; staff just see their assignments.
        $staffUsers = $user->is_admin
            ? User::where('role', 'staff')->orderBy('name')->get(['id', 'name'])
            : collect();
        $staffNames = $staffUsers->pluck('name', 'id');

        $accessMap = MailboxAccess::map();
        $mailboxData = $mailboxes->map(fn ($mb) => [
            'id' => $mb->id,
            'label' => $mb->label,
            'email' => $mb->email,
            'category' => $mb->category,
            'imap_host' => $mb->imap_host,
            'imap_port' => $mb->imap_port,
            'imap_username' => $mb->imap_username,
            'imap_encryption' => $mb->imap_encryption,
            'webmail_url' => $mb->webmail_url,
            'is_active' => (bool) $mb->is_active,
            'user_ids' => array_map('intval', (array) ($accessMap[(string) $mb->id] ?? [])),
        ])->values();

        return view('mail.index', compact('mailboxes', 'staffUsers', 'staffNames', 'accessMap', 'mailboxData'));
    }

    /** JSON feed for the navbar bell. Refreshes synchronously if stale. */
    public function unread()
    {
        $this->authorizeAccess();

        // Refresh on demand (bounded) so counts update without a queue worker
        // or cron. The lock prevents concurrent duplicate polls.
        if (MailboxMonitor::isStale()
            && \Illuminate\Support\Facades\Cache::add(MailboxMonitor::CACHE_REFRESH_LOCK, 1, 15)) {
            try {
                app(MailboxMonitor::class)->pollAll(8);
            } catch (\Throwable $e) {
                // Return whatever counts we already have.
            }
        }

        $rows = MailboxMonitor::breakdown();

        return response()->json([
            'total' => (int) $rows->sum('last_unread_count'),
            'stale' => MailboxMonitor::isStale(),
            'checked_at' => optional($rows->max('last_checked_at'))?->toIso8601String(),
            'mailboxes' => $rows->map(fn ($m) => [
                'id' => $m->id,
                'label' => $m->label,
                'email' => $m->email,
                'unread' => (int) $m->last_unread_count,
                'total_count' => (int) $m->last_total_count,
                'url' => $m->webmail_url,
                'error' => $m->last_error,
            ])->values(),
        ]);
    }

    /** Manually refresh all mailboxes now. */
    public function poll(MailboxMonitor $monitor)
    {
        $this->authorizeAdmin();

        $monitor->pollAll();

        return redirect()->route('mail.index')->with('success', 'Mailboxes refreshed.');
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $mailbox = Mailbox::create($this->validated($request));
        MailboxAccess::setAssignedUsers($mailbox->id, $request->input('users', []));

        return redirect()->route('mail.index')->with('success', 'Mailbox added.');
    }

    public function update(Request $request, Mailbox $mailbox)
    {
        $this->authorizeAdmin();

        $data = $this->validated($request, $mailbox);

        // Keep the existing password when the field is left blank.
        if (empty($data['imap_password'])) {
            unset($data['imap_password']);
        }

        $mailbox->update($data);
        MailboxAccess::setAssignedUsers($mailbox->id, $request->input('users', []));

        return redirect()->route('mail.index')->with('success', 'Mailbox updated.');
    }

    public function destroy(Mailbox $mailbox)
    {
        $this->authorizeAdmin();

        MailboxAccess::forget($mailbox->id);
        $mailbox->delete();

        return redirect()->route('mail.index')->with('success', 'Mailbox removed.');
    }

    private function validated(Request $request, ?Mailbox $mailbox = null): array
    {
        $data = $request->validate([
            'label' => 'required|string|max:100',
            'email' => 'required|email|max:190',
            'category' => ['nullable', Rule::in(['portal', 'admin', 'document', 'student', 'application', 'enquiry'])],
            'imap_host' => 'nullable|string|max:190',
            'imap_port' => 'nullable|integer|min:1|max:65535',
            'imap_username' => 'nullable|string|max:190',
            'imap_password' => 'nullable|string|max:255',
            'imap_encryption' => ['nullable', Rule::in(['ssl', 'tls', 'none'])],
            'webmail_url' => 'nullable|url|max:255',
            'is_active' => 'nullable|boolean',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['imap_port'] = $data['imap_port'] ?? 993;
        $data['imap_encryption'] = $data['imap_encryption'] ?? 'ssl';

        // `users` is handled via the pivot, not a column.
        unset($data['users']);

        return $data;
    }
}
