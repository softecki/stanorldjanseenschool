<?php

namespace App\Models\Staff;

use App\Models\Gender;
use App\Models\Role;
use App\Models\Staff\Designation;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $casts = [
        'upload_documents' => 'array',
    ];
        
    public function scopeActive($query)
    {
        return $query->where('status', \App\Enums\Status::ACTIVE);
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function upload()
    {
        return $this->belongsTo(Upload::class, 'upload_id', 'id');
    }
    
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
    
    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id', 'id');
    }
    
    public function gender()
    {
        return $this->belongsTo(Gender::class, 'gender_id', 'id');
    }
}
