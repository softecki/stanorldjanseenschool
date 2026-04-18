<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOverview extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stock_overview';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'goods_name',
        'stocked_amount',
        // 'available_stock',
        // 'consumed_amount',
        'unit_of_measure',
        'category',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        // 'stocked_amount' => 'decimal:2',
        // 'available_stock' => 'decimal:2',
        // 'consumed_amount' => 'decimal:2',
    ];
}

