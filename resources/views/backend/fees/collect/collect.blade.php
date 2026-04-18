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
                            <li class="breadcrumb-item" aria-current="page"><a
                                    href="{{ route('fees-collect.index') }}">{{ $data['title'] }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ ___('common.details') }}</li>
                        </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <div class="card ot-card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <div class="d-flex justify-content-between align-content-center mb-3">
                            <div class="align-self-center">
                                <h5 class="title">{{ ___('student_info.admission_no') }}</h5>
                                <p class="paragraph">{{ @$data['student']->admission_no }}</p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-content-center mb-3">
                            <div class="align-self-center">
                                <h5 class="title">{{ ___('student_info.student_name') }}</h5>
                                <p class="paragraph">{{ @$data['student']->first_name }}
                                    {{ @$data['student']->last_name }}</p>
                                <input type="hidden" name="student_id" id="student_id"
                                    value="{{ $data['student']->id }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="d-flex justify-content-between align-content-center mb-3">
                            <div class="align-self-center">
                                <h5 class="title">{{ ___('academic.class') }}</h5>
                                <p class="paragraph">{{ @$data['student']->sessionStudentDetails->class->name }}</p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-content-center mb-3">
                            <div class="align-self-center">
                                <h5 class="title">{{ ___('academic.section') }}</h5>
                                <p class="paragraph">{{ @$data['student']->sessionStudentDetails->section->name }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="d-flex justify-content-between align-content-center mb-3">
                            <div class="align-self-center">
                                <h5 class="title">{{ ___('student_info.guardian_name') }}</h5>
                                <p class="paragraph">{{ @$data['student']->parent->guardian_name }}</p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-content-center mb-3">
                            <div class="align-self-center">
                                <h5 class="title">{{ ___('student_info.mobile_number') }}</h5>
                                <p class="paragraph">{{ @$data['student']->mobile }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-header d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">{{___('fees.fees_details')}}</h4>
                    @if (hasPermission('fees_collect_create'))
                        <a href="#" class="btn btn-lg btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#modalCustomizeWidth" onclick="feesCollect()">
                            <span><i class="fa-solid fa-plus"></i> </span>
                            <span class="">{{ ___('fees.Collect') }}</span>
                        </a>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered role-table" id="students_table">
                        <thead class="thead">
                            <tr>
                                <th class="purchase mr-4">{{ ___('common.All') }}</th>
                                <th class="purchase">{{ ___('fees.group') }}</th>
                                <th class="purchase">{{ ___('fees.type') }}</th>
                                <th class="purchase">{{ 'Fees Amount'}} ({{ Setting('currency_symbol') }})</th>
                                
                                <th class="purchase">{{ 'Paid Amount' }}</th>
                                <th class="purchase">{{ 'Remained Amount' }} ({{ Setting('currency_symbol') }})</th>
                                <th class="purchase">{{ ___('common.status') }}</th>
                                @if (hasPermission('fees_collect_delete'))
                                    <th class="purchase">{{ ___('common.action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="tbody">
                           @php $firstCheckedSet = false; @endphp

                            @foreach (@$data['fees_assigned'] as $item)
                              @php
                                    $shouldCheck = false;
                                    if (!$firstCheckedSet && $item->remained_amount > 1) {
                                        $shouldCheck = true;
                                        $firstCheckedSet = true;
                                    }
                                @endphp
                                <tr>
                                    <td><input class="form-check-input child" type="checkbox" name="fees_assign_childrens[]"
                   value="{{ $item->id }}" onclick="selectOnlyOne(this)"
                   {{ $shouldCheck ? 'checked' : '' }}></td>
                                    <td>{{ @$item->feesMaster->group->name }}</td>
                                    <td>{{ @$item->feesMaster->type->name }}</td>
                                    <td>{{ @$item->fees_amount }}
                                    </td>
                                 
                                  
                                    <td>{{ @$item->paid_amount }}</td>
                                    <td>
                                        {{ @$item->remained_amount }}
                                    </td>
                                       <td>
                                        @if ($item->remained_amount < 1)
                                            <span class="badge-basic-success-text">{{ ___('fees.Paid') }}</span>
                                        @else
                                            <span class="badge-basic-danger-text">{{ ___('fees.Unpaid') }}</span>
                                        @endif
                                    </td>
                                    
                                    @if (hasPermission('fees_collect_delete'))
                                        <td class="action">
                                            @if ($item->remained_amount > 1)
                                            <a title="Amendment" href="{{ route('fees-collect.amendment', $item->id ) }}"  class="btn btn-sm btn-outline-primary"><span
                                                        class="icon mr-1"><i
                                                            class="fa-solid fa-rotate-right"></i></span></a>
                                            @endif
                                            {{-- <div class="dropdown dropdown-action">
                                                <button type="button" class="btn-dropdown" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="fa-solid fa-ellipsis"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end ">
                                                     @if ($item->fees_collect_count) 
                                                        <li>
                                                            <a class="dropdown-item" href="javascript:void(0);"
                                                                onclick="delete_row('fees-collect/delete', {{ @$item->feesCollect->id }}, true)">
                                                                <span class="icon mr-8"><i
                                                                        class="fa-solid fa-trash-can"></i></span>
                                                                <span>{{ ___('common.Revert Payment') }}</span>
                                                            </a>
                                                        </li>
                                                     @endif 
                                                </ul>
                                            </div> --}}
                                        </td>
                                    @endif
                                </tr>
                            @endforeach

                        </tbody>
                    </table>

                     <table class="table table-bordered role-table" id="students_table">
                        <thead class="thead">
                            <tr>
                                <th class="purchase">{{ ___('fees.type') }}</th>
                                <th class="purchase">{{ 'Due Term One' }}</th>
                                <th class="purchase">{{ 'Paid Term One' }}</th>
                                <th class="purchase">{{ 'Fees Term Two'}} </th>
                                <th class="purchase">{{ 'Due Term Two' }}</th>
                                <th class="purchase">{{ 'Paid Term Two' }}</th>
                                <th class="purchase">{{ 'Remained Amount' }} </th>                              
                            </tr>
                        </thead>
                        <tbody class="tbody">

                            @foreach (@$data['fees_assigned'] as $item)
                            @if (@$item->feesMaster->group->name == "Outstanding Balance")
                             <tr>
                                    
                                    <td>{{ @$item->feesMaster->type->name }}</td>
                                    <td></td>
                                    <td>{{ @$item->fees_amount }}</td>
                                    <td>{{ @$item->quater_three + @$item->quater_four }}</td>
                                    <td>{{ @$item->paid_amount }}</td>
                                    <td>{{ @$item->remained_amount }}</td>
                                </tr>
                            @endif
                             @if (@$item->feesMaster->group->name != "Outstanding Balance")
                                 
                            
                                <tr>
                                    
                                    <td>{{ @$item->feesMaster->type->name }}</td>
                                    <td>{{ @$item->quater_one + @$item->quater_two }}</td>
                                    <td>{{ (@$item->fees_amount/2) - (@$item->quater_one + @$item->quater_two) }}</td>
                                    <td>{{ @$item->fees_amount/2 }}</td>
                                        <td>{{ @$item->quater_three + @$item->quater_four }}
                                    </td>
                                    <td>{{ (@$item->fees_amount/2) - (@$item->quater_three + @$item->quater_four)}}</td>
                                    
                                    <td>{{ @$item->remained_amount  }}
                                    </td>
                                </tr>
                                 @endif
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
