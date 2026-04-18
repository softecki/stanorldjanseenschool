<!DOCTYPE html>
<html>
<head>
    <title>Payments Record</title>
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
                     <a title="Print " class="btn btn-sm btn-outline-warning" href="{{ route('fees-collect.printReceipt',$data['result'][0]->student_id) }}">
                                                <span class="icon mr-1" ><i class="fa-solid fa-receipt"></i></span>
                                            </a>
                    <table class="table">
                        <thead>
                        
                        <tr>
                            <th class="table_td">Student Name:</th>
                            <th class="table_td">{{$data['result'][0]->first_name}} {{$data['result'][0]->last_name}}</th>
                         <th class="table_td">Class Name:</th>
                            <th class="table_td">{{$data['result'][0]->name}}</th>
                        </tr>
                     
                        <tr>
                            @php
                                // Include outstanding balance if remained_amount != 0 OR if outstandingbalance is negative (overpayment)
                                // Only for 2026
                                $outstandingBalance = !empty($data['outstandingBalance'][0]) ? ($data['outstandingBalance'][0]->outstandingbalance ?? 0) : 0;
                                $outstandingRemainedAmount = !empty($data['outstandingBalance'][0]) ? ($data['outstandingBalance'][0]->remained_amount ?? 0) : 0;
                                $outstandingYear = !empty($data['outstandingBalance'][0]) ? ($data['outstandingBalance'][0]->year ?? 2026) : 2026;
                                
                                $shouldIncludeOutstanding = ($outstandingRemainedAmount != 0 || $outstandingBalance < 0);
                                
                                $outstandingAmount = $shouldIncludeOutstanding 
                                    ? ($data['outstandingBalance'][0]->amount ?? 0) 
                                    : 0;
                                $outstandingPaid = $shouldIncludeOutstanding 
                                    ? ($data['outstandingBalance'][0]->paid_amount ?? 0) 
                                    : 0;
                                $outstandingRemained = $shouldIncludeOutstanding 
                                    ? $outstandingBalance 
                                    : 0;
                            @endphp
                            <th class="table_td">Previous Fees ({{ $outstandingYear }}) {{number_format($outstandingAmount, 2, '.', ',') }} TZS.</th>
                            <th class="table_td">Previous Paid ({{ $outstandingYear }}) {{number_format($outstandingPaid, 2, '.', ',') }}  TZS</th>
                            <th class="table_td">Previous Remained ({{ $outstandingYear }})</th>
                            <th class="table_td {{ $outstandingRemained < 0 ? 'text-danger' : '' }}">
                                {{number_format($outstandingRemained, 2, '.', ',') }}  TZS
                                @if($outstandingRemained < 0)
                                    <span style="font-size: 0.85em;">(Overpayment)</span>
                                @endif
                            </th>
                        </tr>

                        <tr>
                            @php
                                $currentYearAmount = $data['otherFee'][0]->amount ?? 0;
                                // If outstanding balance is negative (overpayment), it reduces the total amount
                                $totalAmount = $currentYearAmount + $outstandingRemained;
                            @endphp
                            <th class="table_td">Current Year Amount:</th>
                            <th class="table_td">{{number_format($currentYearAmount, 2, '.', ',') }} TZS.</th>
                             <th class="table_td">Total Amount:</th>
                            <th class="table_td {{ $totalAmount < 0 ? 'text-danger' : '' }}">
                                {{number_format($totalAmount, 2, '.', ',') }}  TZS
                                @if($totalAmount < 0)
                                    <span style="font-size: 0.85em;">(Credit)</span>
                                @endif
                            </th>
                        </tr>
                      
                       
                      
                       

                         <tr>
                            @php
                                $schoolFeesQ1 = !empty($data['schoolfees'][0]) ? ($data['schoolfees'][0]->quater_one ?? 0) : 0;
                                $schoolFeesQ2 = !empty($data['schoolfees'][0]) ? ($data['schoolfees'][0]->quater_two ?? 0) : 0;
                                $schoolFeesQ3 = !empty($data['schoolfees'][0]) ? ($data['schoolfees'][0]->quater_three ?? 0) : 0;
                                $schoolFeesQ4 = !empty($data['schoolfees'][0]) ? ($data['schoolfees'][0]->quater_four ?? 0) : 0;
                            @endphp
                            <th class="table_td">1st Term {{number_format($schoolFeesQ1, 2, '.', ',') }} TZS</th>
                            <th class="table_td">2nd Term {{number_format($schoolFeesQ2, 2, '.', ',') }}  TZS</th>
                            <th class="table_td">3rd Term {{number_format($schoolFeesQ3, 2, '.', ',') }} TZS</th>
                            <th class="table_td">4th Term {{ number_format($schoolFeesQ4, 2, '.', ',') }} TZS</th> 
                        </tr>

                         <tr>
                            @php
                                $transportQ1 = !empty($data['transport'][0]) ? ($data['transport'][0]->quater_one ?? 0) : 0;
                                $transportQ2 = !empty($data['transport'][0]) ? ($data['transport'][0]->quater_two ?? 0) : 0;
                                $transportQ3 = !empty($data['transport'][0]) ? ($data['transport'][0]->quater_three ?? 0) : 0;
                                $transportQ4 = !empty($data['transport'][0]) ? ($data['transport'][0]->quater_four ?? 0) : 0;
                            @endphp
                            <th class="table_td">1st Term {{number_format($transportQ1, 2, '.', ',') }} TZS</th>
                            <th class="table_td">2nd Term {{number_format($transportQ2, 2, '.', ',') }}  TZS</th>
                            <th class="table_td">3rd Term {{number_format($transportQ3, 2, '.', ',') }} TZS</th>
                            <th class="table_td">4th Term {{ number_format($transportQ4, 2, '.', ',') }} TZS</th> 
                        </tr>

                         <tr>
                            @php
                                $lunchQ1 = !empty($data['lunch'][0]) ? ($data['lunch'][0]->quater_one ?? 0) : 0;
                                $lunchQ2 = !empty($data['lunch'][0]) ? ($data['lunch'][0]->quater_two ?? 0) : 0;
                                $lunchQ3 = !empty($data['lunch'][0]) ? ($data['lunch'][0]->quater_three ?? 0) : 0;
                                $lunchQ4 = !empty($data['lunch'][0]) ? ($data['lunch'][0]->quater_four ?? 0) : 0;
                            @endphp
                            <th class="table_td">1st Term {{number_format($lunchQ1, 2, '.', ',') }} TZS</th>
                            <th class="table_td">2nd Term {{number_format($lunchQ2, 2, '.', ',') }}  TZS</th>
                            <th class="table_td">3rd Term {{number_format($lunchQ3, 2, '.', ',') }} TZS</th>
                            <th class="table_td">4th Term {{ number_format($lunchQ4, 2, '.', ',') }} TZS</th> 
                        </tr>
                      
                        </thead>
                    </table>

                @endif
                <div class="table-responsive">
                    <h4>PAYMENT RECORD</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="table_th">{{___('common.date')}}</th>
                                <th class="table_th">{{'Receipt Type'}}</th>
                                <th class="table_th">{{'Receipt Number'}}</th>
                                <th class="table_th">{{___('common.Amount')}} ({{ Setting('currency_symbol') }})</th>
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
                            <tr>
                                <td class="table_td">{{ dateFormat($item->date) }} </td>
                                <td class="table_td">{{ ++$key }} PAYMENT FEES</td>
                                <td class="table_td">{{ $item->transaction_id }}</td>
                                <td class="table_td">{{ number_format($item->amount, 2, '.', ',')}}</td>
                            </tr>
                            @endforeach
                        <tr>
                            <td></td>
                            <td></td>
                            <td class="table_td" >Total Received</td>
                            <td class="table_td">{{ number_format($total, 2, '.', ',') }}</td>
                        </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td class="table_td">Balance Remaining</td>
                                @php
                                    // Calculate balance remaining using the same logic as total amount calculation
                                    // Include outstanding balance if remained_amount != 0 OR if outstandingbalance is negative (overpayment)
                                    $outstandingForBalance = 0;
                                    if (!empty($data['outstandingBalance'][0])) {
                                        $outstandingBal = $data['outstandingBalance'][0]->outstandingbalance ?? 0;
                                        $outstandingRem = $data['outstandingBalance'][0]->remained_amount ?? 0;
                                        if ($outstandingRem != 0 || $outstandingBal < 0) {
                                            $outstandingForBalance = $outstandingBal;
                                        }
                                    }
                                    $currentYearForBalance = $data['otherFee'][0]->amount ?? 0;
                                    // If outstanding balance is negative (overpayment), it reduces the total amount
                                    $totalAmountForBalance = $currentYearForBalance + $outstandingForBalance;
                                    $balanceRemaining = $totalAmountForBalance - $total;
                                @endphp
                                <td class="table_td {{ $balanceRemaining < 0 ? 'text-danger' : '' }}">
                                    {{ number_format($balanceRemaining, 2, '.', ',') }}
                                    @if($balanceRemaining < 0)
                                        <span style="font-size: 0.85em;">(Credit)</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
            </div>


        </div>
    </div>
</body>
</html>
