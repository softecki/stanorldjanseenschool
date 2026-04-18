<?php

namespace App\Models;

use App\Models\Role;
use App\Models\Upload;
use App\Models\Staff\Staff;
use App\Models\Staff\Designation;
use App\Models\StudentInfo\ParentGuardian;
use App\Models\StudentInfo\Student;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'image',
        'password',
        'date_of_birth',
        'upload_id',
        'email_verified_at',
        'phone',
        'permission',
        'last_login',
        'designation_id',
        'status',
        'reset_password_otp',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'reset_password_otp',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'permissions'       => 'array'
    ];

   /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }


    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'upload_id', 'id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function userGender()
    {
        return $this->belongsTo(Gender::class, 'gender', 'id');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id', 'id');
    }

    public function staff()
    {
        return $this->hasOne(Staff::class, 'user_id', 'id');
    }

    public function parent()
    {
        return $this->hasOne(ParentGuardian::class, 'user_id', 'id');
    }


    public function unreadNotifications()
    {
        return $this->hasMany(SystemNotification::class, 'reciver_id', 'id')->latest()->where('is_read',0)->select('id','title','message','reciver_id','created_at');
    }
}
