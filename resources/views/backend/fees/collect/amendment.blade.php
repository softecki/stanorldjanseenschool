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
                        <li class="breadcrumb-item">{{ 'Amendment' }}</li>

                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <div class="card ot-card">
            <div class="card-body">
                <form action="{{ route('fees-collect.update_amendment', @$data['fees_collect']->id) }}" enctype="multipart/form-data" method="post"
                    id="visitForm">
                    @csrf
                    @method('PUT')
                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Fees Amount' }}</label>
                                    <input disabled class="form-control ot-input @error('name') is-invalid @enderror" name="fees_amount"
                                        value="{{ old('name',@$data['fees_collect']->fees_amount) }}" list="datalistOptions" id="exampleDataList" type="text"
                                        placeholder="{{ ___('common.enter_name') }}">
                                    @error('name')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Paid Amount' }}</label>
                                    <input disabled class="form-control ot-input @error('code') is-invalid @enderror" name="paid_amount"
                                        value="{{ old('code',@$data['fees_collect']->paid_amount) }}" list="datalistOptions" id="exampleDataList" type="text"
                                        placeholder="{{ 'Paid Amount'}}">
                                    @error('code')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Remained Amount' }}</label>
                                    <input disabled class="form-control ot-input @error('code') is-invalid @enderror" name="quater_one"
                                        value="{{ old('code',@$data['fees_collect']->remained_amount) }}" list="datalistOptions" id="exampleDataList" type="text"
                                        placeholder="{{ 'Paid Amount'}}">
                                    @error('code')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Term One Remained' }}</label>
                                    <input disabled class="form-control ot-input @error('code') is-invalid @enderror" name="quater_two"
                                        value="{{ old('code',@$data['fees_collect']->quater_two+@$data['fees_collect']->quater_one) }}" list="datalistOptions" id="exampleDataList" type="text"
                                        placeholder="{{ 'Paid Amount'}}">
                                    @error('code')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
 
                                <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Term Two Remained' }}</label>
                                    <input disabled class="form-control ot-input @error('code') is-invalid @enderror" name="quater_three"
                                        value="{{ old('code',@$data['fees_collect']->quater_three+@$data['fees_collect']->quater_four) }}" list="datalistOptions" id="exampleDataList" type="text"
                                        placeholder="{{ 'Paid Amount'}}">
                                    @error('code')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Parent/Guardian Name' }}</label>
                                    <input  class="form-control ot-input @error('code') is-invalid @enderror" name="parent_name"
                                         list="datalistOptions" id="exampleDataList" type="text"
                                        placeholder="{{ 'Enter Parent/Guardian Name'}}">
                                    @error('code')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                 <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Phone Number' }}</label>
                                    <input  class="form-control ot-input @error('code') is-invalid @enderror" name="phonenumber"
                                         list="datalistOptions" id="exampleDataList" type="text"
                                        placeholder="{{ 'Phone Number'}}">
                                    @error('code')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                  <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Date' }}<span
                                        class="fillable">*</span></label>
                                    <input class="form-control ot-input @error('code') is-invalid @enderror" name="date"
                                        value="" list="datalistOptions" id="exampleDataList" type="date"
                                        placeholder="{{ 'Date'}}">
                                    @error('code')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Amount' }}</label>
                                    <input  class="form-control ot-input @error('code') is-invalid @enderror" name="new_amount"
                                        value="" list="datalistOptions" id="exampleDataList" type="number"
                                        placeholder="{{ 'Discount/Surcharge Amount'}}">
                                    @error('code')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="amendment_type" class="form-label">{{ 'Type' }}</label>
                                    <select class="form-control ot-input" name="amendment_type" id="amendment_type">
                                        <option value="1">Discount</option>
                                        <option value="2">Amendment</option>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Reason' }}</label>
                                    <textarea class="form-control ot-textarea mt-0 @error('description') is-invalid @enderror" name="description"
                                    list="datalistOptions" id="exampleDataList" type="text"
                                    placeholder="{{ 'Enter Description' }}">{{ old('description',@$data['fees_collect']->comment) }}</textarea>
                                    @error('description')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                               

                                <div class="col-md-12 mt-24">
                                    <div class="text-end">
                                        <button class="btn btn-lg ot-btn-primary"><span><i class="fa-solid fa-save"></i>
                                            </span>{{ ___('common.update') }}</button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
@endsection
