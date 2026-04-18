@extends('backend.master')
@section('title')
    {{ $data['title'] ?? 'Transactions' }}
@endsection
@section('content')
    <div class="page-content">

        <div class="page-header">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="bradecrumb-title mb-1">{{ $data['title'] ?? 'Transactions' }}</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item">{{ $data['title'] ?? 'Transactions' }}</li>
                    </ol>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('danger'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('danger') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-content table-basic mt-20">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $data['title'] ?? 'Transactions' }}</h4>
                    @if (hasPermission('expense_create'))
                        <a href="{{ route('transactions.create') }}" class="btn btn-md btn-outline-primary">
                            <span><i class="fa-solid fa-plus"></i></span>
                            <span>{{ ___('common.add') }}</span>
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered class-table">
                            <thead class="thead">
                                <tr>
                                    <th class="serial">{{ ___('common.sr_no') }}</th>
                                    <th class="purchase">{{ ___('common.name') }}</th>
                                    <th class="purchase">{{ ___('common.description') }}</th>
                                    <th class="purchase">{{ ___('account.expense_head') }}</th>
                                    <th class="purchase">{{ ___('account.date') }}</th>
                                    <th class="purchase">{{ 'Receivers Name' }}</th>
                                    <th class="purchase">{{ ___('account.amount') }} ({{ Setting('currency_symbol') }})</th>
                                    <th class="purchase">{{ 'Status' }}</th>
                                    @if (hasPermission('expense_update') || hasPermission('expense_delete'))
                                        <th class="action">{{ ___('common.action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="tbody">
                                @forelse ($data['expense'] as $key => $row)
                                    <tr id="row_{{ $row->id }}">
                                        <td class="serial">{{ $data['expense']->firstItem() + $key }}</td>
                                        <td>{{ @$row->name }}</td>
                                        <td>{{ @$row->description }}</td>
                                        <td>{{ @$row->head->name }}</td>
                                        <td>{{ dateFormat(@$row->date) }}</td>
                                        <td>{{ @$row->invoice_number }}</td>
                                        <td>{{ number_format(@$row->amount) }}</td>
                                        <td>{{ @$row->status_name }}</td>
                                        @if (hasPermission('expense_update') || hasPermission('expense_delete'))
                                            <td class="action">
                                                @if (hasPermission('expense_update'))
                                                    <a class="btn btn-sm btn-outline-primary"
                                                       href="{{ route('transactions.edit', $row->id) }}"><span
                                                            class="icon mr-8"><i
                                                                class="fa-solid fa-pen-to-square"></i></span>
                                                        {{ ___('common.edit') }}</a>
                                                @endif
                                                @if (hasPermission('expense_delete'))
                                                    <a class="btn btn-sm btn-outline-danger" href="javascript:void(0);"
                                                       onclick="delete_row('transactions/delete', {{ $row->id }})">
                                                        <span class="icon mr-8"><i
                                                                class="fa-solid fa-trash-can"></i></span>
                                                        <span>{{ ___('common.delete') }}</span>
                                                    </a>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%" class="text-center gray-color">
                                            <img src="{{ asset('images/no_data.svg') }}" alt="" class="mb-primary"
                                                 width="100">
                                            <p class="mb-0 text-center">{{ ___('common.no_data_available') }}</p>
                                            <p class="mb-0 text-center text-secondary font-size-90">
                                                {{ ___('common.please_add_new_entity_regarding_this_table') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($data['expense']->hasPages())
                        <div class="ot-pagination pagination-content d-flex justify-content-end align-content-center py-3">
                            <nav aria-label="Page navigation example">
                                <ul class="pagination justify-content-between">
                                    {!! $data['expense']->links() !!}
                                </ul>
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
@endsection

@push('script')
    @include('backend.partials.delete-ajax')
@endpush
