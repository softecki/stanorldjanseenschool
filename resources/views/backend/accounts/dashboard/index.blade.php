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
                    <li class="breadcrumb-item">{{ $data['title'] }}</li>
                </ol>
            </div>
        </div>
    </div>

    {{-- Quick links --}}
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('chart-of-accounts.index') }}" class="btn btn-outline-primary btn-sm me-1">{{ __('Chart of Accounts') }}</a>
            <a href="{{ route('payment-methods.index') }}" class="btn btn-outline-primary btn-sm me-1">{{ __('Payment Methods') }}</a>
            <a href="{{ route('income.index') }}" class="btn btn-outline-primary btn-sm me-1">{{ ___('account.income') }}</a>
            <a href="{{ route('expense.index') }}" class="btn btn-outline-primary btn-sm me-1">{{ ___('account.expense') }}</a>
            <a href="{{ route('accounting.cashbook') }}" class="btn btn-outline-primary btn-sm me-1">{{ __('Daily Cashbook') }}</a>
            <a href="{{ route('accounting.reports.income') }}" class="btn btn-outline-primary btn-sm me-1">{{ __('Income Report') }}</a>
            <a href="{{ route('accounting.reports.expense') }}" class="btn btn-outline-primary btn-sm me-1">{{ __('Expense Report') }}</a>
            <a href="{{ route('accounting.reports.profit-loss') }}" class="btn btn-outline-primary btn-sm me-1">{{ __('Profit & Loss') }}</a>
            @if(\Illuminate\Support\Facades\Schema::hasTable('accounting_audit_logs'))
                <a href="{{ route('accounting.audit-log') }}" class="btn btn-outline-secondary btn-sm">{{ __('Audit Log') }}</a>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">{{ __('Total Income') }} (Session)</h6>
                    <h4 class="card-title">{{ number_format($data['total_income'] + $data['fees_collected'], 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">{{ __('Total Expenses') }} (Session)</h6>
                    <h4 class="card-title">{{ number_format($data['total_expense'], 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">{{ __('Fees Collected') }}</h6>
                    <h4 class="card-title">{{ number_format($data['fees_collected'], 0) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card {{ ($data['total_income'] + $data['fees_collected'] - $data['total_expense']) >= 0 ? 'bg-info' : 'bg-warning' }} text-white">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2">{{ __('Balance') }} (Income - Expense)</h6>
                    <h4 class="card-title">{{ number_format($data['total_income'] + $data['fees_collected'] - $data['total_expense'], 0) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>{{ __('Today Income') }}</h6>
                    <h5 class="text-success">{{ number_format($data['today_income'], 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6>{{ __('Today Expense') }}</h6>
                    <h5 class="text-danger">{{ number_format($data['today_expense'], 0) }}</h5>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($data['bank_accounts']) && count($data['bank_accounts']) > 0)
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="mb-2">{{ __('Bank account balances') }}</h5>
            <p class="text-muted small">{{ __('Money in from fees/income increases balance; expenses decrease it.') }}</p>
        </div>
        @foreach($data['bank_accounts'] as $account)
        <div class="col-md-4 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body py-3">
                    <h6 class="card-subtitle mb-1 text-muted">{{ $account->bank_name ?? $account->account_name }}</h6>
                    <p class="mb-0 small">{{ $account->account_number }}</p>
                    <h5 class="card-title mt-2 mb-0">{{ number_format((float)($account->balance ?? 0), 0) }}</h5>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ __('Recent Income') }}</h5></div>
                <div class="card-body">
                    @forelse($data['recent_incomes'] as $r)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span>{{ $r->name ?? 'Income' }} - {{ $r->date }}</span>
                            <strong>{{ number_format($r->amount, 0) }}</strong>
                        </div>
                    @empty
                        <p class="text-muted mb-0">{{ ___('common.no_data_available') }}</p>
                    @endforelse
                </div>
                <div class="card-footer">
                    <a href="{{ route('income.index') }}" class="btn btn-sm btn-outline-primary">{{ ___('common.view_all') }}</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ __('Recent Expenses') }}</h5></div>
                <div class="card-body">
                    @forelse($data['recent_expenses'] as $r)
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span>{{ $r->name ?? 'Expense' }} - {{ $r->date }}</span>
                            <strong>{{ number_format($r->amount, 0) }}</strong>
                        </div>
                    @empty
                        <p class="text-muted mb-0">{{ ___('common.no_data_available') }}</p>
                    @endforelse
                </div>
                <div class="card-footer">
                    <a href="{{ route('expense.index') }}" class="btn btn-sm btn-outline-primary">{{ ___('common.view_all') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
