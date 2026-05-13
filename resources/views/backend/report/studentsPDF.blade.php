<!DOCTYPE html>
<html>
<head>
    <title>Students Report</title>
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
    </style>
</head>
<body>
    <div class="title">Students Report</div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Class</th>
                <th>Section</th>
                <th>Phone</th>
                <th>Guardian Phone</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['rows'] as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->first_name }} {{ $row->last_name }}</td>
                    <td>{{ $row->class_name ?? '-' }}</td>
                    <td>{{ $row->section_name ?? '-' }}</td>
                    <td>{{ $row->mobile ?? '-' }}</td>
                    <td>{{ $row->guardian_mobile ?? '-' }}</td>
                    <td>{{ ($row->active ?? 1) == 2 ? 'Shifted' : 'Active' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
