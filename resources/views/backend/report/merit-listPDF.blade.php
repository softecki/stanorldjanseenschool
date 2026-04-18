{{-- 
<!DOCTYPE html>
<html>
<head>
    <title>Merit List</title>
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
                <div class="report_header">
                    <div class="report_header_logo">
                        <img class="header_logo" src="{{ @globalAsset(setting('light_logo'), '154X38.webp') }}" alt="{{ __('light logo') }}">
                    </div>
                    <div class="report_header_content">
                        <h3>{{ setting('application_name') }}</h4>
                        <p>{{ setting('address') }}</p>
                    </div>
                </div>
                @if ($data['resultData'])
                    <p class="title">Class (Section) : <strong>{{$data['resultData'][0]->student->session_class_student->class->name}} ({{$data['resultData'][0]->student->session_class_student->section->name}})</p></strong>
                @endif
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="table_th">{{___('common.#')}}</th>
                                <th class="table_th">{{___('common.Student Name')}}</th>
                                <th class="table_th">{{___('common.Admission Id')}}</th>
                                <th class="table_th">{{___('common.Position')}}</th>
                                <th class="table_th">{{___('common.Result')}}</th>
                                <th class="table_th">{{___('common.Point')}}</th>
                                <th class="table_th">{{___('common.Grade')}}</th>
                                <th class="table_th">{{___('common.Total Mark')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['resultData'] as $key=>$item)
                            <tr>
                                <td class="table_td">{{ ++$key }}</td>
                                <td class="table_td">{{ @$item->student->first_name }} {{ @$item->student->last_name }}</td>
                                <td class="table_td">{{ @$item->student->admission_no }}</td>
                                <td class="table_td">{{ @$item->position }}</td>
                                <td class="table_td">{{ @$item->result }}</td>
                                <td class="table_td">{{ @$item->grade_point }}</td>
                                <td class="table_td">{{ @$item->grade_name }}</td>
                                <td class="table_td">{{ @$item->total_marks }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="footer">
                    <img src="{{ globalAsset(setting('favicon')) }}" alt="Icon">
                    <p>{{ setting('footer_text') }}</p>
                </div>
            </div>


        </div>
    </div>
</body>
</html> --}}

<!DOCTYPE html>
<html>
<head>
    <title>Merit List</title>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        margin: 0;
        padding: 0;
        -webkit-print-color-adjust: exact !important;
    }

    table {
        border-collapse: collapse;
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
        border: 1px solid #000;
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

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid black;
        padding: 6px;
        text-align: center;
    }

    th {
        background-color: #f0f0f0;
    }

    .result-header {
        text-align: center;
        margin: 20px 0;
    }

    .result-header h2 {
        margin: 0;
        font-size: 24px;
        font-weight: bold;
    }

    .result-header h3 {
        margin: 5px 0 0;
        font-size: 18px;
        font-weight: normal;
    }

    .rotate-text {
    transform: rotate(-25deg);
    writing-mode: vertical-rl; 
    text-align: center;
    vertical-align: middle;
    font-size: 10px; /* adjust smaller if needed*/
}
.no-border {
    border: none !important;
}
</style>
</head>
<body>
<div class="page-content">

    <div class="page-header">
       
    </div>

       <div class="row">
    
        
        @if (@$data['resultData'])
        <div class="col-lg-12">
         
            <div class="" id="printableArea">
                <div class="result-header">
                    <h2>ST. ARNOLD JANSSEN PRE AND PRIMARY SCHOOL</h2>
                    <h3>CLASS IIIA MID-TERM 1 EXAMINATION RESULTS - {{ date('jS F Y') }}</h3>
                </div>
            <div class="table-responsive">
                <table class="table table-bordered">
        <thead>
            <tr>
                <th>S/No</th>
                <th class="no-border">NAME</th>
                @foreach($data['resultData']['subjects'] as $subjectCode)
                    <th class="rotate-text no-border">{{ strtoupper($subjectCode) }}</th>
                @endforeach
                <th>TOTAL</th>
                <th>AVERAGE</th>
                <th>GRADE</th>
                <th>POSITION</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['resultData']['results'] as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student['name'] }}</td>
                    @foreach($data['resultData']['subjects'] as $subjectCode)
                        <td>{{ $student['subjects'][$subjectCode] }}</td>
                    @endforeach
                    <td>{{ $student['total'] }}</td>
                    <td>{{ $student['average'] }}</td>
                    <td>{{ $student['grade'] }}</td>
                    <td>{{ $student['position'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
            </div>
            </div>
        </div>
    @endif
       </div>
</div>
</body>
</html>

