@extends('backend.master')

@section('title')
    {{ ___('common.Fees Assignment By Year') }}
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
                    <h1 class="bradecrumb-title mb-1">{{ 'Fees Assignment By Year' }}</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item">{{ 'Reports' }}</li>
                        <li class="breadcrumb-item">{{ 'Fees Assignment By Year' }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <div class="row">
            <div class="col-12">
                <form action="{{ route('report-fees-by-year.search') }}" method="post" id="feesByYearForm" enctype="multipart/form-data">
                    @csrf
                    <div class="card ot-card mb-24 position-relative z_1">
                        <div class="card-header d-flex align-items-center gap-4 flex-wrap">
                            <h3 class="mb-0">{{ ___('common.Filtering') }}</h3>

                            <div class="card_header_right d-flex align-items-center gap-3 flex-fill justify-content-end flex-wrap">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="number" 
                                           id="bulkRecalculateYear" 
                                           class="form-control" 
                                           placeholder="Enter Year (e.g., 2025)" 
                                           value="{{ $data['selected_year'] ?? date('Y') }}"
                                           min="2000" 
                                           max="2100"
                                           style="width: 150px;">
                                    <button type="button" class="btn btn-md btn-warning" id="bulkRecalculateBtn" onclick="bulkRecalculateBalances()">
                                        <i class="las la-sync"></i> Bulk Recalculate Balances
                                    </button>
                                </div>
                                <button type="button" class="btn btn-md btn-primary" id="generateOutstandingBalanceBtn" onclick="generateOutstandingBalance2026()">
                                    <i class="las la-plus-circle"></i> Generate Outstanding Balance for 2026
                                </button>
                                <div class="single_small_selectBox">
                                    <select id="yearSelect" class="class nice-select niceSelect bordered_style wide @error('year') is-invalid @enderror" name="year">
                                        <option value="">{{ ___('common.select_year') }}</option>
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

                                <div class="single_small_selectBox">
                                    <select id="classSelect" class="class nice-select niceSelect bordered_style wide @error('class') is-invalid @enderror" name="class">
                                        <option value="">{{ ___('academic.select_class') }} ({{ ___('common.all') }})</option>
                                        @foreach (@$data['classes'] ?? [] as $classItem)
                                            <option {{ old('class', @$data['selected_class']) == $classItem->id ? 'selected' : '' }}
                                                    value="{{ $classItem->id }}">{{ $classItem->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('class')
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
                            <h3 class="mb_20">{{ 'Fees Assignment Report - Year: ' . @$data['selected_year'] }}</h3>
                            <p class="mb_20">Total Records: {{ count($data['result']) }}</p>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table border_table mb_3">
                                <thead>
                                <tr>
                                    <th>{{___('common.#')}}</th>
                                    <th>{{___('common.name')}}</th>
                                    <th>{{___('academic.class')}}</th>
                                    <th>{{'Total Outstanding Balance'}}</th>
                                    <th>{{'Action'}}</th>
                                </tr>
                                </thead>
                                <tbody class="text-sm">
                                @forelse ($data['result'] as $key=>$item)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $item->first_name }} {{ $item->last_name }}</td>
                                        <td>{{ $item->class_name ?? 'N/A' }}</td>
                                        <td>{{ number_format($item->total_outstandingbalance ?? 0, 2) }}</td>
                                        <td>
                                            <a href="{{ route('report-fees-by-year.detail', ['studentId' => $item->student_id, 'year' => $data['selected_year']]) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="las la-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="td-text-center">
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

        function bulkRecalculateBalances() {
            // Get year from the dedicated input field
            const yearInput = document.getElementById('bulkRecalculateYear');
            const selectedYear = yearInput?.value || document.getElementById('yearSelect')?.value || '{{ $data['selected_year'] ?? date('Y') }}';
            
            if (!selectedYear) {
                alert('Please enter a year in the input field.');
                yearInput?.focus();
                return;
            }
            
            // Validate year format
            const year = parseInt(selectedYear);
            if (isNaN(year) || year < 2000 || year > 2100) {
                alert('Please enter a valid year between 2000 and 2100.');
                yearInput?.focus();
                return;
            }

            if (!confirm('Are you sure you want to recalculate balances for ALL students in year ' + year + '? This will reset and reapply all transactions in the correct order for all students. This action may take several minutes.')) {
                return;
            }

            const btn = document.getElementById('bulkRecalculateBtn');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="las la-spinner fa-spin"></i> Recalculating...';

            fetch('{{ route("report-fees-by-year.bulk-recalculate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    year: year
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    let message = 'Bulk recalculation completed!\n\n';
                    message += 'Year: ' + data.year + '\n';
                    message += 'Total Students: ' + data.total_students + '\n';
                    message += 'Successfully Processed: ' + data.success_count + '\n';
                    message += 'Errors: ' + data.error_count;
                    
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
                alert('An error occurred while recalculating balances.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }

        function generateOutstandingBalance2026() {
            if (!confirm('Are you sure you want to generate Outstanding Balance for 2026? This will create Outstanding Balance entries for all students based on their 2025 balances. This action cannot be undone.')) {
                return;
            }

            // Session ID is fixed to 9 for 2026
            const sessionId = 9;

            const btn = document.getElementById('generateOutstandingBalanceBtn');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="las la-spinner fa-spin"></i> Generating...';

            fetch('{{ route("report-fees-by-year.generate-outstanding-balance-2026") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    session_id: sessionId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    let message = 'Outstanding Balance for 2026 generated successfully!\n\n';
                    message += 'Total Students Processed: ' + data.total_students + '\n';
                    message += 'Created/Updated: ' + data.created_count + '\n';
                    message += 'Skipped: ' + data.skipped_count;
                    
                    if (data.errors && data.errors.length > 0) {
                        message += '\n\nErrors (' + data.errors.length + '):\n';
                        data.errors.slice(0, 5).forEach(error => {
                            message += '- ' + error + '\n';
                        });
                        if (data.errors.length > 5) {
                            message += '... and ' + (data.errors.length - 5) + ' more errors.';
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
                alert('An error occurred while generating Outstanding Balance.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }
    </script>
@endsection

