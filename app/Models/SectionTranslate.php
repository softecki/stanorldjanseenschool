<?php

namespace App\Models;

use App\Models\WebsiteSetup\PageSections;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionTranslate extends Model
{
    use HasFactory;

    protected $casts = [
        'data' => 'array'
    ];

    public function pageSection()
    {
        return $this->belongsTo(PageSections::class, 'section_id', 'id');
    }

}
