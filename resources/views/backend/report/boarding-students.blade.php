@extends('backend.master')

@section('title')
    {{ 'Boarding Students Report' }}
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
        line-height: 1.4;
    }

    h1, h2, h3, h4, h5, h6 {
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

    .border_table thead {
        background-color: #F6F8FA;
    }

    .table td, .table th {
        padding: 8px;
        vertical-align: top;
        border-top: 1px solid #ddd;
        color: #000;
    }

    .border_table tr {
        border-bottom: 1px solid #ddd !important;
    }

    .table th {
        color: #000;
        font-weight: 600;
        border-bottom: 2px solid #000 !important;
        background-color: #fff;
    }

    p {
        font-size: 14px;
        color: #000;
        font-weight: 400;
        margin: 0;
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

    .td-text-center {
        text-align: center !important;
    }

    .border_table tbody tr:nth-of-type(odd) {
        background: #F0F3F5;
    }
</style>

@section('content')
    <div class="page-content">
        {{-- bradecrumb Area S t a r t --}}
        <div class="page-header">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="bradecrumb-title mb-1">{{ 'Boarding Students Report' }}</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item">{{ 'Reports' }}</li>
                        <li class="breadcrumb-item">{{ 'Boarding Students' }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <div class="row">
            <div class="col-12">
                <form action="{{ route('report-boarding-students.search') }}" method="post" id="boardingStudentsForm" enctype="multipart/form-data">
                    @csrf
                    <div class="card ot-card mb-24 position-relative z_1">
                        <div class="card-header d-flex align-items-center gap-4 flex-wrap">
                            <h3 class="mb-0">{{ ___('common.Filtering') }}</h3>

                            <div class="card_header_right d-flex align-items-center gap-3 flex-fill justify-content-end flex-wrap">
                                <a href="{{ route('report-boarding-students.find-missing-2026') }}" class="btn btn-md btn-info">
                                    <i class="las la-search"></i> Find Missing School Fees 2026
                                </a>
                                <button type="button" class="btn btn-md btn-warning" id="updateBoardingFeesBtn" onclick="updateBoardingSchoolFees2026()">
                                    <i class="las la-edit"></i> Update School Fees to 2,200,000.00 for 2026
                                </button>
                                <div class="single_small_selectBox">
                                    <select id="yearSelect" class="class nice-select niceSelect bordered_style wide @error('year') is-invalid @enderror" name="year">
                                        <option value="">{{ ___('common.select_year') }} ({{ ___('common.all') }})</option>
                                        @foreach ($data['years'] as $yearItem)
                                            <option {{ old('year', @$data['selected_year']) == $yearItem->year ? 'selected' : '' }}
                                                    value="{{ $yearItem->year }}">{{ $yearItem->year }}</option>
                                        @endforeach
                                    </select>
                                    @error('year')
                                    <div id="validationServer04Feedback" class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
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
                        </div>
                    @endif
                    <div class="routine_wrapper" id="printableArea">
                        <div class="routine_part_iner mb_40">
                            <h3 class="mb_20">{{ 'Boarding Students School Fees Report' }}@if(@$data['selected_year']) - Year: {{ $data['selected_year'] }}@endif</h3>
                            <p class="mb_20">Total Records: {{ count($data['result']) }}</p>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table border_table mb_3">
                                <thead>
                                <tr>
                                    <th>{{___('common.#')}}</th>
                                    <th>{{___('common.name')}}</th>
                                    <th>{{ 'Admission No.' }}</th>
                                    <th>{{ 'Year' }}</th>
                                    <th>{{___('academic.class')}}</th>
                                    <th>{{ 'Section' }}</th>
                                    <th>{{ 'School Fees Amount' }}</th>
                                    <th>{{ 'Paid Amount' }}</th>
                                    <th>{{ 'Remained Amount' }}</th>
                                    <th>{{ 'Outstanding Balance' }}</th>
                                </tr>
                                </thead>
                                <tbody class="text-sm">
                                @forelse ($data['result'] as $key=>$item)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $item->first_name }} {{ $item->last_name }}</td>
                                        <td>{{ $item->admission_no ?? 'N/A' }}</td>
                                        <td>{{ $item->year ?? 'N/A' }}</td>
                                        <td>{{ $item->class_name ?? 'N/A' }}</td>
                                        <td>{{ $item->section_name ?? 'N/A' }}</td>
                                        <td>{{ number_format($item->school_fees_amount ?? 0, 2) }}</td>
                                        <td>{{ number_format($item->school_fees_paid ?? 0, 2) }}</td>
                                        <td>{{ number_format($item->school_fees_remained ?? 0, 2) }}</td>
                                        <td>{{ number_format($item->school_fees_outstanding ?? 0, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="td-text-center">
                                            @include('backend.includes.no-data')
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function printDiv(divName) {
            var printContents = document.getElementById(divName).innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }

        function updateBoardingSchoolFees2026() {
            if (!confirm('Are you sure you want to update School Fees to 2,200,000.00 for ALL boarding students in 2026?\n\nThis will:\n- Set fees_amount to 2,200,000.00\n- Set each quarter (Q1, Q2, Q3, Q4) to 550,000.00\n- Update remained_amount = 2,200,000.00 - existing paid_amount\n\nThis action cannot be undone.')) {
                return;
            }

            const btn = document.getElementById('updateBoardingFeesBtn');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="las la-spinner fa-spin"></i> Updating...';

            fetch('{{ route("report-boarding-students.update-fees-2026") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    let message = 'School fees updated successfully!\n\n';
                    message += 'Year: 2026\n';
                    message += 'New Fees Amount: 2,200,000.00\n';
                    message += 'Quarter Amount: 550,000.00 each\n';
                    message += 'Total Found: ' + data.total_found + '\n';
                    message += 'Successfully Updated: ' + data.updated_count;
                    
                    if (data.errors && data.errors.length > 0) {
                        message += '\n\nErrors (' + data.errors.length + '):\n';
                        data.errors.slice(0, 10).forEach(error => {
                            message += '- ' + error + '\n';
                        });
                        if (data.errors.length > 10) {
                            message += '... and ' + (data.errors.length - 10) + ' more errors.';
                        }
                    }
                    
                    alert(message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating school fees.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }
    </script>
@endsection

