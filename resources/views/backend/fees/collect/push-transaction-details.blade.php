<div class="push-transaction-details" data-push-id="{{ $push->id }}">
    @if (hasPermission('fees_collect_delete'))
    <div class="mb-3 d-flex justify-content-end">
        <button type="button" class="btn btn-sm btn-outline-danger btn-cancel-push-transaction" data-push-id="{{ $push->id }}">
            <i class="fa-solid fa-trash-can me-1"></i> {{ ___('common.delete') }} / Cancel Transaction
        </button>
    </div>
    @endif
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
