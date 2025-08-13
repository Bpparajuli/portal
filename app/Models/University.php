<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'country',
        'city',
        'website',
        'contact_email',
        'description',
    ];

    /**
     * Relationship: A university has many courses.
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
