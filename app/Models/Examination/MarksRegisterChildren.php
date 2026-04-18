<?php

namespace App\Models\Examination;

use App\Models\StudentInfo\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarksRegisterChildren extends Model
{
    use HasFactory;

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function MarksRegister()
    {
        return $this->belongsTo(MarksRegister::class, 'marks_register_id', 'id');
    }
}
