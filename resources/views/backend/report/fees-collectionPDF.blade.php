<!DOCTYPE html>
<html>
<head>
    <title>Fees collection</title>
    <style>
body {
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        margin: 0;
        padding: 0;
        -webkit-print-color-adjust: exact !important;
}
.report {
    background: white;
}
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.table{
    width: 100%;
}
.table_th {
    border-right: 0;
    border-color: transparent !important;
    text-align: left;
    background: #E6E6E6 !important;
    font-size: 16px;
    font-weight: 500;
    text-transform: capitalize;
    padding: 8px 4px;
}
.table_td {
    border-right: 0;
    border-color: transparent !important;
    text-align: left;
    font-size: 14px;
    padding: 8px 4px;
}
.table tr:nth-of-type(odd) {
    padding: 0;
    border-color: white;
    background: #F8F8F8;
}
.table tr:nth-of-type(even) {
    border: 0;
    border-color: white;
    background: #EFEFEF;
}
.footer {
    padding: 5px;
    text-align: center;
    background: #E6E6E6 !important;
    border-radius:  0 0 10px 10px;
}
.title{
    padding: 12px 0px;
    margin: 0 0 12px 0;
    font-size: 20px;
    font-weight: 700;
    text-align: center;
    color: #1f2937;
    border-bottom: 2px solid #E6E6E6;
}

</style>
</head>
<body>
    <div class="row">
        <div class="col-lg-12">
            <div class="report">
                <p class="title">Fees Collection Report</p>
                <table class="table" style="margin-bottom: 12px;">
                    <tr>
                        <th class="table_th">Students</th>
                        <th class="table_th">Total Fees Assigned</th>
                        <th class="table_th">Total Paid</th>
                        <th class="table_th">Remaining Amount</th>
                    </tr>
                    <tr>
                        <td class="table_td">{{ $data['totals']['students_count'] ?? 0 }}</td>
                        <td class="table_td">{{ number_format($data['totals']['total_fees_amount'] ?? 0, 2) }}</td>
                        <td class="table_td">{{ number_format($data['totals']['paid_amount'] ?? 0, 2) }}</td>
                        <td class="table_td">{{ number_format($data['totals']['remained_amount'] ?? 0, 2) }}</td>
                    </tr>
                </table>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="table_th">{{___('common.#')}}</th>
                                <th class="table_th">{{___('common.Name')}}</th>
                                <th class="table_th">{{___('common.Class')}}</th>
                                <th class="table_th">Section</th>
                                <th class="table_th">Total Fees</th>
                                <th class="table_th">Paid</th>
                                <th class="table_th">Remaining</th>
                                <th class="table_th">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['result'] as $key=>$item)
                            <tr>
                                <td class="table_td">{{ ++$key }}</td>
                                <td class="table_td">{{ $item->first_name }} {{ $item->last_name }}</td>
                                <td class="table_td">{{ $item->class_name }}</td>
                                <td class="table_td">{{ $item->section_name ?? '-' }}</td>
                                <td class="table_td">{{ number_format($item->total_fees_amount ?? 0, 2) }}</td>
                                <td class="table_td">{{ number_format($item->paid_amount ?? 0, 2) }}</td>
                                <td class="table_td">{{ number_format($item->remained_amount ?? 0, 2) }}</td>
                                <td class="table_td">{{ ($item->remained_amount ?? 0) > 0 ? 'With balance' : 'Paid' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="footer">
{{--                    <img src="{{ globalAsset(setting('favicon')) }}" alt="Icon">--}}
                    <p>{{ setting('footer_text') }}</p>
                </div>
            </div>


        </div>
    </div>
</body>
</html>
