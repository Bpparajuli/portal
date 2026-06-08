<?php

namespace App\Models\Traits;

use App\Models\UserStatus;
use Carbon\Carbon;

trait HasOnlineStatus
{
    public function status()
    {
        return $this->hasOne(UserStatus::class, 'user_id');
    }

    public function getOnlineStatusAttribute(): array
    {
        if (!$this->status) {
            return [
                'is_online' => false,
                'last_seen' => 'Never',
                'last_seen_full' => null,
                'last_seen_human' => 'Never',
                'last_seen_with_day' => 'Never',
                'last_login' => null,
                'last_login_ip' => null,
            ];
        }

        $isOnline = (bool) $this->status->is_online;
        $lastSeen = $this->status->last_seen;
        $lastLogin = $this->status->last_login_at;
        $lastLoginIp = $this->status->last_login_ip;

        if (!$lastSeen) {
            return [
                'is_online' => false,
                'last_seen' => 'Never',
                'last_seen_full' => null,
                'last_seen_human' => 'Never',
                'last_seen_with_day' => 'Never',
                'last_login' => $this->formatTimestamp($lastLogin),
                'last_login_ip' => $lastLoginIp,
            ];
        }

        $carbonLastSeen = Carbon::parse($lastSeen)->timezone(config('app.timezone'));
        $now = now();

        if ($isOnline && $carbonLastSeen->diffInMinutes($now) <= 2) {
            return [
                'is_online' => true,
                'last_seen' => 'Online',
                'last_seen_full' => $carbonLastSeen->format('l, F j, Y g:i A'),
                'last_seen_human' => 'Online now',
                'last_seen_with_day' => 'Online',
                'last_login' => $this->formatTimestamp($lastLogin),
                'last_login_ip' => $lastLoginIp,
            ];
        }

        $diffInDays = $carbonLastSeen->diffInDays($now);

        if ($diffInDays < 1) {
            $lastSeenText = $carbonLastSeen->diffForHumans(['parts' => 1]);
            $lastSeenWithDay = $carbonLastSeen->diffForHumans(['parts' => 1]);
        } elseif ($diffInDays == 1) {
            $lastSeenText = 'Yesterday at ' . $carbonLastSeen->format('g:i A');
            $lastSeenWithDay = 'Yesterday';
        } elseif ($diffInDays < 7) {
            $lastSeenText = $carbonLastSeen->format('l') . ' at ' . $carbonLastSeen->format('g:i A');
            $lastSeenWithDay = $carbonLastSeen->format('l');
        } else {
            $lastSeenText = $carbonLastSeen->format('M j, Y') . ' at ' . $carbonLastSeen->format('g:i A');
            $lastSeenWithDay = $carbonLastSeen->format('M j, Y');
        }

        return [
            'is_online' => false,
            'last_seen' => $lastSeenText,
            'last_seen_full' => $carbonLastSeen->format('l, F j, Y g:i A'),
            'last_seen_human' => $lastSeenText,
            'last_seen_with_day' => $lastSeenWithDay,
            'last_login' => $this->formatTimestamp($lastLogin),
            'last_login_ip' => $lastLoginIp,
        ];
    }

    protected function formatTimestamp($timestamp): ?string
    {
        if (!$timestamp) {
            return null;
        }

        $carbon = Carbon::parse($timestamp);
        $diffInDays = $carbon->diffInDays(now());

        return match (true) {
            $diffInDays == 0 => 'Today at ' . $carbon->format('g:i A'),
            $diffInDays == 1 => 'Yesterday at ' . $carbon->format('g:i A'),
            $diffInDays < 7 => $carbon->format('l') . ' at ' . $carbon->format('g:i A'),
            default => $carbon->format('M j, Y') . ' at ' . $carbon->format('g:i A'),
        };
    }

    public function getFormattedLastSeenAttribute(): string
    {
        $status = $this->online_status;

        if ($status['is_online']) {
            return '<span class="text-success">● Online</span>';
        }

        if (!$status['last_seen_full']) {
            return '<span class="text-muted">Never</span>';
        }

        return '<span class="text-muted" title="' . $status['last_seen_full'] . '">Last seen: ' . $status['last_seen_with_day'] . '</span>';
    }

    public function getFormattedLastLoginAttribute(): string
    {
        $status = $this->online_status;

        if (!$status['last_login']) {
            return '<span class="text-muted">Never logged in</span>';
        }

        return '<span class="text-muted">Last login: ' . $status['last_login'] . '</span>';
    }

    public function updateOnlineStatus(): UserStatus
    {
        $status = $this->status()->firstOrCreate([], [
            'is_online' => true,
            'last_seen' => now(),
        ]);

        $status->update([
            'is_online' => true,
            'last_seen' => now(),
        ]);

        return $status;
    }

    public function setOffline(): void
    {
        if ($this->status) {
            $this->status->update([
                'is_online' => false,
                'last_seen' => now(),
            ]);
        }
    }

    public function updateLoginInfo($request): UserStatus
    {
        $status = $this->status()->firstOrCreate([], [
            'is_online' => true,
            'last_seen' => now(),
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $status->update([
            'is_online' => true,
            'last_seen' => now(),
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        return $status;
    }
}
