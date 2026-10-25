<?php

namespace App\Services;

use App\Models\Mailbox;
use App\Models\Setting;
use App\Models\User;

/**
 * Mailbox access control, stored in the existing `settings` table as a JSON
 * map: { "<mailbox_id>": [userId, userId, ...] }.
 *
 * Admins see every mailbox. Staff see only the mailboxes they are assigned to.
 * No extra table is required.
 */
class MailboxAccess
{
    public const KEY = 'mailbox_access';

    /** Full assignment map: [mailboxId => [userId, ...]]. */
    public static function map(): array
    {
        $value = Setting::getValue(self::KEY, []);

        return is_array($value) ? $value : [];
    }

    /** User IDs assigned to a single mailbox. */
    public static function assignedUserIds(int $mailboxId): array
    {
        $map = self::map();

        return array_values(array_unique(array_map('intval', (array) ($map[(string) $mailboxId] ?? []))));
    }

    /** Replace the user assignments for a mailbox. */
    public static function setAssignedUsers(int $mailboxId, array $userIds): void
    {
        $map = self::map();

        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));

        if (empty($userIds)) {
            unset($map[(string) $mailboxId]);
        } else {
            $map[(string) $mailboxId] = $userIds;
        }

        Setting::setValue(self::KEY, $map, 'email');
    }

    /** Drop a mailbox's assignments (used when a mailbox is deleted). */
    public static function forget(int $mailboxId): void
    {
        $map = self::map();

        if (isset($map[(string) $mailboxId])) {
            unset($map[(string) $mailboxId]);
            Setting::setValue(self::KEY, $map, 'email');
        }
    }

    /** Mailbox IDs a user may see (all for admins). */
    public static function mailboxIdsFor(?User $user): array
    {
        if (! $user) {
            return [];
        }

        if ($user->is_admin) {
            return Mailbox::pluck('id')->map('intval')->all();
        }

        $ids = [];
        foreach (self::map() as $mailboxId => $userIds) {
            if (in_array($user->id, array_map('intval', (array) $userIds), true)) {
                $ids[] = (int) $mailboxId;
            }
        }

        return $ids;
    }
}
