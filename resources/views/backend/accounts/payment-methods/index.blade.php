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
                    <a href="{{ route('payment-methods.create') }}" class="btn btn-lg btn-outline-primary">
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
                                <th>{{ ___('common.description') }}</th>
                                <th>{{ ___('common.status') }}</th>
                                @if (hasPermission('account_head_update') || hasPermission('account_head_delete'))
                                    <th class="action">{{ ___('common.action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="tbody">
                            @forelse ($data['methods'] as $key => $row)
                            <tr id="row_{{ $row->id }}">
                                <td class="serial">{{ $data['methods']->firstItem() + $key }}</td>
                                <td>{{ $row->name }}</td>
                                <td>{{ $row->description ?? '-' }}</td>
                                <td>
                                    @if($row->is_active)
                                        <span class="badge-basic-success-text">{{ ___('common.active') }}</span>
                                    @else
                                        <span class="badge-basic-danger-text">{{ ___('common.inactive') }}</span>
                                    @endif
                                </td>
                                @if (hasPermission('account_head_update') || hasPermission('account_head_delete'))
                                    <td class="action">
                                        @if(hasPermission('account_head_update'))
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('payment-methods.edit', $row->id) }}"><i class="fa-solid fa-pen-to-square"></i> {{ ___('common.edit') }}</a>
                                        @endif
                                        @if(hasPermission('account_head_delete'))
                                            <a class="btn btn-sm btn-outline-danger" href="javascript:void(0);" onclick="delete_row('payment-methods/delete', {{ $row->id }})"><i class="fa-solid fa-trash-can"></i> {{ ___('common.delete') }}</a>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="100%" class="text-center gray-color">{{ ___('common.no_data_available') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="ot-pagination pagination-content d-flex justify-content-end py-3">
                    {!! $data['methods']->links() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('script')@include('backend.partials.delete-ajax')@endpush
