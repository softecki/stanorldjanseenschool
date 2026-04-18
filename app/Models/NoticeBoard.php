<?php

namespace App\Models;

use App\Models\Upload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NoticeBoard extends Model
{
    use HasFactory;

    protected $casts = [
        'visible_to' => 'array',
    ];

    public function attachmentFile()
    {
        return $this->belongsTo(Upload::class, 'attachment', 'id');
    }

    public function defaultTranslate()
    {
        
        $relation = $this->hasOne(NoticeBoardTranslate::class, 'notice_board_id')->where('locale', request()->locale ?? config('app.locale'));
        if ($relation->exists()) {
            return $relation;
        } else {
            return $this->hasOne(NoticeBoardTranslate::class, 'notice_board_id')->where('locale', 'en');
        }
    }


    public function translations()
    {
        return $this->hasMany(NoticeBoardTranslate::class, 'notice_board_id', 'id');
    }

}
