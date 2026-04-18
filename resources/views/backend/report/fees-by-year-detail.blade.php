@extends('backend.master')

@section('title')
    {{ 'Student Fees Detail - ' . $data['student']->first_name . ' ' . $data['student']->last_name }}
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

    .mb_20 {
        margin-bottom: 20px !important;
    }

    .mb_30 {
        margin-bottom: 30px !important;
    }
</style>

@section('content')
    <div class="page-content">
        {{-- bradecrumb Area S t a r t --}}
        <div class="page-header">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="bradecrumb-title mb-1">{{ 'Student Fees Detail' }}</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('report-fees-by-year.index') }}">{{ 'Fees By Year' }}</a></li>
                        <li class="breadcrumb-item">{{ $data['student']->first_name }} {{ $data['student']->last_name }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <div class="row">
            <div class="col-12">
                <div class="card ot-card mb-24 position-relative z_1">
                    <div class="card-header d-flex align-items-center gap-4 flex-wrap justify-content-between">
                        <h3 class="mb-0">Student Information</h3>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" class="btn btn-md btn-warning" id="recalculateBtn" onclick="recalculateBalances()">
                                <i class="las la-sync"></i> Recalculate Balances
                            </button>
                            @if($data['selected_year'] == 2025)
                            <button type="button" class="btn btn-md btn-primary" id="generateOutstandingBtn" onclick="generateOutstandingBalance2026()">
                                <i class="las la-plus-circle"></i> Generate Outstanding Balance for 2026
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> {{ $data['student']->first_name }} {{ $data['student']->last_name }}</p>
                        <p><strong>Class:</strong> {{ $data['student']->class_name ?? 'N/A' }}</p>
                        <p><strong>Section:</strong> {{ $data['student']->section_name ?? 'N/A' }}</p>
                        <p><strong>Year:</strong> {{ $data['selected_year'] }}</p>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card ot-card mb-24">
                    <div class="card-header">
                        <h3 class="mb-0">Fees Groups Breakdown</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table border_table mb_3">
                                <thead>
                                <tr>
                                    <th>{{___('common.#')}}</th>
                                    <th>{{'Fees Group'}}</th>
                                    <th>{{'Total Fees Amount'}}</th>
                                    <th>{{'Total Paid Amount'}}</th>
                                    <th>{{'Total Remained Amount'}}</th>
                                    <th>{{'Outstanding Balance'}}</th>
                                </tr>
                                </thead>
                                <tbody class="text-sm">
                                @forelse ($data['feesGroups'] as $key=>$group)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $group->fees_group_name }}</td>
                                        <td>{{ number_format($group->total_fees_amount ?? 0, 2) }}</td>
                                        <td>{{ number_format($group->total_paid_amount ?? 0, 2) }}</td>
                                        <td>{{ number_format($group->total_remained_amount ?? 0, 2) }}</td>
                                        <td>{{ number_format($group->total_outstandingbalance ?? 0, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="td-text-center">
                                            @include('backend.includes.no-data')
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card ot-card mb-24">
                    <div class="card-header">
                        <h3 class="mb-0">Transactions (Year: {{ $data['selected_year'] }})</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table border_table mb_3">
                                <thead>
                                <tr>
                                    <th>{{___('common.#')}}</th>
                                    <th>{{'Transaction ID'}}</th>
                                    <th>{{'Date'}}</th>
                                    <th>{{'Fees Group'}}</th>
                                    <th>{{'Fees Type'}}</th>
                                    <th>{{'Amount'}}</th>
                                    <th>{{'Payment Method'}}</th>
                                    <th>{{'Bank Account'}}</th>
                                </tr>
                                </thead>
                                <tbody class="text-sm">
                                @forelse ($data['transactions'] as $key=>$transaction)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $transaction->transaction_id ?? 'N/A' }}</td>
                                        <td>{{ $transaction->date ? date('Y-m-d', strtotime($transaction->date)) : 'N/A' }}</td>
                                        <td>{{ $transaction->fees_group_name ?? 'N/A' }}</td>
                                        <td>{{ $transaction->fees_type_name ?? 'N/A' }}</td>
                                        <td>{{ number_format($transaction->amount ?? 0, 2) }}</td>
                                        <td>{{ $transaction->payment_method ?? 'N/A' }}</td>
                                        <td>{{ $transaction->account_name ?? ($transaction->account_number ?? 'N/A') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="td-text-center">
                                            @include('backend.includes.no-data')
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function recalculateBalances() {
            if (!confirm('Are you sure you want to recalculate balances? This will reset and reapply all transactions in the correct order.')) {
                return;
            }

            const studentId = {{ $data['student']->id }};
            const year = {{ $data['selected_year'] }};
            const btn = document.getElementById('recalculateBtn');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="las la-spinner fa-spin"></i> Recalculating...';

            fetch(`{{ route('report-fees-by-year.recalculate', ['studentId' => ':studentId', 'year' => ':year']) }}`.replace(':studentId', studentId).replace(':year', year), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Balances recalculated successfully!\nTotal Transactions: ' + data.total_transactions + '\nTotal Amount: ' + parseFloat(data.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
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
            if (!confirm('Are you sure you want to generate Outstanding Balance for 2026 for this student? This will create/update Outstanding Balance entry based on 2025 balances.')) {
                return;
            }

            const studentId = {{ $data['student']->id }};
            const btn = document.getElementById('generateOutstandingBtn');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="las la-spinner fa-spin"></i> Generating...';

            fetch(`{{ route('report-fees-by-year.generate-outstanding-balance-2026-student', ['studentId' => ':studentId']) }}`.replace(':studentId', studentId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Outstanding Balance for 2026 ' + data.action + ' successfully!\n\n' +
                          'Student: ' + data.student_name + '\n' +
                          'Outstanding Balance: ' + parseFloat(data.total_outstanding_balance).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    // Optionally reload or redirect
                    // location.reload();
                } else if (data.status === 'info') {
                    alert(data.message);
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

