@extends('backend.master')

@section('title')
    {{ ___('common.details') }} — Push Transaction
@endsection

@section('content')
    <div class="page-content">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h4 class="bradecrumb-title mb-1">{{ ___('common.details') }} — Push Transaction</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('fees-collect.index') }}">{{ ___('fees.fees_collect') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('fees-collect.collect-transactions') }}">Collect Transactions</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ ___('common.details') }}</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="card ot-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <a href="{{ route('fees-collect.collect-transactions') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i> {{ ___('common.back') }}
                    </a>
                    @if (hasPermission('fees_collect_delete'))
                        <form action="{{ route('fees-collect.cancel-push-transaction', $push->id) }}" method="post" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fa-solid fa-trash-can me-1"></i> {{ ___('common.delete') }} / Cancel Transaction
                            </button>
                        </form>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tbody>
                            <tr><th class="table-light" style="width: 40%;">ID</th><td>{{ $push->id }}</td></tr>
                            <tr><th class="table-light">Reference</th><td>{{ $push->reference ?? '—' }}</td></tr>
                            <tr><th class="table-light">Phone</th><td>{{ $push->phone ?? '—' }}</td></tr>
                            <tr><th class="table-light">Sender account</th><td>{{ $push->sender_account ?? '—' }}</td></tr>
                            <tr><th class="table-light">Amount ({{ Setting('currency_symbol') }})</th><td>{{ number_format($push->amount ?? 0, 2, '.', ',') }}</td></tr>
                            <tr><th class="table-light">Service</th><td>{{ $push->service ?? '—' }}</td></tr>
                            <tr><th class="table-light">Control number</th><td>{{ $push->control_number ?? '—' }}</td></tr>
                            <tr><th class="table-light">Payment status</th><td>{{ $push->payment_status ?? '—' }}</td></tr>
                            <tr><th class="table-light">Payment receipt</th><td>{{ $push->payment_receipt ?? '—' }}</td></tr>
                            <tr><th class="table-light">Payment date</th><td>{{ isset($push->payment_date) && $push->payment_date ? dateFormat($push->payment_date) : '—' }}</td></tr>
                            <tr><th class="table-light">Settlement status</th><td>{{ $push->settlement_status ?? '—' }}</td></tr>
                            <tr><th class="table-light">Settlement receipt</th><td>{{ $push->settlement_receipt ?? '—' }}</td></tr>
                            <tr><th class="table-light">Settlement date</th><td>{{ isset($push->settlement_date) && $push->settlement_date ? dateFormat($push->settlement_date) : '—' }}</td></tr>
                            <tr><th class="table-light">Fees assign children ID</th><td>{{ $push->fees_assign_children_id ?? '—' }}</td></tr>
                            <tr><th class="table-light">Account ID</th><td>{{ $push->account_id ?? '—' }}</td></tr>
                            <tr><th class="table-light">Is processed</th><td>{{ isset($push->is_processed) ? ($push->is_processed ? 'Yes' : 'No') : '—' }}</td></tr>
                            <tr><th class="table-light">Created at</th><td>{{ isset($push->created_at) && $push->created_at ? dateFormat($push->created_at) : '—' }}</td></tr>
                            <tr><th class="table-light">Updated at</th><td>{{ isset($push->updated_at) && $push->updated_at ? dateFormat($push->updated_at) : '—' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
