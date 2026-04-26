<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserStatus extends Model
{
    protected $table = 'user_statuses';

    protected $fillable = [
        'user_id',
        'is_online',
        'last_seen',
        'last_login_at',
        'last_login_ip'
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_seen' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    // Relationship back to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Get formatted last login
    public function getFormattedLastLoginAttribute()
    {
        if (!$this->last_login_at) {
            return '<span class="text-muted">Never logged in</span>';
        }

        $carbonLastLogin = Carbon::parse($this->last_login_at);
        $now = now();
        $diffInDays = $carbonLastLogin->diffInDays($now);

        if ($diffInDays == 0) {
            $loginText = 'Today at ' . $carbonLastLogin->format('g:i A');
        } elseif ($diffInDays == 1) {
            $loginText = 'Yesterday at ' . $carbonLastLogin->format('g:i A');
        } elseif ($diffInDays < 7) {
            $loginText = $carbonLastLogin->format('l') . ' at ' . $carbonLastLogin->format('g:i A');
        } else {
            $loginText = $carbonLastLogin->format('M j, Y') . ' at ' . $carbonLastLogin->format('g:i A');
        }

        return '<span class="text-muted" title="' . $carbonLastLogin->format('l, F j, Y g:i A') . '">Last login: ' . $loginText . '</span>';
    }
}
