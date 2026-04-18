<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeworkStudent extends Model
{
    use HasFactory;

    public function homeworkUpload()
    {
        return $this->belongsTo(Upload::class, 'homework', 'id');
    }
}
