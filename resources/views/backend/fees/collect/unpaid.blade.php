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
                    <h1 class="bradecrumb-title mb-1">{{ "Unpaid Fees"}}</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item" aria-current="page"><a
                                    href="{{ route('fees-collect.index') }}">{{ "Unpaid Fees" }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ ___('common.details') }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <div class="row">
            <div class="col-12">
                <div class="card ot-card mb-24 position-relative z_1">
                    <form action="{{ route('fees-collect-searchb') }}" enctype="multipart/form-data" method="post" id="fees-collect">
                        @csrf
                        <div class="card-header d-flex align-items-center gap-4 flex-wrap">
                            <h3 class="mb-0">{{ ___('common.Filtering') }}</h3>

                            <div class="card_header_right d-flex align-items-center gap-3 flex-fill justify-content-end flex-wrap">
                                <!-- table_searchBox -->

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

                <div class="card-header d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">{{"Unpaid Fees"}}</h4>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered role-table" id="students_table">
                        <thead class="thead">
                        <tr>
                            <th class="purchase">{{ 'Student Name' }}</th>
                            <th class="purchase">{{ 'Description' }}</th>
                            <th class="purchase">{{ 'Record Date' }}</th>
                            <th class="purchase">{{ ___('common.status') }}</th>
                            <th class="purchase">{{ 'Paid Amount' }}</th>
                            <th class="purchase">{{ 'Remained Amount' }} ({{ Setting('currency_symbol') }})</th>
                            @if (hasPermission('fees_collect_delete'))
                                <th class="purchase">{{ ___('common.action') }}</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody class="tbody">

                        @foreach (@$data['fees_assigned'] as $item)
                            <tr>

                                <td>{{ @$item->first_name }} {{ @$item->last_name }}</td>
                                <td>{{ @$item->feesMaster->type->name }}</td>
                                <td>{{ dateFormat(@$item->transaction_date) }}</td>
                                <td>
                                    @if ($item->remained_amount < 1)
                                        <span class="badge-basic-success-text">{{ ___('fees.Paid') }}</span>
                                    @else
                                        <span class="badge-basic-danger-text">{{ ___('fees.Unpaid') }}</span>
                                    @endif
                                </td>

                                <td>{{ @$item->paid_amount }}</td>
                                <td>
                                    {{ @$item->remained_amount }}
                                </td>

                                @if (hasPermission('fees_collect_delete'))
                                    <td class="action">
                                        <a href="{{ route('fees-collect.collect',$item->student_id) }}"  class="btn btn-sm btn-outline-primary"><span
                                                    class="icon mr-8"><i
                                                        class="fa-solid fa-pen-to-square"></i></span>{{___('fees.Collect')}}</a>
                                        <!-- <a class="btn btn-sm btn-outline-warning" href="{{ route('fees-collect.printTransactionReceipt',$item->fees_collect_id ) }}">

                                            <span class="icon mr-8" ><i class="fa-solid fa-receipt"></i></span>
                                            {{'Receipt'}}
                                        </a> -->
                                    </td>
                                @endif
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
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
