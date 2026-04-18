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

    <div class="table-content table-basic mt-20">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $data['title'] }}</h4>
                @if (hasPermission('account_head_create'))
                    <a href="{{ route('chart-of-accounts.create') }}" class="btn btn-lg btn-outline-primary">
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
                                <th>Code</th>
                                <th>{{ ___('account.type') }}</th>
                                <th>{{ ___('common.status') }}</th>
                                @if (hasPermission('account_head_update') || hasPermission('account_head_delete'))
                                    <th class="action">{{ ___('common.action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="tbody">
                            @forelse ($data['accounts'] as $key => $row)
                            <tr id="row_{{ $row->id }}">
                                <td class="serial">{{ $data['accounts']->firstItem() + $key }}</td>
                                <td>{{ $row->name }}</td>
                                <td>{{ $row->code ?? '-' }}</td>
                                <td>
                                    @if($row->type == 'income')<span class="badge bg-info">{{ __('Income') }}</span>
                                    @elseif($row->type == 'expense')<span class="badge bg-warning text-dark">{{ __('Expense') }}</span>
                                    @elseif($row->type == 'asset')<span class="badge bg-success">{{ __('Asset') }}</span>
                                    @else<span class="badge bg-secondary">{{ __('Liability') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($row->status == 1)
                                        <span class="badge-basic-success-text">{{ ___('common.active') }}</span>
                                    @else
                                        <span class="badge-basic-danger-text">{{ ___('common.inactive') }}</span>
                                    @endif
                                </td>
                                @if (hasPermission('account_head_update') || hasPermission('account_head_delete'))
                                    <td class="action">
                                        @if(hasPermission('account_head_update'))
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('chart-of-accounts.edit', $row->id) }}">
                                                <i class="fa-solid fa-pen-to-square"></i> {{ ___('common.edit') }}
                                            </a>
                                        @endif
                                        @if(hasPermission('account_head_delete'))
                                            <a class="btn btn-sm btn-outline-danger" href="javascript:void(0);" onclick="delete_row('chart-of-accounts/delete', {{ $row->id }})">
                                                <i class="fa-solid fa-trash-can"></i> {{ ___('common.delete') }}
                                            </a>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="100%" class="text-center gray-color">
                                    <p class="mb-0">{{ ___('common.no_data_available') }}</p>
                                    <p class="mb-0 text-secondary">{{ __('Run the Accounting seeder to add default Chart of Accounts.') }}</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="ot-pagination pagination-content d-flex justify-content-end py-3">
                    {!! $data['accounts']->links() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('script')@include('backend.partials.delete-ajax')@endpush
