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
                                href="{{ route('expense.index') }}">{{ ___('settings.expense') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ ___('common.add_new') }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <div class="card ot-card">
            <div class="card-body">
                <form action="{{ route('product.sellout') }}" enctype="multipart/form-data" method="post" id="visitForm">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <div class="row">

                      

                                <div class="col-md-6 mb-3">
                                <label for="itemSelect" class="form-label">{{ 'Product Name' }} <span class="fillable">*</span></label>
                                <select class="form-control ot-input @error('name') is-invalid @enderror" name="name" id="itemSelect">
                                    <option value="">{{ 'Select Product' }}</option>
                                    @foreach($data['items'] as $item)
                                        <option value="{{ $item->id }}" {{ old('name') == $item->name ? 'selected' : '' }}>
                                            {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                                

                                 <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Quantity' }} <span
                                            class="fillable">*</span></label>
                                    <input class="form-control ot-input @error('quantity') is-invalid @enderror" name="quantity"
                                        value="{{ old('quantity') }}" list="datalistOptions" id="exampleDataList"
                                        placeholder="{{ 'Enter Quantity' }}">
                                    @error('name')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                  <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Receipt Number' }} <span
                                            class="fillable">*</span></label>
                                    <input class="form-control ot-input @error('receipt') is-invalid @enderror" name="receipt"
                                        value="{{ old('receipt') }}" list="datalistOptions" id="exampleDataList"
                                        placeholder="{{ 'Enter Receipt' }}">
                                    @error('receipt')
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
                        </div>
                    </div>
            </div>
        </div>
    </div>
@endsection
