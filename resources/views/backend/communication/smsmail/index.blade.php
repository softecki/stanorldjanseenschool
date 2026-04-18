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
                    @if (hasPermission('sms_mail_send'))
                        <a href="{{ route('smsmail.create') }}" class="btn btn-md btn-outline-primary">
                            <span><i class="fa-solid fa-plus"></i> </span>
                            <span class="">{{ ___('common.add') }}</span>
                        </a>
                    @endif
                    <a href="{{ route('template.delivery') }}" class="btn btn-md btn-outline-success">
                        <span><i class="fa-solid fa-comments"></i> </span>
                        <span class="">Check Delivery</span>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered role-table sms-mail">
                            <thead class="thead">
                                <tr>
                                    <th class="serial">{{ ___('common.sr_no') }}</th>
                                    <th class="serial">Phone Number</th>
                                    <th class="purchase">Message  </th>
                                    <th class="purchase">Status  </th>
                                    <th class="purchase">Group Name</th>
                                    <th class="purchase">{{ ___('account.description') }}</th>
                                </tr>
                            </thead>
                            <tbody class="tbody">
                                @forelse ($data['smsmail'] ?? [] as $key => $row)
                                <tr id="row_{{ $row->id }}">
                                    <td class="serial">{{ ++$key }}</td>
                                    <td>{{ $row->to }}</td>
                                    <td>{{ $row->sms }}</td>
                                    <td>{{ $row->status_groupName }}</td>

                                    <td>
                                        {{$row->status_name}}
                                    </td>

                                    <td>
                                       {{ @$row->status_description }}
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
                                    {!!$data['smsmail']->appends(\Request::capture()->except('page'))->links() !!}
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
    <script src="{{ global_asset('backend') }}/assets/js/__sms_mail.js"></script>
@endpush
