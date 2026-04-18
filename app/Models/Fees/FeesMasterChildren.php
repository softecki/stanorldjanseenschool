<?php

namespace App\Models\Fees;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeesMasterChildren extends Model
{
    use HasFactory;

    public function type()
    {
        return $this->belongsTo(FeesType::class, 'fees_type_id', 'id');
    }
}
