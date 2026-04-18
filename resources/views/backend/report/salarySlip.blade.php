<!DOCTYPE html>
<html>
<head>
    <title>Salary Slip</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            background: #f8f9fa;
        }
        .report {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 20px auto;
            max-width: 800px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .report_header {
            background: #392C7D;
            color: white;
            padding: 10px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .report_header_logo img {
            height: 65px;
        }
        .report_header h3 {
            margin: 0;
            font-size: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background: #E6E6E6;
            font-size: 16px;
            font-weight: bold;
        }
        .footer {
            background: #E6E6E6;
            padding: 10px;
            text-align: center;
            border-radius: 0 0 10px 10px;
        }
    </style>
</head>
<body>
    <div class="report">
        <div class="report_header">
            <div class="report_header_logo">
                <img src="../public/backend/uploads/settings/light.png" alt="NALOPA SCHOOLS">
            </div>
            <h3>NALOPA SCHOOLS</h3>
            <p>Phone: 0753268400 | 0686268400</p>
        </div>

        <table class="table">
            <tr>
                <th>Employee Name:</th>
                <td>{{ 'Nice Emanuel' }}</td>
                <th>Salary Month:</th>
                <td>{{ 'January' }}</td>
            </tr>
            <tr>
                <th>Employee ID:</th>
                <td>{{ '001' }}</td>
                <th>Designation:</th>
                <td>{{ 'Accountant' }}</td>
            </tr>
        </table>

        <table class="table">
            <thead>
                <tr>
                    <th>Earnings</th>
                    <th>Amount (TZS)</th>
                    <th>Deductions</th>
                    <th>Amount (TZS)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ 'Basic Salary' }}</td>
                    <td>{{ '200000' }}</td>
                    <td>{{ 'Loan' }}</td>
                    <td>{{ '50000' }}</td>
                </tr>
                <tr>
                    <td>{{ '' }}</td>
                    <td>{{ '' }}</td>
                    <td>{{ 'PAYE' }}</td>
                    <td>{{ '30000' }}</td>
                </tr>
                <tr>
                    <td>{{ '' }}</td>
                    <td>{{ '' }}</td>
                    <td>{{ 'HESLB' }}</td>
                    <td>{{ '20000' }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: right; font-weight: bold;">Total Earnings</td>
                    <td colspan="2" style="text-align: right;">{{ '200000' }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: right; font-weight: bold;">Total Deductions</td>
                    <td colspan="2" style="text-align: right;">{{ '100000' }}</td>
                </tr>
            </tbody>
        </table>

        <h4 style="text-align: right; margin-right: 10px;">Net Pay: TZS {{ '100000' }}</h4>

        <div class="footer">
            <p>{{ setting('footer_text') }}</p>
        </div>
    </div>
</body>
</html>
