<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revenue extends Model
{
    protected $fillable = [
        'student_id',
        'amount',
        'method',
        'transaction_date',
        'reference_number',
        'description',
        'receipt_file',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime', // Change from 'date' to 'datetime'
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Add a mutator to ensure date is properly set
    public function setTransactionDateAttribute($value)
    {
        if ($value instanceof \DateTime) {
            $this->attributes['transaction_date'] = $value->format('Y-m-d');
        } else {
            $this->attributes['transaction_date'] = $value;
        }
    }

    // Add accessor to always return a Carbon instance
    public function getTransactionDateAttribute($value)
    {
        if ($value) {
            return \Carbon\Carbon::parse($value);
        }
        return null;
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Boot events
    protected static function booted()
    {
        static::created(function ($revenue) {
            $revenue->updateStudentReceivedRevenue();
        });

        static::updated(function ($revenue) {
            $revenue->updateStudentReceivedRevenue();
        });

        static::deleted(function ($revenue) {
            $revenue->updateStudentReceivedRevenue();
        });
    }

    public function updateStudentReceivedRevenue()
    {
        if ($this->student) {
            $total = $this->student->revenues()->sum('amount');
            $this->student->received_revenue = $total;
            $this->student->saveQuietly();
        }
    }
}
