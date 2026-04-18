<?php

namespace App\Models\OnlineExamination;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Answer extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function allAnswers()
    {
        return $this->hasMany(AnswerChildren::class, 'answer_id', 'id');
    }

}
