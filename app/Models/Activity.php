<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Activity extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = ['user_id', 'type', 'description', 'notifiable_id', 'link'];

    // Relations
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 🔥 Accessor to make $act->link work in Blade
    public function getLinkAttribute()
    {
        // Return stored link or link inside JSON data if using notifications
        if ($this->attributes['link'] ?? false) {
            return $this->attributes['link'];
        }

        if (isset($this->data['link'])) {
            return $this->data['link'];
        }

        return null;
    }
}
