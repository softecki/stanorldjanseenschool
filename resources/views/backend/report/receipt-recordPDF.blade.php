<!DOCTYPE html>
<html>
<head>
    <title>Payments Record</title>
    <style>
body {
        font-family: 'Poppins', 'Arial', sans-serif;
        font-size: 14px;
        margin: 0;
        padding: 20px;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact;
        background: #f5f5f5;
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
    align-content: center;
    width: 1000px;
}
.report_header_content h3 {
    font-size: 24px;
    margin: 0;
    text-align: center;
    width: 1000px;

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
    padding: 10px 0px;
    margin: 10px 0px;
    font-size: 16px;
    text-align: center;
    background: #E6E6E6;
}

</style>
</head>
<body>
    <div class="row">
        <div class="col-lg-12">
            <div class="report">

                <div class="">
                    @if (!empty($data['result']))
                    <!-- Header Section -->
                    <div style="background: linear-gradient(135deg, #392C7D 0%, #5a4a9d 100%); padding: 12px 15px; border-radius: 8px 8px 0 0; margin-bottom: 15px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 25%; vertical-align: middle; padding-right: 15px;">
                                    <img style="width: 80px; height: 80px; border-radius: 6px; background: white; padding: 6px; object-fit: contain;" 
                                         src="{{ @globalAsset(setting('light_logo'), '154X38.webp') }}" 
                                         alt="{{ __('light logo') }}" 
                                         onerror="this.style.display='none'">
                                </td>
                                <td style="width: 55%; vertical-align: middle; color: #000000;">
                                    <h2 style="margin: 0 0 6px 0; font-size: 22px; font-weight: bold; color: #000000; line-height: 1.2;">NALOPA SCHOOLS</h2>
                                    <p style="margin: 4px 0; font-size: 12px; color: #000000; font-weight: 500; line-height: 1.3;">P.O.BOX 11 ARUSHA TANZANIA</p>
                                    <p style="margin: 4px 0; font-size: 12px; color: #000000; font-weight: 500; line-height: 1.3;">Phone: 0753268400 / 0686268400</p>
                                    <p style="margin: 6px 0 0 0; font-size: 11px; color: #000000; font-weight: 600;">Fee Payment Receipt</p>
                                </td>
                                <td style="width: 20%; vertical-align: middle; text-align: right;">
                                    @if(!empty($data['qrCodeUrl']))
                                    <div style="background: white; padding: 6px; border-radius: 6px; display: inline-block;">
                                        <img src="{{ $data['qrCodeUrl'] }}" 
                                             alt="QR Code" 
                                             style="width: 80px; height: 80px; display: block;">
                                    </div>
                                    @endif
                                </td>
                        </tr>
                        </table>
                    </div>

                    <!-- Student Information Section -->
                    <div style="background: #F8F9FA; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #392C7D;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="padding: 8px; width: 25%; font-weight: 600; color: #333;">Student Name:</td>
                                <td style="padding: 8px; width: 25%; color: #555;">{{$data['result'][0]->first_name}} {{$data['result'][0]->last_name}}</td>
                                <td style="padding: 8px; width: 25%; font-weight: 600; color: #333;">Control Number:</td>
                                <td style="padding: 8px; width: 25%; color: #555;">{{$data['result'][0]->control_number}}</td>
                        </tr>
                        <tr>
                                <td style="padding: 8px; font-weight: 600; color: #333;">Class Name:</td>
                                <td style="padding: 8px; color: #555;">{{$data['result'][0]->name}}</td>
                                <td style="padding: 8px; font-weight: 600; color: #333;">Academic Year:</td>
                                <td style="padding: 8px; color: #555;">2026</td>
                        </tr>
                        </table>
                    </div>
                    
                    <!-- Financial Summary Section -->
                    <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #E0E0E0;">
                        <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #392C7D; border-bottom: 2px solid #392C7D; padding-bottom: 8px;">Financial Summary</h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr style="background: #F8F9FA;">
                                <td style="padding: 10px; font-weight: 600; color: #333; border-bottom: 1px solid #E0E0E0;">Previous Balance:</td>
                                <td style="padding: 10px; text-align: right; color: #D32F2F; font-weight: 600; border-bottom: 1px solid #E0E0E0;">
                                    {{number_format($data['outstandingBalance'][0]->outstandingbalance ?? 0, 2, '.', ',') }} TZS
                                </td>
                        </tr>
                        <tr>
                                <td style="padding: 10px; font-weight: 600; color: #333; border-bottom: 1px solid #E0E0E0;">Current Year Amount:</td>
                                <td style="padding: 10px; text-align: right; color: #555; border-bottom: 1px solid #E0E0E0;">
                                    {{number_format($data['otherFee'][0]->amount ?? 0, 2, '.', ',') }} TZS
                                </td>
                        </tr>
                            <tr style="background: #E8F5E9;">
                                <td style="padding: 12px; font-weight: 700; color: #2E7D32; font-size: 16px; border-top: 2px solid #4CAF50;">Total Amount Due:</td>
                                <td style="padding: 12px; text-align: right; color: #2E7D32; font-weight: 700; font-size: 16px; border-top: 2px solid #4CAF50;">
                                    {{number_format(($data['otherFee'][0]->amount ?? 0) + ($data['outstandingBalance'][0]->outstandingbalance ?? 0), 2, '.', ',') }} TZS
                                </td>
                        </tr>
                    </table>
                    </div>

                @endif
                <!-- Payment Record Section -->
                <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #E0E0E0;">
                    <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #392C7D; border-bottom: 2px solid #392C7D; padding-bottom: 8px;">PAYMENT RECORD</h3>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #392C7D; color: white;">
                                <th style="padding: 12px; text-align: left; font-weight: 600; border: 1px solid #2a1f5a;">{{___('common.date')}}</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600; border: 1px solid #2a1f5a;">Receipt Type</th>
                                <th style="padding: 12px; text-align: left; font-weight: 600; border: 1px solid #2a1f5a;">Receipt Number</th>
                                <th style="padding: 12px; text-align: right; font-weight: 600; border: 1px solid #2a1f5a;">{{___('common.Amount')}} ({{ Setting('currency_symbol') }})</th>
                            </tr>
                        </thead>
                                               <tbody>
                            @php
                                $total = 0;
                            @endphp
                            @foreach ($data['results'] as $key=>$item)
                            @php
                                $total += $item->amount;
                            @endphp
                            <tr style="border-bottom: 1px solid #E0E0E0;">
                                <td style="padding: 10px; color: #555;">{{ dateFormat($item->date) }}</td>
                                <td style="padding: 10px; color: #555;">{{ ++$key }} PAYMENT FEES</td>
                                <td style="padding: 10px; color: #555; font-family: monospace;">{{ $item->transaction_id }}</td>
                                <td style="padding: 10px; text-align: right; color: #2E7D32; font-weight: 600;">{{ number_format($item->amount, 2, '.', ',')}}</td>
                            </tr>
                            @endforeach
                            <tr style="background: #F8F9FA; border-top: 2px solid #392C7D;">
                                <td colspan="3" style="padding: 12px; font-weight: 700; color: #333; text-align: right;">Total Received:</td>
                                <td style="padding: 12px; text-align: right; font-weight: 700; color: #2E7D32; font-size: 16px;">{{ number_format($total, 2, '.', ',') }} TZS</td>
                        </tr>
                            <tr style="background: #FFEBEE; border-top: 2px solid #D32F2F;">
                                <td colspan="3" style="padding: 12px; font-weight: 700; color: #333; text-align: right;">Balance Remaining:</td>
                                  @php
                                    $totalAmount = ($data['otherFee'][0]->amount ?? 0) + ($data['outstandingBalance'][0]->outstandingbalance ?? 0);
                                @endphp
                                <td style="padding: 12px; text-align: right; font-weight: 700; color: #D32F2F; font-size: 16px;">{{ number_format(($totalAmount - $total), 2, '.', ',') }} TZS</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Bank Account Information Section -->
                <div style="background: #E3F2FD; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #1976D2;">
                    <h3 style="margin: 0 0 12px 0; font-size: 14px; color: #1976D2; border-bottom: 2px solid #1976D2; padding-bottom: 6px; font-weight: 600;">Bank Account Information</h3>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="background: #FFFFFF;">
                            <td style="padding: 8px; font-weight: 600; color: #333; border-bottom: 1px solid #BBDEFB; width: 25%;">CRDB</td>
                            <td style="padding: 8px; color: #555; border-bottom: 1px solid #BBDEFB; width: 35%;">CRDB - Nalopa Schools</td>
                            <td style="padding: 8px; color: #1976D2; font-weight: 600; font-family: monospace; border-bottom: 1px solid #BBDEFB; width: 40%;">01J1035239900</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; font-weight: 600; color: #333; border-bottom: 1px solid #BBDEFB;">NMB</td>
                            <td style="padding: 8px; color: #555; border-bottom: 1px solid #BBDEFB;">NMB - Nalopa Schools</td>
                            <td style="padding: 8px; color: #1976D2; font-weight: 600; font-family: monospace; border-bottom: 1px solid #BBDEFB;">42810009176</td>
                        </tr>
                     
                    </table>
                </div>
                
                <div class="footer" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 15px;">
                    <p style="margin: 0; font-size: 12px; color: #333;">{{ $data['printedby'] }}</p>
                    <p style="margin: 0; font-size: 12px; color: #333;">{{ setting('footer_text') }}</p>
                </div>
            </div>


        </div>
    </div>
</body>
</html>
