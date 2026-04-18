@extends('mainapp::layouts.backend.master')
@section('title')
    {{ @$data['title'] }}
@endsection
@section('content')
    <div class="page-content">

        {{-- bradecrumb Area S t a r t --}}
        <div class="page-header">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="bradecrumb-title mb-1">{{ $data['title'] }}</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item">{{ ___('common.Subscription List') }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <!--  table content start -->
        <div class="table-content table-basic mt-20">
            <div class="card">
       
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ ___('common.Subscription List') }}</h4>
                    <a href="{{ route('subscription.create') }}" class="btn btn-lg ot-btn-primary">
                        <span><i class="fa-solid fa-plus"></i> </span>
                        <span class="">{{ ___('common.add') }}</span>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered subscription-table">
                            <thead class="thead">
                                <tr>
                                    <th class="serial">{{ ___('common.sr_no') }}</th>
                                    <th class="purchase">{{ ___('common.Sub domain key') }}</th>
                                    <th class="purchase">{{ ___('common.Package') }}</th>
                                    <th class="purchase">{{ ___('common.Price') }}</th>
                                    <th class="purchase">{{ ___('common.Purchase Date') }}</th>
                                    <th class="purchase">{{ ___('common.Date of Expire') }}</th>
                                    <th class="purchase">{{ ___('common.Trx ID') }}</th>
                                    <th class="purchase">{{ ___('common.Method') }}</th>
                                    <th class="purchase">{{ ___('common.status') }}</th>
                                    <th class="purchase">{{ ___('common.Payment status') }}</th>
                                    <th class="action">{{ ___('common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="tbody">
                                @forelse ($data['subscriptions'] as $key => $row)
                                <tr id="row_{{ $row->id }}">
                                    <td class="serial">{{ ++$key }}</td>
                                    <td>{{ @$row->school->sub_domain_key }}</td>
                                    <td>{{ $row->package->name }}</td>
                                    <td>{{ $row->package->price }}</td>
                                    <td>{{ dateFormat(@$row->created_at) }}</td>
                                    <td>{{ $row->expiry_date ? dateFormat(@$row->expiry_date) : ___('common.Lifetime') }}</td>
                                    <td>{{ $row->trx_id }}</td>
                                    <td>{{ $row->method }}</td>
                                    <td>
                                        @if ($row->status == App\Enums\SubscriptionStatus::APPROVED)
                                            <span class="badge-basic-success-text">{{ ___('common.Approved') }}</span>
                                        @elseif ($row->status == App\Enums\SubscriptionStatus::REJECT)
                                            <span class="badge-basic-danger-text">{{ ___('common.Reject') }}</span>
                                        @else
                                            <span class="badge-basic-warning-text">{{ ___('common.Pending') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row->payment_status == 1)
                                            <span class="badge-basic-success-text">{{ ___('common.Paid') }}</span>
                                        @else
                                            <span class="badge-basic-danger-text">{{ ___('common.Unpaid') }}</span>
                                        @endif
                                    </td>
                                    
                                    <td class="action">
                                        @if ($row->status != App\Enums\SubscriptionStatus::REJECT)
                                            <div class="dropdown dropdown-action">
                                                <button type="button" class="btn-dropdown" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="fa-solid fa-ellipsis"></i>
                                                </button>

                                                <ul class="dropdown-menu dropdown-menu-end ">
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('subscription.edit', $row->id) }}"><span
                                                                    class="icon mr-8"><i
                                                                        class="fa-solid fa-edit"></i></span>
                                                                {{ ___('common.edit') }}</a>
                                                        </li>
                                                    {{--@if ($row->status == App\Enums\SubscriptionStatus::PENDING)
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ route('subscription.reject', $row->id) }}"><span
                                                                    class="icon mr-8"><i
                                                                        class="fa-solid fa-pen-to-square"></i></span>
                                                                {{ ___('common.Reject') }}</a>
                                                        </li>
                                                    @endif --}}
                                                    {{-- <li>
                                                        <a class="dropdown-item" href="javascript:void(0);"
                                                            onclick="delete_row('subscription/delete', {{ $row->id }})">
                                                            <span class="icon mr-8"><i
                                                                    class="fa-solid fa-trash-can"></i></span>
                                                            <span>{{ ___('common.delete') }}</span>
                                                        </a>
                                                    </li> --}}
                                                </ul>
                                            </div>
                                        @endif
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
                                    {!!$data['subscriptions']->links() !!}
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
