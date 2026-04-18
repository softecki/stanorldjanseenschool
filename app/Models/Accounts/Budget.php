<?php

namespace App\Models\Accounts;

use App\Models\Session;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    protected $table = 'accounting_budgets';

    protected $fillable = [
        'account_id',
        'session_id',
        'amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountingAccount::class, 'account_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }
}
