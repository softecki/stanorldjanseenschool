<?php

namespace App\Models\Fees;

use App\Models\Academic\Classes;
use App\Models\StudentInfo\StudentCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeesType extends Model
{
    use HasFactory;

    public function scopeActive($query)
    {
        return $query->where('status', \App\Enums\Status::ACTIVE);
    }

    public function schoolClass()
    {
        return $this->belongsTo(Classes::class, 'class_id', 'id');
    }

    public function feeMasters()
    {
        return $this->hasMany(FeesMaster::class, 'fees_type_id', 'id');
    }

    /**
     * Optional student categories this fee type applies to (empty = not restricted by category).
     */
    public function studentCategories()
    {
        return $this->belongsToMany(
            StudentCategory::class,
            'fees_type_student_category',
            'fees_type_id',
            'student_category_id'
        )->withTimestamps();
    }
}
