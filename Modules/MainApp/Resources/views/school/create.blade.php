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
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item" aria-current="page"><a
                                href="{{ route('school.index') }}">{{ ___('settings.School') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ ___('common.add_new') }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <div class="card ot-card">
            <div class="card-body">
                <form action="{{ route('school.store') }}" enctype="multipart/form-data" method="post" id="visitForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="exampleDataList" class="form-label">{{ ___('common.name') }} <span
                                    class="fillable">*</span></label>
                            <input class="form-control ot-input @error('name') is-invalid @enderror" name="name" type="text"
                                list="datalistOptions" id="exampleDataList"
                                placeholder="{{ ___('common.Enter name') }}" value="{{ old('name') }}">
                            @error('name')
                                <div id="validationServer04Feedback" class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>          

                        <div class="col-md-6 mb-3">
                            <label for="validationServer04" class="form-label">{{ ___('common.Package') }} <span class="fillable">*</span></label>
                            <select class="nice-select niceSelect bordered_style wide @error('package') is-invalid @enderror"
                            name="package" id="validationServer04"
                            aria-describedby="validationServer04Feedback">
                                <option value="">{{ ___('common.Select package') }}</option>
                                @foreach ($data['packages'] as $item)
                                    <option {{ old('package') == $item->id ? 'selected':'' }} value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>

                            @error('package')
                                <div id="validationServer04Feedback" class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                                                        
                        <div class="col-md-6 mb-3">
                            <label for="exampleDataList" class="form-label">{{ ___('common.phone') }} <span
                                    class="fillable">*</span></label>
                            <input class="form-control ot-input @error('phone') is-invalid @enderror" name="phone" type="text"
                                list="datalistOptions" id="exampleDataList"
                                placeholder="{{ ___('common.Enter phone') }}" value="{{ old('phone') }}">
                            @error('phone')
                                <div id="validationServer04Feedback" class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                                                        
                        <div class="col-md-6 mb-3">
                            <label for="exampleDataList" class="form-label">{{ ___('common.email') }} <span
                                    class="fillable">*</span></label>
                            <input class="form-control ot-input @error('email') is-invalid @enderror" name="email" type="email"
                                list="datalistOptions" id="exampleDataList"
                                placeholder="{{ ___('common.Enter email') }}" value="{{ old('email') }}">
                            @error('email')
                                <div id="validationServer04Feedback" class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                                                       
                        <div class="col-md-6 mb-3">
                            <label for="sub_domain_key" class="form-label">{{ ___('common.Sub domain key') }} <span
                                    class="fillable">*</span></label>
                            <input class="form-control ot-input @error('sub_domain_key') is-invalid @enderror" name="sub_domain_key" type="text"
                                list="datalistOptions" id="sub_domain_key"
                                placeholder="{{ ___('common.Enter sub domain key (0-9a-z)') }}" value="{{ old('sub_domain_key') }}">
                            @error('sub_domain_key')
                                <div id="validationServer04Feedback" class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                                                        
                        <div class="col-md-6 mb-3">
                            <label for="validationServer04" class="form-label">{{ ___('common.status') }} <span class="fillable">*</span></label>
                            <select class="nice-select niceSelect bordered_style wide @error('status') is-invalid @enderror"
                            name="status" id="validationServer04"
                            aria-describedby="validationServer04Feedback">
                                <option {{ old('status') == App\Enums\Status::ACTIVE ? 'selected':'' }} value="{{ App\Enums\Status::ACTIVE }}">{{ ___('common.active') }}</option>
                                <option {{ old('status') == App\Enums\Status::INACTIVE ? 'selected':'' }} value="{{ App\Enums\Status::INACTIVE }}">{{ ___('common.inactive') }}
                                </option>
                            </select>

                            @error('status')
                                <div id="validationServer04Feedback" class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                                                        
                        <div class="col-md-12 mb-3">
                            <label for="exampleDataList" class="form-label">{{ ___('common.address') }} <span
                                    class="fillable">*</span></label>
                            <input class="form-control ot-input @error('address') is-invalid @enderror" name="address" type="text"
                                list="datalistOptions" id="exampleDataList"
                                placeholder="{{ ___('common.Enter address') }}" value="{{ old('address') }}">
                            @error('address')
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



@push('script')
    <script>
        const inputField = document.getElementById('sub_domain_key');
        inputField.addEventListener('input', function(event) {
        const currentValue = event.target.value;
        const sanitizedValue = currentValue.replace(/[^0-9a-z]/g, '');
        event.target.value = sanitizedValue;
        });
    </script>
@endpush