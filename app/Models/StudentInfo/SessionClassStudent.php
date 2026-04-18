<?php

namespace App\Models\StudentInfo;

use App\Models\Session;
use App\Models\Academic\Shift;
use App\Models\HomeworkStudent;
use App\Models\Academic\Classes;
use App\Models\Academic\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SessionClassStudent extends Model
{
    use HasFactory;

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id', 'id');
    }
    public function class()
    {
        return $this->belongsTo(Classes::class, 'classes_id', 'id');
    }
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'id');
    }

    public function homeworkStudent()
    {
        return $this->belongsTo(HomeworkStudent::class, 'student_id', 'student_id');
    }

}
