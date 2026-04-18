<?php

namespace App\Models\OnlineExamination;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineExamChildrenQuestions extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'online_exam_id',
        'question_bank_id'
    ];

    public function question()
    {
        return $this->belongsTo(QuestionBank::class, 'question_bank_id', 'id');
    }
}
