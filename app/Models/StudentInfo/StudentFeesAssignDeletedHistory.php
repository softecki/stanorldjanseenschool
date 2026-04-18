<?php

namespace App\Models\StudentInfo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentFeesAssignDeletedHistory extends Model
{
    use HasFactory;

    protected $table = 'student_fees_assign_deleted_history';

    protected $guarded = [];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];
}
