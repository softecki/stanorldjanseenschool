<?php

namespace App\Models\Fees;

use App\Models\StudentInfo\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeesCollect extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function feesAssignChild()
    {
        return $this->belongsTo(FeesAssignChildren::class, 'fees_assign_children_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}
