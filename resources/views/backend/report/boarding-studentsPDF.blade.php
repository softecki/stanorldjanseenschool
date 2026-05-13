<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Boarding Students Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 10px; }
        h1 { font-size: 20px; margin: 0 0 6px; }
        .muted { color: #6b7280; margin-bottom: 12px; }
        .summary { width: 100%; margin-bottom: 14px; border-collapse: collapse; }
        .summary td { border: 1px solid #d1d5db; padding: 7px; }
        .summary strong { display: block; font-size: 13px; margin-top: 3px; }
        table.report { width: 100%; border-collapse: collapse; }
        .report th, .report td { border: 1px solid #d1d5db; padding: 6px; text-align: left; vertical-align: top; }
        .report th { background: #f3f4f6; font-weight: 700; }
        .money { text-align: right; white-space: nowrap; }
    </style>
</head>
<body>
    @php
        $rows = collect($data['result'] ?? []);
    @endphp
    <h1>Boarding Students Report</h1>
    <div class="muted">Year: {{ $data['selected_year'] ?? date('Y') }} | Total records: {{ $rows->count() }}</div>

    <table class="summary">
        <tr>
            <td>School Fees <strong>{{ number_format($rows->sum('school_fees_amount'), 2) }}</strong></td>
            <td>Paid <strong>{{ number_format($rows->sum('school_fees_paid'), 2) }}</strong></td>
            <td>Remained <strong>{{ number_format($rows->sum('school_fees_remained'), 2) }}</strong></td>
            <td>Outstanding <strong>{{ number_format($rows->sum('school_fees_outstanding'), 2) }}</strong></td>
        </tr>
    </table>

    <table class="report">
        <thead>
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Admission No.</th>
                <th>Year</th>
                <th>Class</th>
                <th>Section</th>
                <th>School Fees</th>
                <th>Paid</th>
                <th>Remained</th>
                <th>Outstanding</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($data['result'] ?? []) as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->first_name }} {{ $item->last_name }}</td>
                    <td>{{ $item->admission_no ?? 'N/A' }}</td>
                    <td>{{ $item->year ?? 'N/A' }}</td>
                    <td>{{ $item->class_name ?? 'N/A' }}</td>
                    <td>{{ $item->section_name ?? 'N/A' }}</td>
                    <td class="money">{{ number_format($item->school_fees_amount ?? 0, 2) }}</td>
                    <td class="money">{{ number_format($item->school_fees_paid ?? 0, 2) }}</td>
                    <td class="money">{{ number_format($item->school_fees_remained ?? 0, 2) }}</td>
                    <td class="money">{{ number_format($item->school_fees_outstanding ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center;">No boarding students found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
