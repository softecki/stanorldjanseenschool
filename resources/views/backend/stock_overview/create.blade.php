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
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item" aria-current="page"><a
                                href="{{ route('blood-groups.index') }}">{{ "Stock Overview" }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ ___('common.add_new') }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <div class="card ot-card">
            <div class="card-body">
                <form action="{{ route('storekeeper.store') }}" enctype="multipart/form-data" method="post" id="visitForm">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <div class="row">

                                <div class="col-md-6">
                                    <label for="validationServer04" class="form-label">{{ 'Product Name'}} <span
                                        class="fillable">*</span></label>

                                    <select class="nice-select niceSelect bordered_style wide @error('name') is-invalid @enderror"
                                    name="name" id="validationServer04"
                                    aria-describedby="validationServer04Feedback">
                                    @foreach ($data['products'] as $item)
                                        <option value="{{ $item->id }}" {{ old('name') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                    </select>
                                </div>


                                <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Price' }} <span
                                            class="fillable">*</span></label>
                                    <input class="form-control ot-input @error('price') is-invalid @enderror" name="price"
                                        list="datalistOptions" id="exampleDataList"
                                        placeholder="{{ 'Enter Price' }}" value="{{ old('price') }}">
                                    @error('price')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Quantity' }} <span
                                            class="fillable">*</span></label>
                                    <input class="form-control ot-input @error('quantity') is-invalid @enderror" name="quantity"
                                        list="datalistOptions" id="exampleDataList"
                                        placeholder="{{ 'Enter Quantity' }}" value="{{ old('quantity') }}">
                                    @error('quantity')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="validationServer04" class="form-label">{{ 'Unit'}} <span
                                        class="fillable">*</span></label>

                                    <select class="nice-select niceSelect bordered_style wide @error('unit') is-invalid @enderror"
                                    name="unit" id="validationServer04"
                                    aria-describedby="validationServer04Feedback">
                                    @foreach ($data['units'] as $item)
                                        <option value="{{ $item->id }}" {{ old('unit') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                    </select>
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
