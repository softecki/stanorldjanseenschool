<?php

namespace App\Models\OnlineExamination;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnswerChildren extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'answer' => 'array',
    ];

    public function answer()
    {
        return $this->belongsTo(Answer::class, 'answer_id', 'id');
    }
}
