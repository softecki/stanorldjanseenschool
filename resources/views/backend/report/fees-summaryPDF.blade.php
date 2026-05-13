<!DOCTYPE html>
<html>
<head>
    <title>Fees Summary</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th,
        td {
            padding: 6px;
            border: 1px solid #e5e7eb;
        }
        th {
            background: #f3f4f6;
            font-weight: 700;
            text-align: left;
        }
        .summary {
            margin-bottom: 12px;
        }
        .amount {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="title">Fees Summary</div>

    <table class="summary">
        <tr>
            <th>Students</th>
            <th>Outstanding Fee</th>
            <th>Total Amount</th>
            <th>Fees Amount</th>
            <th>Paid (collections)</th>
            <th>Remained</th>
        </tr>
        <tr>
            <td>{{ $data['totals']['students_count'] ?? 0 }}</td>
            <td class="amount">{{ number_format($data['totals']['remained_amount_outstanding'] ?? 0, 2) }}</td>
            <td class="amount">{{ number_format($data['totals']['total_assigned_amount'] ?? 0, 2) }}</td>
            <td class="amount">{{ number_format($data['totals']['fees_excluding_outstanding'] ?? 0, 2) }}</td>
            <td class="amount">{{ number_format($data['totals']['paid_from_collections'] ?? 0, 2) }}</td>
            <td class="amount">{{ number_format($data['totals']['remained_after_collections'] ?? 0, 2) }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Class</th>
                <th>Phone Number</th>
                <th>Status</th>
                <th class="amount">Outstanding Fee</th>
                <th class="amount">Total Amount</th>
                <th class="amount">Fees Amount</th>
                <th class="amount">Paid Amount</th>
                <th class="amount">Remained Amount</th>
                <th>Comment</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['rows'] as $index => $row)
                @php
                    $status = (string) ($row->active ?? '') === '2' ? 'Shifted' : 'Active';
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->first_name }} {{ $row->last_name }}</td>
                    <td>{{ $row->class_name ?? '-' }}</td>
                    <td>{{ $row->mobile ?? '-' }}</td>
                    <td>{{ $status }}</td>
                    <td class="amount">{{ number_format($row->outstanding_remained_amount ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($row->total_assigned_amount ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($row->fees_amount_excluding_outstanding ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($row->paid_from_collections ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($row->remained_after_collections ?? 0, 2) }}</td>
                    <td>{{ $row->assign_comments ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
