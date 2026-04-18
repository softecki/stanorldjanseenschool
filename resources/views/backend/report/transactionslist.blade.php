<!DOCTYPE html>
<html>
<head>
    <title>Transaction Receipts</title>
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
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    page-break-after: always;
}
.report:last-child {
    page-break-after: auto;
}
.footer {
    padding: 10px 15px;
    text-align: center;
    background: #E6E6E6 !important;
    border-radius: 0 0 8px 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>
    <?php
    function numberToWords($number) {
        $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
        return $f->format($number);
    }
    ?>
</head>
<body>
@foreach($allData as $index => $data)
    <div class="row">
        <div class="col-lg-12">
            <div class="report">
                @if (!empty($data['result']))
                <!-- Header Section -->
                <div style="background: linear-gradient(135deg, #392C7D 0%, #5a4a9d 100%); padding: 12px 15px; border-radius: 8px 8px 0 0; margin-bottom: 3px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 25%; vertical-align: middle; padding-right: 15px;">
                                <img style="width: 80px; height: 80px; border-radius: 6px; background: white; padding: 6px; object-fit: contain;" 
                                     src="{{ @globalAsset(setting('light_logo'), '154X38.webp') }}" 
                                     alt="{{ __('light logo') }}" 
                                     onerror="this.style.display='none'">
                            </td>
                            <td style="width: 55%; vertical-align: middle; color: #000000;">
                                <h2 style="margin: 0 0 6px 0; font-size: 14px; font-weight: 700; color: #000000; line-height: 1.2;">NALOPA SCHOOLS</h2>
                                <p style="margin: 4px 0; font-size: 14px; color: #000000; font-weight: 700; line-height: 1.3;">Phone: 0753268400 / 0686268400</p>
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

                <!-- Receipt Information Section -->
                <div style="background: #F8F9FA; padding: 12px 15px; border-radius: 8px; margin-bottom: 3px; border-left: 4px solid #392C7D;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px; font-weight: 700; color: #000000;">Receipt Number:</td>
                            <td style="padding: 8px; font-weight: 700; color: #000000; font-family: monospace;">{{ $data['result'][0]->transaction_id }}</td>
                            <td style="padding: 8px; font-weight: 700; color: #000000; text-align: right;">Receipt Date:</td>
                            <td style="padding: 8px; font-weight: 700; color: #000000; text-align: right;">{{ dateFormat($data['result'][0]->date) }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; font-weight: 700; color: #000000;">Student Name:</td>
                            <td style="padding: 8px; font-weight: 700; color: #000000;">{{$data['result'][0]->first_name}} {{$data['result'][0]->last_name}}</td>
                            <td style="padding: 8px; font-weight: 700; color: #000000; text-align: right;">Class:</td>
                            <td style="padding: 8px; font-weight: 700; color: #000000; text-align: right;">{{$data['result'][0]->name}}</td>
                        </tr>
                    </table>
                </div>

                <!-- Payment Details Section -->
                <div style="background: white; padding: 12px 15px; border-radius: 8px; margin-bottom: 3px; border: 1px solid #E0E0E0; border-left: 4px solid #392C7D;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #F8F9FA;">
                                <th style="padding: 10px; text-align: left; font-weight: 700; color: #000000; border-bottom: 1px solid #E0E0E0;">Description</th>
                                <th style="padding: 10px; text-align: right; font-weight: 700; color: #000000; border-bottom: 1px solid #E0E0E0;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 10px; font-weight: 700; color: #000000; border-bottom: 1px solid #E0E0E0;">{{$data['result'][0]->fee_name}}</td>
                                <td style="padding: 10px; text-align: right; font-weight: 700; color: #000000; border-bottom: 1px solid #E0E0E0;">TZS {{number_format($data['result'][0]->amount, 2, '.', ',')}}</td>
                            </tr>
                            <tr style="background: #E8F5E9;">
                                <td style="padding: 12px; font-weight: 700; color: #000000; font-size: 14px; border-top: 2px solid #4CAF50;">Total Paid:</td>
                                <td style="padding: 12px; text-align: right; font-weight: 700; color: #000000; font-size: 14px; border-top: 2px solid #4CAF50;">TZS {{number_format($data['result'][0]->amount, 2, '.', ',')}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Bank Account and Amount in Words -->
                <div style="background: #F8F9FA; padding: 12px 15px; border-radius: 8px; margin-bottom: 3px; text-align: center; border-left: 4px solid #392C7D;">
                    <p style="margin: 0; font-size: 14px; font-weight: 700; color: #000000; line-height: 1.6;">
                        <strong>Receiving Account:</strong> <span style="font-family: monospace; font-weight: 700;">{{$data['result'][0]->account_number}}</span> - {{$data['result'][0]->account_name}} | 
                        <strong>Amount in words:</strong> <?php echo ucfirst(numberToWords($data['result'][0]->amount)); ?> only
                    </p>
                </div>

                @endif

                <!-- Footer -->
                <div class="footer" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 15px;">
                    <span style="font-size: 14px; font-weight: 700; color: #000000;">{{ $data['printedby'] }}</span>
                    <span style="font-size: 14px; font-weight: 700; color: #000000;">{{ setting('footer_text') }}</span>
                </div>
            </div>
        </div>
    </div>
@endforeach
</body>
</html>
