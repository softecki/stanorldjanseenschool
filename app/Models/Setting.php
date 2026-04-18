<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $casts = [
        'value' => 'array',
    ];

    public function defaultTranslate()
    {

        $relation = $this->hasOne(SettingTranslate::class, 'setting_id')->where('locale', request()->locale ?? config('app.locale'));
        if ($relation->exists()) {
            return $relation;
        } else {
            return $this->hasOne(SettingTranslate::class, 'setting_id')->where('locale', 'en');
        }
    }


    public function translations()
    {
        return $this->hasMany(SettingTranslate::class, 'setting_id', 'id');
    }
}
