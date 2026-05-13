<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Merit List Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 10px; }
        h1 { font-size: 20px; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-weight: 700; }
        .num { text-align: right; }
    </style>
</head>
<body>
    <h1>Merit List Report</h1>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Position</th>
                @foreach(($data['resultData']['subjects'] ?? []) as $subject)
                    <th class="num">{{ strtoupper($subject) }}</th>
                @endforeach
                <th class="num">Total</th>
                <th class="num">Average</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($data['resultData']['results'] ?? []) as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student['name'] ?? 'N/A' }}</td>
                    <td>{{ $student['position'] ?? 'N/A' }}</td>
                    @foreach(($data['resultData']['subjects'] ?? []) as $subject)
                        <td class="num">{{ $student['subjects'][$subject] ?? '-' }}</td>
                    @endforeach
                    <td class="num">{{ $student['total'] ?? 0 }}</td>
                    <td class="num">{{ $student['average'] ?? 0 }}</td>
                    <td>{{ $student['grade'] ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 6 + count($data['resultData']['subjects'] ?? []) }}" style="text-align:center;">No merit list records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
