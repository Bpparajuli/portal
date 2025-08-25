<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_application_id',
        'file_path',
        'type',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class, 'student_application_id');
    }
}
