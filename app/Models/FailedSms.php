<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedSms extends Model
{
    use HasFactory;

    protected $table = 'failed_sms';

    protected $fillable = [
        'student_id',
        'phone_number',
        'message',
        'reference',
        'transaction_id',
        'amount',
        'payment_date',
        'status_code',
        'error_response',
        'retry_count',
        'last_retry_at',
        'is_sent',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'last_retry_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}
