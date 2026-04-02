<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'file',
        'file_type',
        'status',
        'delivered_at',
        'read_at',
    ];

    // Type casting for convenient access
    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'status' => 'string',
    ];

    // Sender relationship
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Receiver relationship
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Optional: helper to check if message is read
    public function isRead(): bool
    {
        return $this->status === 'read';
    }

    // Optional: helper to check if message is delivered
    public function isDelivered(): bool
    {
        return $this->status === 'delivered' || $this->status === 'read';
    }
}
