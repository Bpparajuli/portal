<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentStageHistory extends Model
{
    use HasFactory;

    protected $table = 'student_stage_history';

    protected $fillable = [
        'student_id',
        'from_stage_id',
        'to_stage_id',
        'changed_by',
        'reason',
        'metadata',
        'days_in_previous_stage'
    ];

    protected $casts = [
        'metadata' => 'array',
        'days_in_previous_stage' => 'integer',
    ];

    /**
     * Relationships
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function fromStage()
    {
        return $this->belongsTo(StudentStage::class, 'from_stage_id');
    }

    public function toStage()
    {
        return $this->belongsTo(StudentStage::class, 'to_stage_id');
    }

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Accessors
     */
    public function getDurationInDaysAttribute()
    {
        if ($this->from_stage_id && $this->created_at) {
            $fromHistory = StudentStageHistory::where('student_id', $this->student_id)
                ->where('to_stage_id', $this->from_stage_id)
                ->latest()
                ->first();

            if ($fromHistory) {
                return $fromHistory->created_at->diffInDays($this->created_at);
            }
        }

        return $this->days_in_previous_stage;
    }

    public function getStageChangeDescriptionAttribute()
    {
        $from = $this->fromStage->name ?? 'Initial Stage';
        $to = $this->toStage->name;
        $changer = $this->changer->name;

        return "Stage changed from '{$from}' to '{$to}' by {$changer}";
    }
}
