<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dashboard - {{ date('d M Y') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; margin: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: center; }
        thead th { background: #3d5d94; color: #fff; font-weight: bold; }
        .card-block { margin-bottom: 12px; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
        .card-title { background: #f8f9fa; padding: 10px 14px; border-bottom: 1px solid #e0e0e0; font-weight: bold; color: #2c3e50; font-size: 13px; }
        .counters-row { margin-bottom: 12px; }
        .counters-row td { padding: 14px 12px; font-size: 12px; width: 25%; vertical-align: top; }
        .counters-row .value { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
        .counters-row .value-sm { font-size: 11px; font-weight: bold; margin-bottom: 4px; }
        .counters-row .label { font-size: 10px; opacity: 0.95; }
        .total-row { font-weight: bold; background: #f8f9fa; }
    </style>
</head>
<body>

    <h2 style="margin: 0 0 14px 0; color: #2c3e50;">Dashboard - {{ date('d M Y') }}</h2>

    @if(isset($data['student']) || isset($data['parent']))
    <table class="counters-row" cellspacing="0" cellpadding="0" style="width:100%; table-layout:fixed;">
        <tr>
            <td style="background: #3d5d94; color: #fff;">
                <div class="value">{{ $data['student'] ?? 0 }}</div>
                <div class="label">{{ ___('dashboard.Student') }}</div>
            </td>
            <td style="background: #e6b800; color: #2c3e50;">
                <div class="value">{{ $data['parent'] ?? 0 }}</div>
                <div class="label">{{ ___('dashboard.Parent') }}</div>
            </td>
            <td style="background: #10b981; color: #fff;">
                <div class="value value-sm">{{ number_format($data['fees_collect'] ?? 0, 2) }}</div>
                <div class="label">Total Collection (TZS)</div>
            </td>
            <td style="background: #ef4444; color: #fff;">
                <div class="value value-sm">{{ number_format($data['unpaid_amount'] ?? 0, 2) }}</div>
                <div class="label">Due Amount (TZS)</div>
            </td>
        </tr>
    </table>
    @endif

    @if(!empty($data['fees_groups']) && !empty($data['collection_summary']))
    <div class="card-block">
        <div class="card-title">{{ ___('dashboard.fees_collection') }} ({{ date('Y') }})</div>
        <table>
            <thead>
                <tr>
                    <th></th>
                    @foreach($data['fees_groups'] as $group)
                    <th>{{ $group->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    $totals = []; $paid = []; $due = [];
                    foreach ($data['collection_summary'] as $summary) {
                        $totals[] = $summary['total'];
                        $paid[] = $summary['paid'];
                        $due[] = $summary['total'] - $summary['paid'];
                    }
                @endphp
                <tr><th style="text-align:left;">TOTAL</th>@foreach($totals as $t)<td>{{ number_format($t, 2) }}</td>@endforeach</tr>
                <tr><th style="text-align:left;">AMOUNT PAID</th>@foreach($paid as $p)<td>{{ number_format($p, 2) }}</td>@endforeach</tr>
                <tr><th style="text-align:left;">AMOUNT DUE</th>@foreach($due as $d)<td>{{ number_format($d, 2) }}</td>@endforeach</tr>
            </tbody>
        </table>
    </div>
    @endif

    <div class="card-block">
        <div class="card-title">Income vs Expenses ({{ date('Y') }})</div>
        <table>
            <thead><tr><th>Total Income (TZS)</th><th>Total Expenses (TZS)</th><th>Balance (TZS)</th></tr></thead>
            <tbody>
                <tr>
                    <td>{{ number_format($data['income'] ?? 0, 2) }}</td>
                    <td>{{ number_format($data['expense'] ?? 0, 2) }}</td>
                    <td>{{ number_format($data['balance'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if(!empty($data['quarter_summary_by_group']))
    @foreach($data['quarter_summary_by_group'] as $groupName => $summary)
        @if(strtolower(trim($groupName)) === 'outstanding balance') @continue @endif
    <div class="card-block">
        <div class="card-title">{{ $groupName }} – By quarter ({{ date('Y') }})</div>
        <table>
            <thead><tr><th>Quarter</th><th>Expected (TZS)</th><th>Paid (TZS)</th><th>Remained (TZS)</th><th>% Remaining</th></tr></thead>
            <tbody>
                @foreach($summary['quarters'] as $qName => $q)
                <tr>
                    <td>{{ $qName }}</td>
                    <td>{{ number_format($q['expected'], 2) }}</td>
                    <td>{{ number_format($q['paid'], 2) }}</td>
                    <td>{{ number_format($q['remained'], 2) }}</td>
                    <td>{{ $q['pct_remaining'] }}%</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td>Total</td>
                    <td>{{ number_format($summary['total_expected'], 2) }}</td>
                    <td>{{ number_format($summary['total_paid'], 2) }}</td>
                    <td>{{ number_format($summary['total_remained'], 2) }}</td>
                    <td>—</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endforeach
    @endif

</body>
</html>
