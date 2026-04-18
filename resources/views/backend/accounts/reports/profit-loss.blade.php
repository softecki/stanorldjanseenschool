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
        <div class="col-auto"><label class="form-label mb-0">{{ __('From') }}</label><input type="date" name="from" class="form-control" value="{{ $data['from'] }}"></div>
        <div class="col-auto"><label class="form-label mb-0">{{ __('To') }}</label><input type="date" name="to" class="form-control" value="{{ $data['to'] }}"></div>
        <div class="col-auto"><button type="submit" class="btn btn-primary">{{ ___('common.filter') }}</button></div>
    </form>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">{{ $data['title'] }}</h5></div>
        <div class="card-body">
            <table class="table table-bordered w-auto">
                <tr>
                    <th>{{ __('Total Income') }} (Other + Fees)</th>
                    <td class="text-end text-success">{{ number_format($data['total_income'], 0) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Total Expense') }}</th>
                    <td class="text-end text-danger">{{ number_format($data['total_expense'], 0) }}</td>
                </tr>
                <tr class="fw-bold">
                    <th>{{ __('Profit / Loss') }}</th>
                    <td class="text-end {{ $data['profit_loss'] >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($data['profit_loss'], 0) }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection
