<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class StudentStage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'student_stages';

    protected $fillable = [
        'name',
        'slug',
        'color',
        'stage_order',
        'is_active',
        'is_won_stage',
        'is_lost_stage',
        'description',
        'meta_data',
        'allowed_next_stages',
        'max_days_in_stage',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_won_stage' => 'boolean',
        'is_lost_stage' => 'boolean',
        'stage_order' => 'integer',
        'max_days_in_stage' => 'integer',
        'meta_data' => 'array',
        'allowed_next_stages' => 'array',
    ];

    /**
     * Relationships
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'current_stage_id');
    }

    public function historyFrom()
    {
        return $this->hasMany(StudentStageHistory::class, 'from_stage_id');
    }

    public function historyTo()
    {
        return $this->hasMany(StudentStageHistory::class, 'to_stage_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('stage_order', 'asc');
    }

    public function scopeWon($query)
    {
        return $query->where('is_won_stage', true);
    }

    public function scopeLost($query)
    {
        return $query->where('is_lost_stage', true);
    }

    /**
     * Accessors & Mutators
     */
    public function getOrderAttribute()
    {
        return $this->stage_order;
    }

    public function setOrderAttribute($value)
    {
        $this->stage_order = $value;
    }

    public function getColorClassAttribute()
    {
        $colors = [
            '#ef4444' => 'bg-red-500',
            '#f97316' => 'bg-orange-500',
            '#f59e0b' => 'bg-amber-500',
            '#eab308' => 'bg-yellow-500',
            '#84cc16' => 'bg-lime-500',
            '#22c55e' => 'bg-green-500',
            '#10b981' => 'bg-emerald-500',
            '#14b8a6' => 'bg-teal-500',
            '#06b6d4' => 'bg-cyan-500',
            '#3b82f6' => 'bg-blue-500',
            '#6366f1' => 'bg-indigo-500',
            '#8b5cf6' => 'bg-violet-500',
            '#a855f7' => 'bg-purple-500',
            '#ec4899' => 'bg-pink-500',
        ];

        return $colors[$this->color] ?? 'bg-gray-500';
    }

    public function getLightColorClassAttribute()
    {
        $lightColors = [
            '#ef4444' => 'bg-red-100 text-red-800',
            '#f97316' => 'bg-orange-100 text-orange-800',
            '#f59e0b' => 'bg-amber-100 text-amber-800',
            '#eab308' => 'bg-yellow-100 text-yellow-800',
            '#84cc16' => 'bg-lime-100 text-lime-800',
            '#22c55e' => 'bg-green-100 text-green-800',
            '#10b981' => 'bg-emerald-100 text-emerald-800',
            '#14b8a6' => 'bg-teal-100 text-teal-800',
            '#06b6d4' => 'bg-cyan-100 text-cyan-800',
            '#3b82f6' => 'bg-blue-100 text-blue-800',
            '#6366f1' => 'bg-indigo-100 text-indigo-800',
            '#8b5cf6' => 'bg-violet-100 text-violet-800',
            '#a855f7' => 'bg-purple-100 text-purple-800',
            '#ec4899' => 'bg-pink-100 text-pink-800',
        ];

        return $lightColors[$this->color] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Check if can move to next stage
     */
    public function canMoveToStage($stageId): bool
    {
        if (empty($this->allowed_next_stages)) {
            return true;
        }

        return in_array($stageId, $this->allowed_next_stages);
    }

    /**
     * Check if stage is overdue (max_days exceeded)
     */
    public function isOverdueForStudent($student): bool
    {
        if (!$this->max_days_in_stage) {
            return false;
        }

        return $student->days_in_current_stage > $this->max_days_in_stage;
    }

    /**
     * Boot method to generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($stage) {
            $stage->slug = Str::slug($stage->name);
        });

        static::updating(function ($stage) {
            $stage->slug = Str::slug($stage->name);
        });
    }
}
