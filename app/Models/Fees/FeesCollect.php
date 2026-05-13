<?php

namespace App\Models\Fees;

use App\Models\BankAccounts;
use App\Models\Session;
use App\Models\StudentInfo\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeesCollect extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function feesAssignChild()
    {
        return $this->belongsTo(FeesAssignChildren::class, 'fees_assign_children_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id', 'id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccounts::class, 'account_id', 'id');
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'fees_collect_by', 'id');
    }
}
