<?php

namespace App\Models\WebsiteSetup;

use App\Models\SectionTranslate;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageSections extends Model
{
    use HasFactory;

    protected $casts = [
        'data' => 'array'
    ];

    public function upload()
    {
        return $this->belongsTo(Upload::class, 'upload_id', 'id');
    }

    public function defaultTranslate()
    {
        $relation = $this->hasOne(SectionTranslate::class, 'section_id')->where('locale', request()->locale ?? config('app.locale'));
        if ($relation->exists()) {
            return $relation;
        } else {
            return $this->hasOne(SectionTranslate::class, 'section_id')->where('locale', 'en');
        }
    }


    public function translations()
    {
        return $this->hasMany(SectionTranslate::class, 'section_id', 'id');
    }
}
