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
                    <h1 class="bradecrumb-title mb-1">{{ $data['title'] }}</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item">{{ $data['title'] }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <!--  table content start -->
        <div class="table-content table-basic mt-20">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $data['title'] }}</h4>
                    @if (hasPermission('department_create'))
                        <a href="{{ route('salary.create') }}" class="btn btn-lg btn-outline-primary">
                            <span><i class="fa-solid fa-plus"></i> </span>
                            <span class="">{{ ___('common.add') }}</span>
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered role-table">
                            <thead class="thead">
                                <tr>
                                    <th class="serial">{{ ___('common.sr_no') }}</th>
                                    <th class="purchase">{{ ___('common.name') }}</th>
                                    <th class="purchase">{{ 'Amount' }}</th>
                                    <th class="purchase">{{ 'Batch Number' }}</th>
                                    <th class="purchase">{{ 'Created At' }}</th>
                                    <th class="purchase">{{ ___('common.status') }}</th>
                                    @if (hasPermission('department_update') || hasPermission('department_delete'))
                                        <th class="action">{{ ___('common.action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="tbody">
                                @forelse ($data['departments'] as $key => $row)
                                <tr id="row_{{ $row->id }}">
                                    <td class="serial">{{ ++$key }}</td>
                                    <td>{{ $row->first_name }} {{ $row->last_name }}</td>
                                    <td>{{ $row->amount }} </td>
                                    <td>{{ $row->batchnumber }} </td>
                                    <td>{{ $row->created_at }} </td>
                                    <td>
                                        @if ($row->payment_status == App\Enums\Status::ACTIVE)
                                            <span class="badge-basic-success-text">{{ 'Paid' }}</span>
                                        @else
                                            <span class="badge-basic-danger-text">{{ 'Not Paid' }}</span>
                                        @endif
                                    </td>
                                    @if (hasPermission('department_update') || hasPermission('department_delete'))
                                        <td class="action">
                                            @if ($row->payment_status != App\Enums\Status::ACTIVE)
                                            <a class="btn btn-outline-primary btn-sm"
                                               href="{{ route('salary.edit', $row->id) }}"><span
                                                        class="icon mr-8"><i
                                                            class="fa-solid fa-pen-to-square"></i></span>
                                                {{ ___('common.update') }}</a>
                                                <a class="btn btn-sm btn-outline-danger" href="javascript:void(0);"
                                                   onclick="delete_row('salary/delete', {{ $row->id }})">
                                                                <span class="icon mr-8"><i
                                                                            class="fa-solid fa-trash-can"></i></span>
                                                    <span>{{ ___('common.delete') }}</span>
                                                </a>
                                            @else
                                                <a class="btn btn-sm btn-outline-warning" href="{{ route('fees-collect.printSalaryReceipt',$row->id) }}">
                                                    {{'Receipt'}}
                                                    <span class="icon mr-8" ><i class="fa-solid fa-receipt"></i></span>
                                                </a>
                                            @endif



                                        </td>
                                    @endif
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
                                    {!!$data['departments']->links() !!}
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
