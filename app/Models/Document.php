<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use App\Models\User;
use Illuminate\Notifications\Notifiable;

class Document extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'student_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'file_type',
        'document_type',
        'status',
    ];

    // Relationships
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
}
