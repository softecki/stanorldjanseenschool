<?php

namespace App\Models\OnlineExamination;

use App\Enums\Status;
use App\Models\Academic\Classes;
use App\Models\Academic\Section;
use App\Models\Academic\Subject;
use App\Models\Examination\ExamType;
use Illuminate\Database\Eloquent\Model;
use App\Models\OnlineExamination\Answer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OnlineExam extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'session_id',
        'classes_id',
        'section_id',
        'subject_id',
        'name',
        'exam_type_id',
        'total_mark',
        'start',
        'end',
        'published',
        'status'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', \App\Enums\Status::ACTIVE);
    }
    public function scopeLatest($query)
    {
        return $query->orderBy('id', 'desc');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'classes_id', 'id');
    }
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }
    public function type()
    {
        return $this->belongsTo(ExamType::class, 'exam_type_id', 'id');
    }
    public function examQuestions()
    {
        return $this->hasMany(OnlineExamChildrenQuestions::class, 'online_exam_id', 'id');
    }
    public function examStudents()
    {
        return $this->hasMany(OnlineExamChildrenStudents::class, 'online_exam_id', 'id');
    }
    public function studentAnswer()
    {
        return $this->hasMany(Answer::class, 'online_exam_id', 'id');
    }

    public function answer()
    {
        return $this->hasOne(Answer::class, 'online_exam_id', 'id');
    }
}
