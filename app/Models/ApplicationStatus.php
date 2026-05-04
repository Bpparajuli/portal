<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationStatus extends Model
{
    protected $fillable = [
        'name',
        'bg_color',
        'text_color',
        'sort_order',
        'is_active',
    ];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
