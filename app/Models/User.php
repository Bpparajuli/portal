<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'business_name',
        'owner_name',
        'name',
        'contact',
        'address',
        'email',
        'password',
        'business_logo',
        'is_admin',
        'is_agent',
        'active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        // ✅ Cast to boolean
        'is_admin' => 'boolean',
        'is_agent' => 'boolean',
        'active'   => 'boolean',
    ];
    public function students()
    {
        return $this->hasMany(Student::class, 'agent_id');
    }
}
