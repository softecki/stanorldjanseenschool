@extends('backend.master')

@section('title')
    {{ 'SCHOOL REPORT' }}
@endsection
<style>
    body {
        font-family: 'Poppins', sans-serif;
        font-size: 12px;
        margin: 0;
        padding: 0;
    }

    table th, table td {
  font-size: 12px;
  padding: 6px;
  line-height: 1.4; /* Example for table content */
}

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        margin: 0;
        color: #000;
    }

    .routine_wrapper {
        max-width: 100%;
        margin: auto;
        background: #fff;
        padding: 20px;
        border-radius: 5px;
    }

    .table {
        width: 100%;
        margin-bottom: 1rem;
        color: #212529;
    }

    .border_none {
        border: 0px solid transparent;
        border-top: 0px solid transparent !important;
    }

    .routine_part_iner {
        background-color: #fff;
    }

    .routine_part_iner h4 {
        font-size: 30px;
        font-weight: 500;
        margin-bottom: 40px;

    }

    .routine_part_iner h3 {
        font-size: 25px;
        font-weight: 500;
        margin-bottom: 5px;

    }

    .table_border thead {
        background-color: #F6F8FA;
    }

    .table td,
    .table th {
        padding: 0px 0;
        vertical-align: top;
        border-top: 0 solid transparent;
        color: #000;
    }

    .table_border tr {
        border-bottom: 1px solid #000 !important;
    }

    th p span,
    td p span {
        color: #212E40;
    }

    .table th {
        color: #000;
        font-weight: 300;
        border-bottom: 1px solid #000 !important;
        background-color: #fff;
    }

    p {
        font-size: 14px;
        color: #000;
        font-weight: 400;
    }

    h5 {
        font-size: 12px;
        font-weight: 500;
    }

    h6 {
        font-size: 10px;
        font-weight: 300;
    }

    .mt_40 {
        margin-top: 40px;
    }

    .table_style th,
    .table_style td {
        padding: 20px;
    }

    .routine_info_table td {
        font-size: 10px;
        padding: 0px;
    }

    .routine_info_table td h6 {
        color: #6D6D6D;
        font-weight: 400;
    }

    .text_right {
        text-align: right;
    }

    .virtical_middle {
        vertical-align: middle !important;
    }

    .border_bottom {
        border-bottom: 1px solid #000;
    }

    .line_grid {
        display: grid;
        grid-template-columns: 100px auto;
        grid-gap: 10px;
    }

    .line_grid span {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    p {
        margin: 0;
        color: #000;
    }

    .font_18 {
        font-size: 18px;
    }

    .mb-0 {
        margin-bottom: 0;
    }

    .mb_30 {
        margin-bottom: 30px !important;
    }

    .mb_40 {
        margin-bottom: 40px !important;
    }

    .mb_10 {
        margin-bottom: 10px !important;
    }

    .mb_20 {
        margin-bottom: 20px !important;
    }

    .bold_text {
        font-weight: 600;
    }

    .border_table {
        /* border: 1px solid #000; */
    }

    .title_header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 40px 0 15px 0;
    }

    .border_table tr:nth-of-type(n) {
        border: 1px solid #000;
    }

    .border_table tfoot tr:first-of-type {
        border: 0;
    }

    .border_table tfoot tr:first-of-type td {
        border: 0;
    }

    .border_table tbody tr:nth-of-type(odd) {
        background: #F0F3F5;
    }

    .routine_header {}

    .routine_header h3 {
        font-size: 24px;
        font-weight: 500;
    }

    .routine_header p {
        font-size: 14px;
        font-weight: 400;
        margin-bottom: 15px !important;
    }

    /* border_table  */
    .border_table {}

    .border_table thead {}

    .border_table thead tr th {
        padding: 5px;
        border-right: 1px solid #EAEAEA;
        border-color: #EAEAEA !important;
        font-weight: 700;
        font-size: 16px;
        color: #1A1A21;
        padding: 19.75px 16px;
        text-align: left;
        white-space: nowrap;
        vertical-align: middle;
        border-top: 1px solid;
    }

    .border_table tbody tr td,
    .border_table tfoot tr td {
        border-bottom: 1px solid #EAEAEA;
        text-align: left;
        padding: 30.5px 16px;
        border-right: 1px solid #EAEAEA;
        font-weight: 500;
        font-size: 18px;
        line-height: 22px;
        color: #424242;
        white-space: nowrap;
    }

    .border_table tr:nth-of-type(n) {
        border: 1px solid #EAEAEA;
        border-top: 0;
    }

    .border_table tbody tr th {
        background: #EAEAEA;
        border: 1px solid #FFFFFF;
        font-weight: 700;
        font-size: 18px;
        line-height: 30px;
        border-color: #fff !important;
        color: #424242;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 140px;
    }

    .classBox_wiz {
        min-height: 129px;
        vertical-align: middle;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 15px;
    }

    .classBox_wiz h5 {
        font-weight: 600;
        font-size: 18px;
        line-height: 22px;
        color: #424242;
        margin: 0 0 5px 0;
        white-space: nowrap
    }

    .classBox_wiz p {
        font-weight: 500;
        font-size: 14px;
        line-height: 18px;
        color: #6B6B6B;
        margin: 0 0 5px 0;
    }

    .marked_bg {
        background: #6B6B6B !important;
        color: #FFFFFF !important;
    }

    .break_text {
        min-height: 129px;
        vertical-align: middle;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 15px;
    }

    .break_text h5 {
        font-weight: 600;
        font-size: 18px;
        line-height: 22px;
        color: #424242;
        transform: rotate(-30deg);
    }

    .download_print_btns {
        display: flex;
        align-items: center;
        justify-content: center;
        grid-gap: 12px;
    }

    .student_info {
        display: flex;
        align-items: center;
        grid-gap: 16px;
    }

    .student_info img {
        border-radius: 4px;
        width: 24px;
        height: 24px;
    }

    .student_info h5 {
        font-weight: 500;
        font-size: 18px;
        line-height: 22px;
        color: #424242;
        margin: 0;
    }

    /* routine_wrapper_header  */
    .routine_wrapper_header {
        background: #392C7D;
        padding: 32px 36px;
        border-radius: 16px 16px 0 0;
        margin-bottom: 0;
        flex-wrap: wrap;
        grid-gap: 20px;
        margin-bottom: 20px;
        justify-content: center;
    }

    .routine_wrapper_header h3 {
        font-weight: 500;
        font-size: 36px;
        line-height: 40px;
        color: #FFFFFF;
        margin: 0;
    }

    .routine_wrapper_header h4 {
        font-size: 24px;
        color: #FF5170;
        font-weight: 500;
        margin: 7px 0 7px 0;
    }

    .routine_wrapper_header p {
        font-weight: 500;
        font-size: 18px;
        line-height: 30px;
        color: #FFFFFF;
        margin: 0;
    }

    .routine_wrapper_header_logo .header_logo {
        max-width: 193px;
    }

    .routine_wrapper_header {
        display: flex;
        align-items: center;
    }

    .routine_wrapper_header {
        padding: 30px 20px;
    }

    .routine_wrapper_header h3 {
        font-size: 24px;
    }

    .print_copyright_text {
        flex-direction: column;
        align-items: center;
        justify-content: center;
        grid-gap: 10px;
        margin: 20px 0;
        display: flex;
        align-items: center;
        padding-bottom: 10px;

    }

    .download_print_btns {
        display: flex;
        align-items: center;
        justify-content: start;
        grid-gap: 12px;
        background: #F3F3F3;
        padding: 20px;
        flex-wrap: wrap;
    }

    .vertical_seperator {
        border-right: 1px solid #FFFFFF;
        height: 93px;
        margin: 0 30px 0 40px;
    }

    .td-text-center {
        text-align: center !important;
    }

    @media (max-width: 768px) {
        .student_info_single {
            width: 100%;
        }

        .vertical_seperator {
            display: none !important;
        }

        .routine_wrapper {
            width: 100%;
        }

        .routine_wrapper_body {
            padding: 0;
        }

        .student_info_single {
            flex-wrap: wrap;
        }

        .download_print_btns {
            margin-top: 30px;
        }

        .routine_wrapper_header {
            padding: 20px 20px;
        }

        .routine_wrapper_header h3 {
            font-size: 24px;
        }
    }
</style>

@section('content')
    <div class="page-content">
        {{-- bradecrumb Area S t a r t --}}
        <div class="page-header">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="bradecrumb-title mb-1">{{ 'Collection Summary' }}</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item">{{ 'Collection Summary' }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <div class="row">
            <div class="col-12">
                <form action="{{ route('report-fees-summary.search') }}" method="post" id="marksheed" enctype="multipart/form-data">
                    @csrf
                    <div class="card ot-card mb-24 position-relative z_1">
                        <div class="card-header d-flex align-items-center gap-4 flex-wrap">
                            <h3 class="mb-0">{{ ___('common.Filtering') }}</h3>

                            <div class="card_header_right d-flex align-items-center gap-3 flex-fill justify-content-end flex-wrap">
                                <div class="single_small_selectBox">
                                    <select id="getSections" class="class nice-select niceSelect bordered_style wide @error('class') is-invalid @enderror"
                                            name="class" required>
                                        <option value="">{{ ___('student_info.select_class') }} *</option>
                                        <option value="0">{{ 'All' }} </option>
                                        @foreach ($data['classes'] as $item)
                                            <option {{ old('class', @$data['request']->class) == $item->id ? 'selected' : '' }}
                                                    value="{{ $item->class->id }}">{{ $item->class->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('class')
                                    <div id="validationServer04Feedback" class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="single_small_selectBox">
                                    <select class="sections_ nice-select niceSelect bordered_style wide @error('section') is-invalid @enderror"
                                            name="fee_group_id">
                                        <option value="">{{ 'Select Fee' }} </option>
                                        <option value="0">{{ 'All' }} </option>
                                        <option value="1">{{ 'Outstanding Balance' }}</option>
                                        <option value="2">{{ 'School Fees' }}</option>
                                        <option value="3">{{ 'Transport' }}</option>
                                        <option value="4">{{ 'Outstanding Term One' }}</option>
                                        {{-- <option value="5">{{ 'All Outstanding' }}</option> --}}



                                    </select>
                                    @error('section')
                                    <div id="validationServer04Feedback" class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="single_small_selectBox">
                                    <select class="sections_ nice-select niceSelect bordered_style wide @error('section') is-invalid @enderror"
                                            name="section" required>
                                        <option value="">{{ 'Select Status' }} </option>
                                        <option value="0">{{ 'All' }} </option>
                                        {{-- <option value="1">{{ 'Paid' }}</option>
                                        <option value="2">{{ 'Unpaid' }}</option>
                                        <option value="3">{{ 'Partially' }}</option> --}}

                                    </select>
                                    @error('section')
                                    <div id="validationServer04Feedback" class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                 <div class="single_small_selectBox">
                                    <input type="text" class="form-control ot-input" value="{{@$data['request']->amount}}" name="amount">
                                </div>

                                <div class="single_small_selectBox">
                                    <input type="text" class="form-control ot-input" value="{{@$data['request']->dates}}" name="dates">
                                </div>


                                <button class="btn btn-md btn-outline-primary" type="submit">
                                    {{___('common.Search')}}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            @if (@$data['result'])
                <div class="col-lg-12">
                    @if (count($data['result']) > 0)
                        <div class="download_print_btns">
                            <button class="btn btn-md btn-outline-dark" onclick="printDiv('printableArea')">
                                {{___('common.print_now')}}
                                <span><i class="fa-solid fa-print"></i></span>
                            </button>
                            @if(!empty($data['request']))

                                <a class="btn btn-md btn-outline-success" href="{{ route('report-fees-summary.excel-generate', ['class'=>$data['request']->class,'section'=>$data['request']->section, 'dates'=>Illuminate\Support\Facades\Crypt::encryptString($data['request']->dates),'fee_group_id'=>$data['request']->fee_group_id,'amount'=>$data['request']->amount]) }}">
                                    {{'Export Excel'}}
                                    <span><i class=" fa-file-excel"></i></span>
                                </a>
                            @endif

                        </div>
                    @endif

                   
                    <div class="routine_wrapper" id="printableArea">
                    <!-- <div class="col-sm-6">
                    <p class=" mb-1">{{ 'Total Amount' }}: {{number_format($data['total_amount'], 2, '.', ',')}} TZS</p>
                    <p class="text-success mb-1">{{ 'Collected Amount' }}: {{number_format($data['paid_amount'], 2, '.', ',')}} TZS</p>
                    <p class="text-success mb-1">{{ 'Collected Oustanding' }}: {{number_format($data['paid_amount_outstanding'], 2, '.', ',')}} TZS</p>
                    <p class="text-danger mb-1">{{ 'Remained Amount' }}: {{number_format($data['remained_amount'], 2, '.', ',')}} TZS</p>
                    <p class="text-danger mb-1">{{ 'Remained Outstanding' }}: {{number_format($data['remained_amount_outstanding'], 2, '.', ',')}} TZS</p>
                    <p class="text-danger mb-1">{{ 'Total Remained' }}: {{number_format($data['remained_amount']+$data['remained_amount_outstanding'], 2, '.', ',')}} TZS</p>
                    </div> -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb_30">
                                <thead>
                                <tr >
                                    <th >{{___('common.#')}}</th>
                                    <th >{{___('common.name')}}</th>
                                    <th >{{___('academic.class')}} </th>
                                    <th>Fees Amount</th>
                                    <th >{{'Paid'}} ({{ Setting('currency_symbol') }})</th>
                                    <th >{{'Remained '}} ({{ Setting('currency_symbol') }})</th>
                                    <th >{{'Outstanding  '}} ({{ Setting('currency_symbol') }})</th>
                                    <th >{{'Total Remained'}}</th>
                                    <th>Comment</th>

                                </tr>
                                </thead>
                                <tbody>
                                    @php $fees_amount = 0;$paid_amount = 0;$remained_amount = 0; @endphp {{-- Initialize outside --}}
                                    @forelse ($data['result'] as $key => $item)
                                        @php $fees_amount += $item->fees_amount; 
                                        $paid_amount += $item->paid_amount; 
                                        $remained_amount += $item->remained_amount; @endphp {{-- Accumulate fees amount --}}
                                        <tr>
                                            <td>{{ $loop->iteration }}</td> {{-- Use $loop->iteration instead of ++$key --}}
                                            <td>{{ $item->first_name }} {{ $item->last_name }}</td>
                                            <td>{{ $item->class_name }}</td>
                                            <td>{{ isset($data['request']) && $data['request']->fee_group_id == "3" ? 
                                            number_format($item->group3_amount, 2) :
                                             number_format($item->fees_amount, 2)  }}</td>
                                            <td>{{ isset($data['request']) && $data['request']->fee_group_id == "3" ? 
                                            number_format($item->group3_paid_amount, 2) :
                                            number_format($item->paid_amount, 2) }}</td>
                                            <td>{{isset($data['request']) && $data['request']->fee_group_id == "3" ? 
                                            number_format($item->group3_remained_amount, 2) :
                                             number_format($item->remained_amount, 2) }}</td>
                                            <td>{{ $item->outstanding_remained_amount ?? 0 }}</td>
                                            <td>{{ ($item->outstanding_remained_amount ?? 0) + ($item->remained_amount ?? 0) }}</td>
                                            <td>{{ $item->assign_comments ?? '' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="td-text-center">
                                                @include('backend.includes.no-data')
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3"><strong>Total</strong></td>
                                        <td><strong>{{ number_format($fees_amount, 2) }}</strong></td>
                                         <td><strong>{{ number_format($paid_amount, 2) }}</strong></td>
                                          <td><strong>{{ number_format($remained_amount, 2) }}</strong></td>
                                        <td colspan="2"></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!--  pagination start -->

                    <div class="ot-pagination pagination-content d-flex justify-content-end align-content-center py-3">
                        <nav aria-label="Page navigation example">
                            <ul class="pagination justify-content-between">
                                {!!$data['result']->appends(\Request::capture()->except('page'))->links() !!}
                            </ul>
                        </nav>
                    </div>

                    <!--  pagination end -->

                </div>
            @endif
        </div>
    </div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
  const feeGroupSelect = document.querySelector('select[name="fee_group_id"]');
  const datesInput = document.querySelector('input[name="dates"]');
  const datesWrapper = datesInput.closest('.single_small_selectBox');

  function toggleDatesInput() {
    if (feeGroupSelect.value === '1') {
      datesWrapper.style.display = 'none';
    } else {
      datesWrapper.style.display = 'block'; // Use 'block' or '' explicitly
    }
  }

  toggleDatesInput();

  feeGroupSelect.addEventListener('change', toggleDatesInput);
});

    </script>
    
@endsection
