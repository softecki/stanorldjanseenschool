<?php

namespace App\Models;

use App\Models\Upload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsMailTemplate extends Model
{
    use HasFactory;

    public function attachmentFile()
    {
        return $this->belongsTo(Upload::class, 'attachment', 'id');
    }
}
