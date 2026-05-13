<?php

namespace App\Models\Fees;

use App\Models\Fees\FeesAssign;
use App\Models\Fees\FeesMaster;
use App\Models\Fees\FeesCollect;
use App\Models\StudentInfo\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeesAssignChildren extends Model
{
    use HasFactory;

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function feesMaster()
    {
        return $this->belongsTo(FeesMaster::class, 'fees_master_id', 'id');
    }

    public function feesCollect()
    {
        return $this->hasOne(FeesCollect::class, 'fees_assign_children_id', 'id');
    }

    /**
     * All payment rows for this assignment line (newest first).
     */
    public function feesCollects()
    {
        return $this->hasMany(FeesCollect::class, 'fees_assign_children_id', 'id')
            ->orderByDesc('fees_collects.created_at')
            ->orderByDesc('fees_collects.id');
    }

    public function feesAssign()
    {
        return $this->belongsTo(FeesAssign::class, 'fees_assign_id', 'id');
    }
}
