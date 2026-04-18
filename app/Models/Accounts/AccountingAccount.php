<?php

namespace App\Models\Accounts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingAccount extends Model
{
    protected $table = 'accounting_accounts';

    protected $fillable = [
        'name',
        'code',
        'type',
        'parent_id',
        'status',
        'description',
    ];

    public const TYPE_INCOME = 'income';
    public const TYPE_EXPENSE = 'expense';
    public const TYPE_ASSET = 'asset';
    public const TYPE_LIABILITY = 'liability';

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeIncome($query)
    {
        return $query->where('type', self::TYPE_INCOME);
    }

    public function scopeExpense($query)
    {
        return $query->where('type', self::TYPE_EXPENSE);
    }

    public function scopeAsset($query)
    {
        return $query->where('type', self::TYPE_ASSET);
    }

    public function scopeLiability($query)
    {
        return $query->where('type', self::TYPE_LIABILITY);
    }
}
