<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAbsentNotification extends Model
{
    use HasFactory;

    protected $cast = ['sending_time' => 'array'];
}
