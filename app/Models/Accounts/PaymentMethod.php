<?php

namespace App\Models\Accounts;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'accounting_payment_methods';

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
