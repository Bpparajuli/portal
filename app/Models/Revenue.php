<?php
// app/Models/Revenue.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'transaction_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Fix: Use proper event handling to update student's received revenue
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
        $total = $this->student->revenues()->sum('amount');
        $this->student->received_revenue = $total;
        $this->student->saveQuietly(); // Use saveQuietly to avoid infinite loops
    }
}
