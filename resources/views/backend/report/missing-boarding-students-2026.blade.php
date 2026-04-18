@extends('backend.master')

@section('title')
    {{ 'Missing Boarding Students - School Fees 2026' }}
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
                    <h1 class="bradecrumb-title mb-1">{{ 'Missing Students - School Fees 2026' }}</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item">{{ 'Reports' }}</li>
                        <li class="breadcrumb-item">{{ 'Boarding Students' }}</li>
                        <li class="breadcrumb-item">{{ 'Missing Students 2026' }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <div class="row">
            <div class="col-12">
                <div class="card ot-card mb-24 position-relative z_1">
                    <div class="card-header d-flex align-items-center gap-4 flex-wrap">
                        <h3 class="mb-0">{{ 'Students Missing School Fees for 2026' }}</h3>

                        <div class="card_header_right d-flex align-items-center gap-3 flex-fill justify-content-end flex-wrap">
                            <button type="button" class="btn btn-md btn-success" id="createMissingFeesBtn" onclick="createMissingBoardingSchoolFees2026()">
                                <i class="las la-plus-circle"></i> Create School Fees for All Missing Students
                            </button>
                            <a href="{{ route('report-boarding-students.index') }}" class="btn btn-md btn-outline-secondary">
                                <i class="las la-arrow-left"></i> Back to Boarding Students Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @if (@$data['missing_students'])
                <div class="col-lg-12">
                    @if (count($data['missing_students']) > 0)
                        <div class="download_print_btns">
                            <p class="mb-0"><strong>Total Missing Students: {{ $data['total_missing'] }}</strong></p>
                        </div>
                    @endif
                    <div class="routine_wrapper" id="printableArea">
                        <div class="routine_part_iner mb_40">
                            <h3 class="mb_20">{{ 'Students Missing School Fees for 2026' }}</h3>
                            <p class="mb_20">These students had School Fees in 2025 but don't have School Fees assignments for 2026.</p>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table border_table mb_3">
                                <thead>
                                <tr>
                                    <th>{{___('common.#')}}</th>
                                    <th>{{___('common.name')}}</th>
                                    <th>{{ 'Admission No.' }}</th>
                                    <th>{{ 'Class 2026' }}</th>
                                    <th>{{ 'Section 2026' }}</th>
                                </tr>
                                </thead>
                                <tbody class="text-sm">
                                @forelse ($data['missing_students'] as $key=>$student)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                        <td>{{ $student->admission_no ?? 'N/A' }}</td>
                                        <td>{{ $student->class_2026 ?? 'N/A' }}</td>
                                        <td>{{ $student->section_2026 ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="td-text-center">
                                            <p>No missing students found. All students from 2025 have School Fees for 2026.</p>
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
        function createMissingBoardingSchoolFees2026() {
            if (!confirm('Are you sure you want to create School Fees (2,200,000.00) for ALL missing students in 2026?\n\nThis will:\n- Create School Fees assignments for students who had School Fees in 2025 but missing for 2026\n- Set fees_amount to 2,200,000.00\n- Set each quarter (Q1, Q2, Q3, Q4) to 550,000.00\n- Use their 2026 class and section information\n\nThis action cannot be undone.')) {
                return;
            }

            const btn = document.getElementById('createMissingFeesBtn');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="las la-spinner fa-spin"></i> Creating...';

            fetch('{{ route("report-boarding-students.create-missing-fees-2026") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    let message = 'School Fees created successfully!\n\n';
                    message += 'Year: 2026\n';
                    message += 'New Fees Amount: 2,200,000.00\n';
                    message += 'Quarter Amount: 550,000.00 each\n';
                    message += 'Total Found: ' + data.total_found + '\n';
                    message += 'Successfully Created: ' + data.created_count;
                    
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
                alert('An error occurred while creating School Fees.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }
    </script>
@endsection

