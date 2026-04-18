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

        
        <div class="col-12">
            <form action="{{ route('parent.search') }}" method="post" id="marksheed" enctype="multipart/form-data">
                @csrf
                <div class="card ot-card mb-24 position-relative z_1">
                    <div class="card-header d-flex align-items-center gap-4 flex-wrap">
                        <h3 class="mb-0">{{ ___('common.Filtering') }}</h3>
                        
                        <div class="card_header_right d-flex align-items-center gap-3 flex-fill justify-content-end flex-wrap">
                            
                            <div class="single_large_selectBox">
                                <input class="form-control ot-input"
                                    name="keyword" list="datalistOptions" id="exampleDataList"
                                    placeholder="{{ ___('student_info.enter_keyword') }}"
                                    value="{{ old('keyword', @$data['request']->keyword) }}">
                            </div>

                            <button class="btn btn-lg btn-outline-primary" type="submit">
                                {{___('common.Search')}}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>


        
        <!--  table content start -->
        <div class="table-content table-basic mt-20">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $data['title'] }}</h4>
                    @if (hasPermission('parent_create'))
                        <a href="{{ route('parent.create') }}" class="btn btn-lg btn-outline-primary">
                            <span><i class="fa-solid fa-plus"></i> </span>
                            <span class="">{{ ___('common.add') }}</span>
                        </a>
                    @endif
                </div>
                @if (@$data['parents'])
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered role-table">
                            <thead class="thead">
                                <tr>
                                    <th class="serial">{{ ___('common.sr_no') }}</th>
                                    <th class="purchase">{{ ___('common.name') }}</th>
                                    <th class="purchase">{{ ___('common.phone') }}</th>
                                    <th class="purchase">{{ ___('common.email') }}</th>
                                    <th class="purchase">{{ ___('common.address') }}</th>
                                    <th class="purchase">{{ ___('common.status') }}</th>
                                    @if (hasPermission('parent_update') || hasPermission('parent_delete'))
                                        <th class="action">{{ ___('common.action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="tbody">
                                @forelse ($data['parents'] as $key => $row)
                                <tr id="row_{{ $row->id }}">
                                    <td class="serial">{{ ++$key }}</td>
                                    <td>{{ $row->user->name }}</td>
                                    <td>{{ $row->user->phone }}</td>
                                    <td>{{ $row->user->email }}</td>
                                    <td>{{ $row->guardian_address }}</td>
                                    <td>
                                        @if ($row->status == App\Enums\Status::ACTIVE)
                                            <span class="badge-basic-success-text">{{ ___('common.active') }}</span>
                                        @else
                                            <span class="badge-basic-danger-text">{{ ___('common.inactive') }}</span>
                                        @endif
                                    </td>
                                    @if (hasPermission('parent_update') || hasPermission('parent_delete'))
                                        <td class="action">

{{--                                            <a class="btn btn-sm btn-outline-warning" href="{{ route('parent.show',@$row->id) }}">--}}
{{--                                                                <span class="icon mr-8"><i--}}
{{--                                                                            class="fa-solid fa-eye"></i></span>--}}
{{--                                                <span>{{ ___('common.show') }}</span>--}}
{{--                                            </a>--}}

                                            <a class="btn btn-sm btn-outline-primary"
                                               href="{{ route('parent.edit', $row->id) }}"><span
                                                        class="icon mr-8"><i
                                                            class="fa-solid fa-pen-to-square"></i></span>
                                                {{ ___('common.edit') }}</a>

                                            <a class="btn btn-sm btn-outline-danger" href="javascript:void(0);"
                                               onclick="delete_row('parent/delete', {{ $row->id }})">
                                                                <span class="icon mr-8"><i
                                                                            class="fa-solid fa-trash-can"></i></span>
                                                <span>{{ ___('common.delete') }}</span>
                                            </a>

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
                                    {!!$data['parents']->appends(\Request::capture()->except('page'))->links() !!}
                                </ul>
                            </nav>
                        </div>

                    <!--  pagination end -->
                </div>
                @else
                <div class="text-center gray-color p-5">
                    <img src="{{ asset('images/no_data.svg') }}" alt="" class="mb-primary" width="100">
                    <p class="mb-0 text-center">{{ ___('common.no_data_available') }}</p>
                    <p class="mb-0 text-center text-secondary font-size-90">
                        {{ ___('common.please_add_new_entity_regarding_this_table') }}</p>
                </div>
                @endif
            </div>
        </div>
        <!--  table content end -->

    </div>
@endsection

@push('script')
    @include('backend.partials.delete-ajax')
@endpush
