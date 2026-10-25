<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mailbox extends Model
{
    protected $fillable = [
        'label',
        'email',
        'category',
        'imap_host',
        'imap_port',
        'imap_username',
        'imap_password',
        'imap_encryption',
        'webmail_url',
        'is_active',
        'last_unread_count',
        'last_total_count',
        'last_checked_at',
        'last_error',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_checked_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Limit a query to the mailboxes a user is allowed to see:
     * admins get everything; staff only their assigned mailboxes
     * (assignments live in the `settings` table via MailboxAccess).
     */
    public function scopeAccessibleTo($query, ?User $user)
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->is_admin) {
            return $query;
        }

        $ids = \App\Services\MailboxAccess::mailboxIdsFor($user);

        return $query->whereIn('id', $ids ?: [0]);
    }

    public function getWebmailLinkAttribute(): ?string
    {
        if ($this->webmail_url) {
            return $this->webmail_url;
        }

        return null;
    }
}
