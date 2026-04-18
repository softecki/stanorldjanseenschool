<?php

namespace App\Models\StudentInfo;

use App\Models\Academic\Shift;
use App\Models\BloodGroup;
use App\Models\Gender;
use App\Models\Religion;
use App\Models\Upload;
use App\Models\User;
use Faker\Core\Blood;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;

    protected $appends = ['full_name'];

    protected $casts = [
        'upload_documents' => 'array',
    ];

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function upload()
    {
        return $this->belongsTo(Upload::class, 'image_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', \App\Enums\Status::ACTIVE);
    }

    public function session_class_student()
    {
        return $this->belongsTo(SessionClassStudent::class, 'id', 'student_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'id');
    }

    public function religion()
    {
        return $this->belongsTo(Religion::class, 'religion_id', 'id');
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class, 'gender_id', 'id');
    }

    public function blood()
    {
        return $this->belongsTo(BloodGroup::class, 'blood_group_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(ParentGuardian::class, 'parent_guardian_id', 'id');
    }

    public function sessionStudentDetails()
    {
        return $this->belongsTo(SessionClassStudent::class, 'id', 'student_id');
    }

    public function studentCategory()
    {
        return $this->belongsTo(StudentCategory::class, 'student_category_id', 'id');
    }
}
