<?php

namespace App\Models\Library;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueBook extends Model
{
    use HasFactory;

    public function scopeActive($query)
    {
        return $query->where('status', \App\Enums\Status::ACTIVE);
    }
    public function user()
    {
        return $this->belongsTo(Member::class, 'user_id', 'id');
    }
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id', 'id');
    }
}
