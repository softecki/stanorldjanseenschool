<!DOCTYPE html>
<html>
<head>
    <title>Break Down Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
        }
        .title {
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
            text-align: center;
            font-size: 18px;
            font-weight: 700;
        }
        .summary {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .summary th,
        .summary td,
        .table th,
        .table td {
            padding: 6px;
            border: 1px solid #e5e7eb;
        }
        .summary th,
        .table th {
            background: #f3f4f6;
            font-weight: 700;
            text-align: left;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .amount {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="title">Break Down Report</div>

    <table class="summary">
        <tr>
            <th>Students</th>
            <th>Total Fees</th>
            <th>Total Paid</th>
            <th>Total Remained</th>
        </tr>
        <tr>
            <td>{{ $data['totals']['students_count'] ?? 0 }}</td>
            <td>{{ number_format($data['totals']['total_fees_amount'] ?? 0, 2) }}</td>
            <td>{{ number_format($data['totals']['total_paid_amount'] ?? 0, 2) }}</td>
            <td>{{ number_format($data['totals']['total_remained_amount'] ?? 0, 2) }}</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Phone</th>
                <th>Class</th>
                @foreach ($data['fee_groups'] as $group)
                    <th class="amount">{{ $group->name }} Fees</th>
                    <th class="amount">{{ $group->name }} Paid</th>
                    <th class="amount">{{ $group->name }} Remained</th>
                @endforeach
                <th class="amount">Total Fees</th>
                <th class="amount">Total Paid</th>
                <th class="amount">Total Remained</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['rows'] as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->first_name }} {{ $row->last_name }}</td>
                    <td>{{ $row->mobile ?? '-' }}</td>
                    <td>{{ $row->class_name ?? '-' }}</td>
                    @foreach ($data['fee_groups'] as $group)
                        @php
                            $breakdown = collect($row->fee_breakdowns)->firstWhere('fee_group_id', $group->id);
                        @endphp
                        <td class="amount">{{ number_format($breakdown->fees_amount ?? 0, 2) }}</td>
                        <td class="amount">{{ number_format($breakdown->paid_amount ?? 0, 2) }}</td>
                        <td class="amount">{{ number_format($breakdown->remained_amount ?? 0, 2) }}</td>
                    @endforeach
                    <td class="amount">{{ number_format($row->total_fees_amount ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($row->total_paid_amount ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($row->total_remained_amount ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
