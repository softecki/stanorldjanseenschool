<?php

namespace App\Models\StudentInfo;

use App\Models\Fees\FeesType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCategory extends Model
{
    use HasFactory;

    public function scopeActive($query)
    {
        return $query->where('status', \App\Enums\Status::ACTIVE);
    }

    public function feesTypes()
    {
        return $this->belongsToMany(
            FeesType::class,
            'fees_type_student_category',
            'student_category_id',
            'fees_type_id'
        )->withTimestamps();
    }
}
