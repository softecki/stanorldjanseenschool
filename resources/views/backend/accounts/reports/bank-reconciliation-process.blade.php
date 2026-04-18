@extends('backend.master')
@section('title'){{ @$data['title'] }}@endsection
@section('content')
<div class="page-content">
    <div class="page-header">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="bradecrumb-title mb-1">{{ $data['title'] }}</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                    <li class="breadcrumb-item">
                        @if(Route::has('accounting.dashboard'))
                            <a href="{{ route('accounting.dashboard') }}">{{ __('Accounting') }}</a>
                        @else
                            <span>{{ __('Accounting') }}</span>
                        @endif
                    </li>
                    <li class="breadcrumb-item"><a href="{{ route('accounting.bank-reconciliation.index') }}">{{ __('Bank Reconciliation') }}</a></li>
                    <li class="breadcrumb-item">{{ $data['title'] }}</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Processed Transactions') }}</h5>
            <p class="mb-0 text-muted">{{ __('Total Transactions: ' . count($data['transactions'])) }}</p>
        </div>
        <div class="card-body">
            @if(count($data['transactions']) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 10%;">{{ __('Posting Date') }}</th>
                                <th style="width: 20%;">{{ __('Details') }}</th>
                                {{-- <th style="width: 10%;">{{ __('Reference Number') }}</th> --}}
                                <th style="width: 10%;">{{ __('Control Number') }}</th>
                                <th style="width: 10%;">{{ __('Value Date') }}</th>
                                <th class="text-end" style="width: 10%;">{{ __('Debit') }}</th>
                                <th class="text-end" style="width: 10%;">{{ __('Credit') }}</th>
                                <th class="text-end" style="width: 10%;">{{ __('Book Balance') }}</th>
                                <th style="width: 15%;">{{ __('Student Name') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['transactions'] as $index => $transaction)
                                <tr>
                                    <td>
                                        <small>{{ $transaction['posting_date'] }}</small>
                                    </td>
                                    <td>
                                        <small title="{{ $transaction['details'] }}">
                                            {{ substr($transaction['details'], 0, 80) }}{{ strlen($transaction['details']) > 80 ? '...' : '' }}
                                        </small>
                                    </td>
                                    {{-- <td>
                                        <small>{{ $transaction['reference_number'] ?? '-' }}</small>
                                    </td> --}}
                                    <td>
                                        <small>{{ $transaction['control_number'] ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $transaction['value_date'] ?? '-' }}</small>
                                    </td>
                                    <td class="text-end">
                                        <small>{{ $transaction['debit'] > 0 ? number_format($transaction['debit'], 2) : '-' }}</small>
                                    </td>
                                    <td class="text-end">
                                        <small>{{ $transaction['credit'] > 0 ? number_format($transaction['credit'], 2) : '-' }}</small>
                                    </td>
                                    <td class="text-end">
                                        <small>{{ number_format($transaction['book_balance'], 2) }}</small>
                                    </td>
                                    <td>
                                        @if($transaction['student_name'])
                                            <span class="badge bg-success">{{ $transaction['student_name'] }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mt-3" role="alert">
                    <h6 class="alert-heading">{{ __('Summary') }}</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>{{ __('Total Transactions:') }}</strong><br>
                            <span class="badge bg-primary">{{ count($data['transactions']) }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>{{ __('Matched Students:') }}</strong><br>
                            <span class="badge bg-success">{{ count(array_filter($data['transactions'], fn($t) => !empty($t['student_name']))) }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>{{ __('Unmatched:') }}</strong><br>
                            <span class="badge bg-warning">{{ count(array_filter($data['transactions'], fn($t) => empty($t['student_name']))) }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>{{ __('Match Rate:') }}</strong><br>
                            <span class="badge bg-info">
                                {{ count($data['transactions']) > 0 ? round((count(array_filter($data['transactions'], fn($t) => !empty($t['student_name']))) / count($data['transactions'])) * 100, 1) : 0 }}%
                            </span>
                        </div>
                    </div>
                    <p class="mt-2 mb-0">
                        <em>{{ __('Green badges indicate successfully matched students. Dashes (-) indicate unmatched transactions that will show empty in the exported report.') }}</em>
                    </p>
                </div>

                <div class="d-grid gap-2 d-sm-flex justify-content-sm-end mt-3">
                    <a href="{{ route('accounting.bank-reconciliation.excel') }}" class="btn btn-success">
                        <i class="me-2 las la-file-excel"></i>{{ __('Download Excel Report') }}
                    </a>
                    <a href="{{ route('accounting.bank-reconciliation.pdf') }}" class="btn btn-danger">
                        <i class="me-2 las la-file-pdf"></i>{{ __('Download PDF Report') }}
                    </a>
                    <a href="{{ route('accounting.bank-reconciliation.reset') }}" class="btn btn-warning">
                        <i class="me-2 las la-redo"></i>{{ __('Upload Another File') }}
                    </a>
                    <a href="{{ route('accounting.dashboard') }}" class="btn btn-secondary">{{ __('Back to Dashboard') }}</a>
                </div>
            @else
                <div class="text-center py-5">
                    <p class="text-muted">{{ __('No transactions found') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
