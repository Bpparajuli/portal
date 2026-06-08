<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Emails extends Model
{
    use SoftDeletes;

    protected $table = 'emails';

    protected $fillable = [
        'sender_id', 'sender_email', 'sender_name',
        'recipient_email', 'recipient_name', 'recipient_id',
        'cc', 'bcc',
        'subject', 'body', 'body_html',
        'folder', 'status',
        'parent_id', 'reference_type', 'reference_id',
        'attachments', 'is_starred', 'is_important',
        'read_at', 'sent_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_starred' => 'boolean',
        'is_important' => 'boolean',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(__CLASS__, 'parent_id');
    }

    public function scopeInbox($query)
    {
        return $query->where('folder', 'inbox');
    }

    public function scopeSent($query)
    {
        return $query->where('folder', 'sent');
    }

    public function scopeDrafts($query)
    {
        return $query->where('folder', 'drafts');
    }

    public function scopeStarred($query)
    {
        return $query->where('is_starred', true);
    }
}
