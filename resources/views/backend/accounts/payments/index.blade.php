@extends('backend.master')
@section('title')
    {{ @$data['title'] }}
@endsection
@section('content')
    <div class="page-content">

        {{-- bradecrumb Area S t a r t --}}
        <div class="page-header">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="bradecrumb-title mb-1">Payments</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item">Payments</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <!--  table content start -->
        <div class="table-content table-basic mt-20">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Payments</h4>
                    @if (hasPermission('expense_create'))
                        <a href="{{ route('expense.create') }}" class="btn btn-lg btn-outline-primary">
                            <span><i class="fa-solid fa-plus"></i> </span>
                            <span class="">{{ ___('common.add') }}</span>
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered class-table">
                            <thead class="thead">
                                <tr>
                                    <th class="serial">{{ 'Payment ID' }}</th>
                                    <th class="serial">{{ 'Destination Account' }}</th>
                                    <th class="purchase">{{ 'Date & Time' }}</th>
                                    <th class="purchase">{{ 'Payee Details' }}</th>
                                    <th class="purchase">{{ 'Amount' }}</th>
                                    <th class="purchase">{{ 'Purpose' }}</th>
                                    <th class="purchase">{{ 'Source Account' }} </th>
                                    <th class="purchase">{{ 'Transaction Reference' }}</th>
                                    @if (hasPermission('expense_update') || hasPermission('expense_delete'))
                                        <th class="action">{{ ___('common.action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="tbody">
                                @forelse ($data['expense'] as $key => $row)
                                <tr id="row_{{ $row->id }}">
                                    <td class="serial">000078{{ ++$key }}</td>
                                    <td class="serial">000088{{ ++$key }} NMB Bank</td>
                                    <td>{{ dateFormat(@$row->date) }}</td>
                                    <td>MonaBan Food Supplier</td>
                                    <td>376,000/= TZS</td>
                                    <td>Food Payments</td>
                                    <td>0847754 CRDB Bank</td>
                                    <td>
                                        0430434344
                                        @if (@$row->upload->path)
                                            <a href="{{ @globalAsset(@$row->upload->path) }}" download>{{ ___('common.download') }}</a>
                                        @endif
                                    </td>
                                        <td class="action">
                                            <a class="btn btn-outline-primary"
                                               href="{{ route('expense.edit', $row->id) }}"><span
                                                        class="icon mr-8"><i
                                                            class="fa-solid fa-pen-to-square"></i></span>
                                                {{ ___('common.edit') }}</a>

                                            <a class="btn btn-outline-danger" href="javascript:void(0);"
                                               onclick="delete_row('expense/delete', {{ $row->id }})">
                                                                <span class="icon mr-8"><i
                                                                            class="fa-solid fa-trash-can"></i></span>
                                                <span>{{ ___('common.delete') }}</span>
                                            </a>


                                        </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="100%" class="text-center gray-color">
                                        <img src="{{ asset('images/no_data.svg') }}" alt="" class="mb-primary" width="100">
                                        <p class="mb-0 text-center">{{ ___('common.no_data_available') }}</p>
                                        <p class="mb-0 text-center text-secondary font-size-90">
                                            {{ ___('common.please_add_new_entity_regarding_this_table') }}</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!--  table end -->
                    <!--  pagination start -->

                        <div class="ot-pagination pagination-content d-flex justify-content-end align-content-center py-3">
                            <nav aria-label="Page navigation example">
                                <ul class="pagination justify-content-between">
                                    {!!$data['expense']->links() !!}
                                </ul>
                            </nav>
                        </div>

                    <!--  pagination end -->
                </div>
            </div>
        </div>
        <!--  table content end -->

    </div>
@endsection

@push('script')
    @include('backend.partials.delete-ajax')
@endpush
