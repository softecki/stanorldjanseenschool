<!DOCTYPE html>
<html dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>{{ $data['title'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
            font-size: 11px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 10px;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 15px;
            font-size: 10px;
        }
        
        .info-section .label {
            font-weight: bold;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        thead {
            background-color: #f5f5f5;
            border-top: 1px solid #ddd;
            border-bottom: 2px solid #333;
        }
        
        th {
            text-align: left;
            padding: 8px;
            font-weight: bold;
            font-size: 10px;
            border-right: 1px solid #ddd;
            color: #333;
        }
        
        th:last-child {
            border-right: none;
        }
        
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .matched-student {
            font-weight: bold;
            color: #007bff;
        }
        
        .not-matched {
            color: #999;
            font-style: italic;
        }
        
        .footer {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 9px;
            text-align: center;
            color: #666;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .summary-section {
            background-color: #f0f0f0;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .summary-section h3 {
            margin: 0 0 8px 0;
            font-size: 12px;
        }
        
        .summary-content {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            font-size: 10px;
        }
        
        .summary-item {
            flex: 1;
            min-width: 150px;
        }
        
        .summary-item .label {
            font-weight: bold;
            color: #333;
        }
        
        .summary-item .value {
            color: #000;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $data['title'] }}</h1>
        <p><span class="label">Generated Date:</span> {{ $data['generated_date'] }}</p>
    </div>

    <div class="summary-section">
        <h3>{{ __('Summary') }}</h3>
        <div class="summary-content">
            <div class="summary-item">
                <span class="label">{{ __('Total Transactions:') }}</span>
                <span class="value">{{ count($data['transactions']) }}</span>
            </div>
            <div class="summary-item">
                <span class="label">{{ __('Matched Students:') }}</span>
                <span class="value">{{ count(array_filter($data['transactions'], fn($t) => !empty($t['student_name']))) }}</span>
            </div>
            <div class="summary-item">
                <span class="label">{{ __('Unmatched Transactions:') }}</span>
                <span class="value">{{ count(array_filter($data['transactions'], fn($t) => empty($t['student_name']))) }}</span>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('Posting Date') }}</th>
                <th>{{ __('Details') }}</th>
                <th>{{ __('Reference Number') }}</th>
                <th>{{ __('Control Number') }}</th>
                <th>{{ __('Value Date') }}</th>
                <th class="text-right">{{ __('Debit') }}</th>
                <th class="text-right">{{ __('Credit') }}</th>
                <th class="text-right">{{ __('Balance') }}</th>
                <th>{{ __('Student Name') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['transactions'] as $transaction)
                <tr>
                    <td>{{ $transaction['posting_date'] }}</td>
                    <td>{{ substr($transaction['details'], 0, 40) }}{{ strlen($transaction['details']) > 40 ? '...' : '' }}</td>
                    <td>{{ $transaction['reference_number'] ?? '-' }}</td>
                    <td>{{ $transaction['control_number'] ?? '-' }}</td>
                    <td>{{ $transaction['value_date'] ?? '-' }}</td>
                    <td class="text-right">
                        @if($transaction['debit'] > 0)
                            {{ number_format($transaction['debit'], 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">
                        @if($transaction['credit'] > 0)
                            {{ number_format($transaction['credit'], 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($transaction['book_balance'], 2) }}</td>
                    <td>
                        @if($transaction['student_name'])
                            <span class="matched-student">{{ $transaction['student_name'] }}</span>
                        @else
                            <span>&nbsp;</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>{{ __('This report was automatically generated by the Bank Reconciliation System') }}</p>
        <p>{{ __('For questions or discrepancies, please contact the accounting department') }}</p>
    </div>
</body>
</html>
