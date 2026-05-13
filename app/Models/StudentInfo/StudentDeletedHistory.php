<?php

namespace App\Models\StudentInfo;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentDeletedHistory extends Model
{
    use HasFactory;

    protected $table = 'student_deleted_history';

    protected $guarded = [];

    protected $appends = ['full_name'];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by', 'id');
    }

    public function feesAssignHistory()
    {
        return $this->hasMany(StudentFeesAssignDeletedHistory::class, 'student_deleted_history_id', 'id');
    }

    public function feesCollectHistory()
    {
        return $this->hasMany(StudentFeesCollectDeletedHistory::class, 'student_deleted_history_id', 'id');
    }

    public function getFullNameAttribute()
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }
}
