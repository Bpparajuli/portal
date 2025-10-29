<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class ApplicationMessage extends Model
{
    use HasFactory, Notifiable;
    protected $table = 'application_message';
    protected $fillable = [
        'application_id',
        'user_id',
        'message',
        'type',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
