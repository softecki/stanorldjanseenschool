<?php

namespace App\Models\Fees;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeesMasterQuarter extends Model
{
    protected $table = 'fees_master_quarters';

    protected $fillable = [
        'fees_master_id',
        'quarter',
        'amount',
    ];

    protected $casts = [
        'quarter' => 'integer',
        'amount' => 'decimal:2',
    ];

    public function feesMaster(): BelongsTo
    {
        return $this->belongsTo(FeesMaster::class, 'fees_master_id');
    }

    /**
     * When exactly four quarter rows exist for the master, use those amounts;
     * otherwise split the total evenly across four quarters.
     *
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    public static function resolvedQuarterAmounts(int $feesMasterId, float $feesAmount): array
    {
        $rows = static::query()
            ->where('fees_master_id', $feesMasterId)
            ->orderBy('quarter')
            ->get(['quarter', 'amount']);

        if ($rows->count() === 4) {
            $byQ = $rows->keyBy('quarter');

            return [
                (float) ($byQ->get(1)?->amount ?? 0),
                (float) ($byQ->get(2)?->amount ?? 0),
                (float) ($byQ->get(3)?->amount ?? 0),
                (float) ($byQ->get(4)?->amount ?? 0),
            ];
        }

        $each = $feesAmount > 0 ? $feesAmount / 4 : 0;

        return [$each, $each, $each, $each];
    }
}
