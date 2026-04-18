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
                                href="{{ route('banksAccounts.index') }}">{{ 'Bank Accounts' }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ ___('common.add_new') }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <div class="card ot-card">
            <div class="card-body">
                <form action="{{ route('banksAccounts.store') }}" enctype="multipart/form-data" method="post" id="visitForm">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Bank Name' }} <span
                                            class="fillable">*</span></label>
                                    <input class="form-control ot-input @error('bank_name') is-invalid @enderror" name="bank_name"
                                        list="datalistOptions" id="exampleDataList"
                                        placeholder="{{ 'Bank Name' }}" value="{{ old('bank_name') }}">
                                    @error('bank_name')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>


                                <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Account Name' }} <span
                                                class="fillable">*</span></label>
                                    <input class="form-control ot-input @error('account_name') is-invalid @enderror" name="account_name"
                                           list="datalistOptions" id="exampleDataList"
                                           placeholder="{{ 'Enter Account Name' }}" value="{{ old('account_name') }}">
                                    @error('account_name')
                                    <div id="validationServer04Feedback" class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Account Number' }} <span
                                                class="fillable">*</span></label>
                                    <input class="form-control ot-input @error('account_number') is-invalid @enderror" name="account_number"
                                           list="datalistOptions" id="exampleDataList"
                                           placeholder="{{ 'Enter Account Number' }}" value="{{ old('account_number') }}">
                                    @error('account_number')
                                    <div id="validationServer04Feedback" class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>


                                <div class="col-md-6">

                                    <label for="validationServer04" class="form-label">{{ ___('common.status') }} <span class="fillable">*</span></label>
                                    <select class="nice-select niceSelect bordered_style wide @error('status') is-invalid @enderror"
                                    name="status" id="validationServer04"
                                    aria-describedby="validationServer04Feedback">
                                        <option value="{{ App\Enums\Status::ACTIVE }}">{{ ___('common.active') }}</option>
                                        <option value="{{ App\Enums\Status::INACTIVE }}">{{ ___('common.inactive') }}
                                        </option>
                                    </select>

                                    @error('status')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>
                                <div class="col-md-12 mt-24">
                                    <div class="text-end">
                                        <button class="btn btn-lg btn-outline-primary"><span><i class="fa-solid fa-save"></i>
                                            </span>{{ ___('common.submit') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
