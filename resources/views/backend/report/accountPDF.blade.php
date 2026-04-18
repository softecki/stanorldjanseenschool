<!DOCTYPE html>
<html>
<head>
    <title>Transaction</title>
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
.report_header {
    background: #392C7D;
    border-radius: 10px 10px 0 0;
    padding: 10px;
}
.report_header_logo {
    float: left;
    padding: 10px;
    border-right: #E6E6E6 3px solid;
    margin-right: 10px;
}
.report_header_logo img {
    height: 65px;
}
.report_header_content {
    color: white;
}
.report_header_content h3 {
    font-size: 24px;
    margin: 0;
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



</style>

<style>
    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-align: center;
        padding: 10px 0;
    }

    .header-left,
    .header-right {
        flex: 1;
        text-align: center;
        font-weight: bold;
        font-size: 16px;
    }

    .header-center {
        flex: 0;
    }

    .header-logo {
        width: 150px;
        height: auto;
    }

    /* Optional: prevent wrapping if too narrow */
    @media screen and (max-width: 600px) {
        .header-container {
            flex-direction: column;
        }

        .header-left,
        .header-right,
        .header-center {
            text-align: center;
            margin-bottom: 10px;
        }
    }
</style>
</head>
<body>
    <div class="row">
        <div class="col-lg-12"> 
            <div class="report">
                <div class="header-container">
    <div class="header-container">
    <div class="header-left">ROCKLAND ENGLISH MEDIUM SCHOOL</div>

    <div class="header-center">
        <img class="header-logo" src="{{ globalAsset(setting('favicon')) }}" alt="Icon">
    </div>

    <div class="header-right">Schools Nursery and Primary</div>
</div>

    <div class="report-range">
        {{ $data['report'] }} Report From {{ $data['start_date'] }} to {{ $data['end_date'] }}
    </div>
</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="table_th">{{___('common.#')}}</th>
                                <th class="table_th">{{___('common.Date')}}</th>
                                <th class="table_th">{{___('common.Name')}}</th>
                                <th class="table_th">{{___('common.Head')}}</th>
                                <th class="table_th">{{___('common.description')}}</th>
                                <th class="table_th">{{___('common.Amount')}} ({{ Setting('currency_symbol') }})</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['result'] as $key=>$item)
                            <tr>
                                <td class="table_td">{{ ++$key }}</td>
                                <td class="table_td">{{ dateFormat($item->date) }}</td>
                                <td class="table_td">
                                    {{-- @if (@$item->fees_collect_id != null)
                                        {{ @$item->feesType->name }}
                                    @else --}}
                                        {{ @$item->name }}
                                    {{-- @endif --}}
                                </td>
                                <td class="table_td">{{ $item->head->name }}</td>
                                <td>{{ $item->description }}</td>
                                <td class="table_td">{{ number_format($item->amount,2) }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                        <td colspan="2"></td>
                                        <td colspan="3">{{ 'Bank Total' }}:</td>
                                        <td>{{ number_format($data['bank'],2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td colspan="3">{{ 'Cash Total' }}:</td>
                                        <td>{{ number_format($data['cash'],2) }}</td>
                                    </tr>
                            <tr>
                                <td class="table_td" colspan="2"></td>
                                <td class="table_td" colspan="3">Total:</td>
                                <td class="table_td">{{ number_format($data['sum'],2) }}</td> 
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="footer">
                    <p>{{ setting('footer_text') }}</p>
                </div>
            </div>


        </div>
    </div>
</body>
</html>
