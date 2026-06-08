<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'file_type',
        'document_type',
        'custom_name',
        'description',
        'status',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public static function getSopForStudent(int $studentId): ?self
    {
        return static::where('student_id', $studentId)
            ->where('document_type', 'SOP')
            ->first();
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('document_type', $type);
    }
}
