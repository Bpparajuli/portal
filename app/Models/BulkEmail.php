<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkEmail extends Model
{
    protected $table = 'bulk_emails';

    protected $fillable = [
        'subject',
        'body',
        'recipient_group',
        'recipients',
        'recipients_count',
        'sent_count',
        'failed_count',
        'status',
        'attachments',
        'created_by',
    ];

    protected $casts = [
        'attachments' => 'array',
        'recipients' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
