<?php

namespace App\Models\Library;

use App\Models\StudentInfo\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    public function scopeActive($query)
    {
        return $query->where('status', \App\Enums\Status::ACTIVE);
    }

    public function user()
    {
        if ($this->category_id == 1) {
            // If member_type is 1, refer to the Student model
            return $this->belongsTo(Student::class, 'user_id', 'id');
        } else {
            // Otherwise, refer to the User model
            return $this->belongsTo(User::class, 'user_id', 'id');
        }
    }
    
    public function category()
    {
        return $this->belongsTo(MemberCategory::class, 'category_id', 'id');
    }
}
