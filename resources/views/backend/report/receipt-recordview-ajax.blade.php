{{-- Inline receipt fragment for AJAX (no html/head/body). Same data as receipt-recordview, redesigned. --}}
<div class="receipt-inline-panel">
    @if (!empty($data['result']))
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-3 border-bottom">
            <div>
                <h5 class="mb-1 text-dark">{{ $data['result'][0]->first_name }} {{ $data['result'][0]->last_name }}</h5>
                <p class="mb-0 small text-muted">Class: {{ $data['result'][0]->name ?? '—' }}</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <a title="Print" class="btn btn-sm btn-outline-primary" href="{{ route('fees-collect.printReceipt',$data['result'][0]->student_id) }}" target="_blank">
                    <i class="fa-solid fa-print me-1"></i> Print PDF
                </a>
            </div>
        </div>
        @php
            $outstandingBalance = !empty($data['outstandingBalance'][0]) ? ($data['outstandingBalance'][0]->outstandingbalance ?? 0) : 0;
            $outstandingRemainedAmount = !empty($data['outstandingBalance'][0]) ? ($data['outstandingBalance'][0]->remained_amount ?? 0) : 0;
            $outstandingYear = !empty($data['outstandingBalance'][0]) ? ($data['outstandingBalance'][0]->year ?? 2026) : 2026;
            $shouldIncludeOutstanding = ($outstandingRemainedAmount != 0 || $outstandingBalance < 0);
            $outstandingAmount = $shouldIncludeOutstanding ? ($data['outstandingBalance'][0]->amount ?? 0) : 0;
            $outstandingPaid = $shouldIncludeOutstanding ? ($data['outstandingBalance'][0]->paid_amount ?? 0) : 0;
            $outstandingRemained = $shouldIncludeOutstanding ? $outstandingBalance : 0;
            $currentYearAmount = $data['otherFee'][0]->amount ?? 0;
            $totalAmount = $currentYearAmount + $outstandingRemained;
            $totalPaid = 0;
            if (!empty($data['results'])) { foreach ($data['results'] as $it) { $totalPaid += $it->amount; } }
            $outstandingForBalance = 0;
            if (!empty($data['outstandingBalance'][0])) {
                $ob = $data['outstandingBalance'][0]->outstandingbalance ?? 0;
                $orm = $data['outstandingBalance'][0]->remained_amount ?? 0;
                if ($orm != 0 || $ob < 0) $outstandingForBalance = $ob;
            }
            $balanceRemaining = ($data['otherFee'][0]->amount ?? 0) + $outstandingForBalance - $totalPaid;
        @endphp
        <div class="row g-2 mb-3">
            <div class="col-md-6 col-lg-3">
                <div class="p-3 rounded bg-light border">
                    <div class="small text-muted">Previous ({{ $outstandingYear }})</div>
                    <div class="fw-semibold">{{ number_format($outstandingAmount, 2, '.', ',') }} {{ Setting('currency_symbol') }}</div>
                    <div class="small">Remained: <span class="{{ $outstandingRemained < 0 ? 'text-danger' : '' }}">{{ number_format($outstandingRemained, 2, '.', ',') }}</span></div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="p-3 rounded bg-light border">
                    <div class="small text-muted">Current Year</div>
                    <div class="fw-semibold">{{ number_format($currentYearAmount, 2, '.', ',') }} {{ Setting('currency_symbol') }}</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="p-3 rounded bg-light border">
                    <div class="small text-muted">Total Amount</div>
                    <div class="fw-semibold {{ $totalAmount < 0 ? 'text-danger' : '' }}">{{ number_format($totalAmount, 2, '.', ',') }} {{ Setting('currency_symbol') }}</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="p-3 rounded bg-light border">
                    <div class="small text-muted">Balance Remaining</div>
                    <div class="fw-semibold {{ $balanceRemaining < 0 ? 'text-danger' : '' }}">{{ number_format($balanceRemaining, 2, '.', ',') }} {{ Setting('currency_symbol') }}</div>
                </div>
            </div>
        </div>
        <h6 class="mb-2">Terms breakdown (School / Transport / Lunch)</h6>
        @php
            $schoolQ = !empty($data['schoolfees'][0]) ? [$data['schoolfees'][0]->quater_one ?? 0, $data['schoolfees'][0]->quater_two ?? 0, $data['schoolfees'][0]->quater_three ?? 0, $data['schoolfees'][0]->quater_four ?? 0] : [0,0,0,0];
            $transportQ = !empty($data['transport'][0]) ? [$data['transport'][0]->quater_one ?? 0, $data['transport'][0]->quater_two ?? 0, $data['transport'][0]->quater_three ?? 0, $data['transport'][0]->quater_four ?? 0] : [0,0,0,0];
            $lunchQ = !empty($data['lunch'][0]) ? [$data['lunch'][0]->quater_one ?? 0, $data['lunch'][0]->quater_two ?? 0, $data['lunch'][0]->quater_three ?? 0, $data['lunch'][0]->quater_four ?? 0] : [0,0,0,0];
        @endphp
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Term</th>
                        <th class="text-end">School ({{ Setting('currency_symbol') }})</th>
                        <th class="text-end">Transport ({{ Setting('currency_symbol') }})</th>
                        <th class="text-end">Lunch ({{ Setting('currency_symbol') }})</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1st</td>
                        <td class="text-end">{{ number_format($schoolQ[0], 2, '.', ',') }}</td>
                        <td class="text-end">{{ number_format($transportQ[0], 2, '.', ',') }}</td>
                        <td class="text-end">{{ number_format($lunchQ[0], 2, '.', ',') }}</td>
                    </tr>
                    <tr>
                        <td>2nd</td>
                        <td class="text-end">{{ number_format($schoolQ[1], 2, '.', ',') }}</td>
                        <td class="text-end">{{ number_format($transportQ[1], 2, '.', ',') }}</td>
                        <td class="text-end">{{ number_format($lunchQ[1], 2, '.', ',') }}</td>
                    </tr>
                    <tr>
                        <td>3rd</td>
                        <td class="text-end">{{ number_format($schoolQ[2], 2, '.', ',') }}</td>
                        <td class="text-end">{{ number_format($transportQ[2], 2, '.', ',') }}</td>
                        <td class="text-end">{{ number_format($lunchQ[2], 2, '.', ',') }}</td>
                    </tr>
                    <tr>
                        <td>4th</td>
                        <td class="text-end">{{ number_format($schoolQ[3], 2, '.', ',') }}</td>
                        <td class="text-end">{{ number_format($transportQ[3], 2, '.', ',') }}</td>
                        <td class="text-end">{{ number_format($lunchQ[3], 2, '.', ',') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <h6 class="mb-2">Payment Record</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>{{ ___('common.date') }}</th>
                        <th>Receipt Type</th>
                        <th>Receipt Number</th>
                        <th>{{ ___('common.Amount') }} ({{ Setting('currency_symbol') }})</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @foreach ($data['results'] ?? [] as $key => $item)
                        @php $total += $item->amount; @endphp
                        <tr>
                            <td>{{ dateFormat($item->date) }}</td>
                            <td>{{ ++$key }} PAYMENT FEES</td>
                            <td>{{ $item->transaction_id ?? '—' }}</td>
                            <td>{{ number_format($item->amount, 2, '.', ',') }}</td>
                        </tr>
                    @endforeach
                    <tr class="table-light">
                        <td colspan="3" class="text-end">Total Received</td>
                        <td>{{ number_format($total, 2, '.', ',') }}</td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="3" class="text-end">Balance Remaining</td>
                        <td class="{{ $balanceRemaining < 0 ? 'text-danger' : '' }}">{{ number_format($balanceRemaining, 2, '.', ',') }} @if($balanceRemaining < 0)<span class="small">(Credit)</span>@endif</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if(!empty($data['printedby']))
            <p class="small text-muted mb-0 mt-2">Printed by: {{ $data['printedby'] }}</p>
        @endif
    @else
        <p class="text-muted mb-0">No record found.</p>
    @endif
</div>
