<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Activity extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = ['user_id', 'type', 'description', 'notifiable_id', 'link'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
