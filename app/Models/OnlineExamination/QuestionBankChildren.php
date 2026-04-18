<?php

namespace App\Models\OnlineExamination;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionBankChildren extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_bank_id',
        'option'
    ];
}
