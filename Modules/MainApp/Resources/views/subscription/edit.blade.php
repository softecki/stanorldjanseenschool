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
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> {{ ___('common.home') }} </a></li>
                        <li class="breadcrumb-item"><a href="{{ route('subscription.index') }}">{{ ___('settings.subscriptions') }}</a></li>
                        <li class="breadcrumb-item">{{ $data['title'] }}</li>

                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}
        <div class="card ot-card">
            <div class="card-body">
                <form action="{{ route('subscription.approved', @$data['subscription']->id) }}" enctype="multipart/form-data" method="post"
                    id="visitForm">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        {{-- <div class="col-md-6 mb-3">
                            <label for="exampleDataList" class="form-label">{{ ___('common.name') }} <span
                                    class="fillable">*</span></label>
                            <input class="form-control ot-input @error('name') is-invalid @enderror" name="name" 
                                list="datalistOptions" id="exampleDataList"
                                placeholder="{{ ___('common.Enter name') }}" value="{{ old('name', @$data['subscription']->name) }}">
                            @error('name')
                                <div id="validationServer04Feedback" class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div> --}}

                        <div class="col-md-6 mb-3">
                            <label for="validationServer04" class="form-label">{{ ___('common.Package') }} <span class="fillable">*</span></label>
                            <select class="nice-select niceSelect bordered_style wide @error('package') is-invalid @enderror"
                            name="package" id="validationServer04"
                            aria-describedby="validationServer04Feedback" @disabled(true)>
                                <option value="">{{ ___('common.Select package') }}</option>
                                @foreach ($data['packages'] as $item)
                                    <option {{ old('package', @$data['subscription']->package_id) == $item->id ? 'selected':'' }} value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>

                            @error('package')
                                <div id="validationServer04Feedback" class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        
                        {{-- <div class="col-md-6 mb-3">
                            <label for="exampleDataList" class="form-label">{{ ___('common.Code') }} <span
                                    class="fillable">*</span></label>
                            <input class="form-control ot-input @error('code') is-invalid @enderror" name="code" type="number"
                                list="datalistOptions" id="exampleDataList"
                                placeholder="{{ ___('common.Enter code') }}" value="{{ old('code', @$data['subscription']->code) }}">
                            @error('code')
                                <div id="validationServer04Feedback" class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div> --}}
                                                        
                        {{-- <div class="col-md-6 mb-3">
                            <label for="exampleDataList" class="form-label">{{ ___('common.phone') }} <span
                                    class="fillable">*</span></label>
                            <input class="form-control ot-input @error('phone') is-invalid @enderror" name="phone"
                                list="datalistOptions" id="exampleDataList"
                                placeholder="{{ ___('common.Enter phone') }}" value="{{ old('phone', @$data['subscription']->phone) }}">
                            @error('phone')
                                <div id="validationServer04Feedback" class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div> --}}
                                                        
                        {{-- <div class="col-md-6 mb-3">
                            <label for="exampleDataList" class="form-label">{{ ___('common.email') }} <span
                                    class="fillable">*</span></label>
                            <input class="form-control ot-input @error('email') is-invalid @enderror" name="email" type="email"
                                list="datalistOptions" id="exampleDataList"
                                placeholder="{{ ___('common.Enter email') }}" value="{{ old('email', @$data['subscription']->email) }}">
                            @error('email')
                                <div id="validationServer04Feedback" class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div> --}}
                           
                        {{-- <div class="col-md-6 mb-3">
                            <label for="exampleDataList" class="form-label">{{ ___('common.Post code') }} <span
                                    class="fillable">*</span></label>
                            <input class="form-control ot-input @error('post_code') is-invalid @enderror" name="post_code" type="number"
                                list="datalistOptions" id="exampleDataList"
                                placeholder="{{ ___('common.Enter post code') }}" value="{{ old('post_code', @$data['subscription']->post_code) }}">
                            @error('post_code')
                                <div id="validationServer04Feedback" class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div> --}}
                                                       
                        {{-- <div class="col-md-6 mb-3">
                            <label for="exampleDataList" class="form-label">{{ ___('common.Sub domain key') }} <span
                                    class="fillable">*</span></label>
                            <input class="form-control ot-input @error('sub_domain_key') is-invalid @enderror" name="sub_domain_key"
                                list="datalistOptions" id="exampleDataList"
                                placeholder="{{ ___('common.Enter sub domain key') }}" value="{{ old('sub_domain_key', @$data['subscription']->sub_domain_key) }}" @readonly(true)>
                            @error('sub_domain_key')
                                <div id="validationServer04Feedback" class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div> --}}
                                                        
                        {{-- <div class="col-md-6 mb-3">
                            <label for="exampleDataList" class="form-label">{{ ___('common.address') }} <span
                                    class="fillable">*</span></label>
                            <input class="form-control ot-input @error('address') is-invalid @enderror" name="address"
                                list="datalistOptions" id="exampleDataList"
                                placeholder="{{ ___('common.Enter address') }}" value="{{ old('address', @$data['subscription']->address) }}">
                            @error('address')
                                <div id="validationServer04Feedback" class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div> --}}
                        <div class="col-md-6 mb-3">
                            <label for="validationServer04" class="form-label">{{ ___('common.Status') }} <span class="fillable">*</span></label>
                            <select class="nice-select niceSelect bordered_style wide @error('status') is-invalid @enderror"
                            name="status" id="validationServer04"
                            aria-describedby="validationServer04Feedback">
                                @if (@$data['subscription']->status != \App\Enums\SubscriptionStatus::APPROVED)
                                    <option {{ @$data['subscription']->status == \App\Enums\SubscriptionStatus::APPROVED ? 'selected':'' }} value="{{ \App\Enums\SubscriptionStatus::APPROVED }}">{{ ___('common.Approved') }}</option>
                                @endif
                                @if (@$data['subscription']->status != \App\Enums\SubscriptionStatus::REJECT)
                                    <option {{ @$data['subscription']->status == \App\Enums\SubscriptionStatus::REJECT ? 'selected':'' }} value="{{ \App\Enums\SubscriptionStatus::REJECT }}">{{ ___('common.Reject') }}</option>
                                @endif
                            </select>

                            @error('status')
                                <div id="validationServer04Feedback" class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-12 mt-24">
                            <div class="text-end">
                                <button class="btn btn-lg ot-btn-primary"><span><i class="fa-solid fa-save"></i>
                                    </span>{{ ___('common.submit') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection