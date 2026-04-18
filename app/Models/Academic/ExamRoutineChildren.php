<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamRoutineChildren extends Model
{
    use HasFactory;
    
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo(Staff::class, 'teacher_id', 'id');
    }

    public function timeSchedule()
    {
        return $this->belongsTo(TimeSchedule::class, 'time_schedule_id', 'id');
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_room_id', 'id');
    }

    public function examRoutine()
    {
        return $this->belongsTo(ExamRoutine::class, 'exam_routine_id', 'id');
    }
}
