<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationDocument extends Model
{
    protected $fillable = [
        'application_id',
        'document_type',
        'file_path',
        'uploaded_by',
    ];

    public function application()
    {
        return $this->belongsTo(StudentApplication::class, 'application_id');
    }
}
