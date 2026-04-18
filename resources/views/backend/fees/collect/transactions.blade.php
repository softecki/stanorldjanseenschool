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
                    <h4 class="bradecrumb-title mb-1">{{ $data['title'] }}</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item" aria-current="page"><a
                                    href="{{ route('fees-collect.index') }}">{{ $data['title'] }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ ___('common.details') }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <div class="row">
            <div class="col-12">
                <div class="card ot-card mb-2 position-relative z_1">
                    <form action="{{ route('fees-collect-searcha') }}" enctype="multipart/form-data" method="post" id="fees-collect-searcha">
                        @csrf
                        <div class="card-header d-flex align-items-center gap-4 flex-wrap">
                            <h3 class="mb-0">{{ ___('common.Filtering') }}</h3>

                            <div class="card_header_right d-flex align-items-center gap-3 flex-fill justify-content-end flex-wrap">
                                <!-- table_searchBox -->

                                <div class="single_selectBox">
                                    <input name="start_date" type="date" class="form-control" placeholder="{{'Start Date'}} " aria-label="Search " aria-describedby="searchIcon">
                                </div>
                                <div class="single_selectBox">
                                    <input name="start_date" type="date" class="form-control" placeholder="{{'End Date'}} " aria-label="Search " aria-describedby="searchIcon">
                                </div>

                                <div class="input-group table_searchBox">
                                    <input name="name" type="text" class="form-control" placeholder="{{___('common.name')}} " aria-label="Search " aria-describedby="searchIcon">
                                    <span class="input-group-text" id="searchIcon">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                </div>
                                <button class="btn btn-md btn-outline-primary">
                                    {{ ___('common.Search')}}
                                </button>
                            </div>


                        </div>
                    </form>
                </div>
            </div>
        </div>


        <div class="card ot-card">
            <div class="card-body">
            <form action="{{ route('fees-collect.print-receipt') }}" enctype="multipart/form-data" method="post" id="fees-collect-print">
            @csrf
            <div class="card-header d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">{{___('fees.fees_details')}}</h4>
                    <button class="btn btn-md btn-outline-primary">Download</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered role-table" id="students_table">
                        <thead class="thead">
                        <tr>
                            <th></th>
                        <th class="purchase">{{ 'Recept No.' }}</th>
                            <th class="purchase">{{ 'Student Name' }}</th>
                            <th class="purchase">{{ ___('fees.type') }}</th>
                            <th class="purchase">{{ ' Date' }}</th>
                            <th class="purchase">{{ ' Amount' }} ({{ Setting('currency_symbol') }})</th>
                            <th class="purchase">{{ ___('common.status') }}</th>
                            <th class="purchase">{{ 'Bank Name' }}</th>
                           
                            @if (hasPermission('fees_collect_delete'))
                                <th class="purchase">{{ ___('common.action') }}</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody class="tbody">

                        @foreach (@$data['fees_assigned'] as $item)
                            <tr>
                            <td>
                                        <input class="form-check-input child" type="checkbox" name="fees_assign_ids[]" value="{{ $item->fees_collect_id }}" >
                                    </td>
                            <td>{{ $item->comments }}</td>
                                <td>{{ @$item->first_name }} {{ @$item->last_name }}</td>
                                <td>{{ @$item->fees_type_name }}</td>
                                <td>{{ dateFormat(@$item->transaction_date) }}</td>
                                <td>{{ @$item->transaction_amount }}</td>
                                <td>
                                        <span class="badge-basic-success-text">{{ ___('fees.Paid') }}</span>

                                </td>
                                <td>
                                    {{ @$item->bank_name }}({{ @$item->account_number }})
                                </td>
                             

                                @if (hasPermission('fees_collect_delete'))
                                    <td class="action">
                                        <a title="Cancel" class="btn btn-sm btn-outline-danger" href="javascript:void(0);"
                                           onclick="delete_row('fees-collect/delete', {{ $item->fees_collect_id }}, true)">
                                                                <span class="icon mr-8"><i
                                                                            class="fa-solid fa-trash-can"></i></span>
                                          
                                        </a>
                                        @if ($item->printed < 1)
                                        <a title="Receipt" class="btn btn-sm btn-outline-warning" href="{{ route('fees-collect.printTransactionReceipt',$item->fees_collect_id ) }}">
                                            <span class="icon mr-8" ><i class="fa-solid fa-receipt"></i></span>
                                        </a>
                                        @else 
                                        <a title="Receipt" class="btn btn-sm btn-outline-success" href="{{ route('fees-collect.printTransactionReceipt',$item->fees_collect_id ) }}">
                                            <span class="icon mr-8" ><i class="fa-solid fa-receipt"></i></span>
                                        </a>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
                </form>
                
                <!-- Pagination -->
                @if(method_exists($data['fees_assigned'], 'links'))
                <div class="ot-pagination pagination-content d-flex justify-content-end align-content-center py-3">
                    <nav aria-label="Page navigation example">
                        <ul class="pagination justify-content-between">
                            {!! $data['fees_assigned']->appends(\Request::capture()->except('page'))->links() !!}
                        </ul>
                    </nav>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div id="view-modal">
        <div class="modal fade" id="modalCustomizeWidth" tabindex="-1" aria-labelledby="modalWidth"
             aria-hidden="true">
            <div class="modal-dialog modal-xl">
                {{--  --}}
            </div>
        </div>
    </div>
    <script>
        function selectOnlyOne(checkbox) {
            const checkboxes = document.querySelectorAll('.form-check-input.child');
            checkboxes.forEach((cb) => {
                if (cb !== checkbox) {
                    cb.checked = false;
                }
            });
        }
    </script>
@endsection
@push('script')
    @include('backend.partials.delete-ajax')
@endpush
