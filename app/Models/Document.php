<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'student_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'document_type',
        'notes',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Optional: nice size
    public function getSizeHumanAttribute()
    {
        $size = $this->file_size ?? 0;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) $size /= 1024;
        return round($size, 2) . ' ' . $units[$i];
    }
}
