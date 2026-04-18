<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportMonth extends Model
{
    use HasFactory;
     protected $fillable = [
        'student_id',
        'fee_assign_children_id', 'user_id', 'month', 'amount', 'status',
        'state'
    ];
}
