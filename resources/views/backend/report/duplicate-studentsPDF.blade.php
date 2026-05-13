<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Duplicate Students</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 11px; }
        h1 { font-size: 20px; margin: 0 0 12px; }
        .summary { margin-bottom: 14px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 7px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-weight: 700; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 999px; font-size: 10px; font-weight: 700; }
        .name { background: #fef3c7; color: #92400e; }
        .phone { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <h1>{{ $data['title'] ?? 'Duplicate Students' }}</h1>
    <div class="summary">Total duplicates: {{ count($data['duplicates'] ?? []) }}</div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Duplicate Type</th>
                <th>Class (Section)</th>
                <th>Student 1</th>
                <th>Student 2</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($data['duplicates'] ?? []) as $key => $duplicate)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>
                        <span class="badge {{ ($duplicate['type'] ?? '') === 'name' ? 'name' : 'phone' }}">
                            {{ ($duplicate['type'] ?? '') === 'name' ? 'Same Name' : 'Same Phone' }}
                        </span>
                    </td>
                    <td>{{ $duplicate['class'] ?? 'N/A' }} ({{ $duplicate['section'] ?? 'N/A' }})</td>
                    <td>
                        ID: {{ $duplicate['student_1']['id'] ?? 'N/A' }}<br>
                        Name: {{ $duplicate['student_1']['name'] ?? 'N/A' }}<br>
                        Phone: {{ $duplicate['student_1']['mobile'] ?? 'N/A' }}
                    </td>
                    <td>
                        ID: {{ $duplicate['student_2']['id'] ?? 'N/A' }}<br>
                        Name: {{ $duplicate['student_2']['name'] ?? 'N/A' }}<br>
                        Phone: {{ $duplicate['student_2']['mobile'] ?? 'N/A' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">No duplicate students found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
