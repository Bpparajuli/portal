<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enquiry extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'subject', 'message',
        'type', 'course_id', 'university_id',
        'is_read', 'is_replied', 'replied_at', 'replied_by',
        'reply_message', 'status',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_replied' => 'boolean',
        'replied_at' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function markAsRead(): static
    {
        $this->update(['is_read' => true]);
        return $this;
    }

    public function markAsReplied(User $user, string $reply): static
    {
        $this->update([
            'is_replied' => true,
            'replied_at' => now(),
            'replied_by' => $user->id,
            'reply_message' => $reply,
            'status' => 'replied',
            'is_read' => true,
        ]);
        return $this;
    }
}
