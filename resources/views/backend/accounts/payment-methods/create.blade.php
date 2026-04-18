@extends('backend.master')
@section('title'){{ @$data['title'] }}@endsection
@section('content')
<div class="page-content">
    <div class="page-header">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="bradecrumb-title mb-1">{{ $data['title'] }}</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('payment-methods.index') }}">{{ __('Payment Methods') }}</a></li>
                    <li class="breadcrumb-item">{{ ___('common.add_new') }}</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="card ot-card">
        <div class="card-body">
            <form action="{{ route('payment-methods.store') }}" method="post">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ ___('common.name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control ot-input @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ ___('common.description') }}</label>
                        <input type="text" class="form-control ot-input" name="description" value="{{ old('description') }}">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">{{ ___('common.active') }}</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">{{ ___('common.save') }}</button>
                <a href="{{ route('payment-methods.index') }}" class="btn btn-secondary">{{ ___('common.cancel') }}</a>
            </form>
        </div>
    </div>
</div>
@endsection
