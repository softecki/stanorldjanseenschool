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
                    <h4 class="bradecrumb-title mb-1">{{ $data['title'] }}</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> {{ ___('common.home') }} </a></li>
                        <li class="breadcrumb-item"><a href="{{ route('religions.index') }}">{{ $data['title'] }}</a></li>
                        <li class="breadcrumb-item">{{ ___('common.edit') }}</li>

                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <div class="card ot-card">
            <div class="card-body">
                <form action="{{ route('fees-assign.update', @$data['fees_assign']->id) }}" enctype="multipart/form-data" method="post"
                    id="visitForm">
                    @csrf
                    @method('PUT')
                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div>
                                        <label for="validationServer04" class="form-label">{{ ___('fees.fees_group') }} <span class="fillable">*</span></label>
                                        <select id="fees_group" class="nice-select niceSelect bordered_style wide @error('fees_group') is-invalid @enderror" name="fees_group">
                                            <option value="">{{ ___('fees.select_fees_group') }}</option>
                                            @foreach ($data['fees_groups'] as $item)
                                                <option {{ old('fees_group',$data['fees_assign']->fees_group_id) == $item->group->id ? 'selected' : '' }} value="{{ $item->group->id }}">{{ $item->group->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('fees_group')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="validationServer04" class="form-label">{{ ___('student_info.class') }} <span
                                            class="fillable">*</span></label>
                                    <select id="getSectionsFees" class="nice-select niceSelect bordered_style wide @error('class') is-invalid @enderror" name="class"
                                        aria-describedby="validationServer04Feedback">
                                        <option value="">{{ ___('student_info.select_class') }}</option>
                                        @foreach ($data['classes'] as $item)
                                            <option {{ old('class', $data['fees_assign']->classes_id) == $item->class->id ? 'selected' : '' }} value="{{ $item->class->id }}">{{ $item->class->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('class')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <h3>{{ ___('student_info.fees_types') }}</h3>
                                    <div class="table-responsive">
                                        <table class="table table-bordered role-table" id="types_table">
                                            <thead class="thead">
                                                <tr>
                                                    <th class="purchase mr-4">{{ ___('common.All') }} <input class="form-check-input" {{count($data['fees_masters']) == count($data['assigned_fes_masters']) ? 'checked':''}} type="checkbox" id="all_fees_masters"></th>
                                                    <th class="purchase">{{ ___('common.name') }}</th>
                                                    <th class="purchase">{{ ___('fees.amount') }} ({{ Setting('currency_symbol') }})</th>
                                                </tr>
                                            </thead>
                                            <tbody class="tbody">

                                                @php
                                                $total = 0;
                                                @endphp
                                                @foreach ($data['fees_masters'] as $item)
                                                <tr>
                                                    <td><input class="form-check-input fees_master" {{ in_array($item->id, $data['assigned_fes_masters'])? 'checked' : '' }} type="checkbox" name="fees_master_ids[]" value="{{$item->id}}"></td>
                                                    <td>{{ $item->type->name }}</td>
                                                    <td>{{ $item->amount }}</td>
                                                </tr>
                                                @php
                                                $total += $item->amount;
                                                @endphp
                                                @endforeach
                                                @if ($total > 0)
                                                <tr>
                                                <td><strong></strong></td>
                                                    <td><strong>{{ ___('common.total') }}</strong></td>
                                                    <td><strong>{{ $total }}</strong></td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <h3>{{ ___('student_info.students_list') }}</h3>
                                    <div class="table-responsive">
                                        <input type="hidden" id="page" value="edit">
                                        <input type="hidden" id="fees_assign_id" value="{{ $data['fees_assign']->id }}">
                                        <table class="table table-bordered role-table" id="students_table" data-fees-assign-id="{{ $data['fees_assign']->id }}">
                                            <thead class="thead">
                                                <tr>
                                                    <th class="purchase mr-4">{{ ___('common.All') }} <input class="form-check-input" type="checkbox" id="all_students"></th>
                                                    <th class="purchase">{{ ___('student_info.admission_no') }}</th>
                                                    <th class="purchase">{{ ___('student_info.student_name') }}</th>
                                                    <th class="purchase">{{ ___('academic.class') }} ({{ ___('academic.section') }})</th>
                                                    <th class="purchase">{{ ___('student_info.guardian_name') }}</th>
                                                    <th class="purchase">{{ ___('student_info.mobile_number') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="tbody"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mt-24">
                                <div class="text-end">
                                    <button class="btn btn-lg ot-btn-primary"><span><i class="fa-solid fa-save"></i>
                                        </span>{{ ___('common.submit') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
@endsection
