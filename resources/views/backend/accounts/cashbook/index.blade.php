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
                    <li class="breadcrumb-item">@if(Route::has('accounting.dashboard'))<a href="{{ route('accounting.dashboard') }}">{{ __('Accounting') }}</a>@else<span>{{ __('Accounting') }}</span>@endif</li>
                    <li class="breadcrumb-item">{{ $data['title'] }}</li>
                </ol>
            </div>
        </div>
    </div>

    <form method="get" class="mb-3 row g-2 align-items-end">
        <div class="col-auto">
            <label class="form-label mb-0">{{ __('Date') }}</label>
            <input type="date" name="date" class="form-control" value="{{ $data['date'] }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">{{ ___('common.filter') }}</button>
        </div>
    </form>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">{{ __('Daily Cashbook') }} - {{ $data['date'] }}</h5>
            <div>
                <span class="badge bg-success me-2">{{ __('In') }}: {{ number_format($data['total_in'], 0) }}</span>
                <span class="badge bg-danger me-2">{{ __('Out') }}: {{ number_format($data['total_out'], 0) }}</span>
                <span class="badge bg-info">{{ __('Balance') }}: {{ number_format($data['balance'], 0) }}</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ ___('account.date') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th class="text-end">{{ __('In') }}</th>
                            <th class="text-end">{{ __('Out') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['fee_payments'] as $fp)
                        <tr>
                            <td>{{ $fp->date }}</td>
                            <td>{{ __('Fee Payment') }} (Student #{{ $fp->student_id }})</td>
                            <td class="text-end text-success">{{ number_format($fp->amount, 0) }}</td>
                            <td class="text-end">-</td>
                        </tr>
                        @endforeach
                        @foreach($data['incomes'] as $inc)
                        <tr>
                            <td>{{ $inc->date }}</td>
                            <td>{{ $inc->name ?? 'Income' }} {{ $inc->invoice_number ? '(' . $inc->invoice_number . ')' : '' }}</td>
                            <td class="text-end text-success">{{ number_format($inc->amount, 0) }}</td>
                            <td class="text-end">-</td>
                        </tr>
                        @endforeach
                        @foreach($data['expenses'] as $exp)
                        <tr>
                            <td>{{ $exp->date }}</td>
                            <td>{{ $exp->name ?? 'Expense' }} {{ $exp->invoice_number ? '(' . $exp->invoice_number . ')' : '' }}</td>
                            <td class="text-end">-</td>
                            <td class="text-end text-danger">{{ number_format($exp->amount, 0) }}</td>
                        </tr>
                        @endforeach
                        @if($data['fee_payments']->isEmpty() && $data['incomes']->isEmpty() && $data['expenses']->isEmpty())
                            <tr><td colspan="4" class="text-center text-muted">{{ ___('common.no_data_available') }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
