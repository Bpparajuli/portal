<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';

    protected $fillable = [
        'student_id',
        'uploaded_by',
        'file_name',     // original filename for display
        'file_path',     // path relative to storage/app/public
        'file_type',
        'file_size',
        'document_type'  // optional: education, identification, financial, ward
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
