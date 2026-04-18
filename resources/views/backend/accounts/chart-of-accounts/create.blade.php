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
                    <li class="breadcrumb-item"><a href="{{ route('chart-of-accounts.index') }}">{{ __('Chart of Accounts') }}</a></li>
                    <li class="breadcrumb-item">{{ ___('common.add_new') }}</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card ot-card">
        <div class="card-body">
            <form action="{{ route('chart-of-accounts.store') }}" method="post">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ ___('common.name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control ot-input @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Code') }} <span class="text-muted">({{ __('optional') }})</span></label>
                        <input type="text" class="form-control ot-input @error('code') is-invalid @enderror" name="code" value="{{ old('code') }}" placeholder="e.g. INC-SF">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ ___('account.type') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" name="type" required>
                            <option value="income" {{ old('type') == 'income' ? 'selected' : '' }}>{{ __('Income') }}</option>
                            <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>{{ __('Expense') }}</option>
                            <option value="asset" {{ old('type') == 'asset' ? 'selected' : '' }}>{{ __('Asset') }}</option>
                            <option value="liability" {{ old('type') == 'liability' ? 'selected' : '' }}>{{ __('Liability') }}</option>
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Parent Account') }}</label>
                        <select class="form-select" name="parent_id">
                            <option value="">{{ __('None') }}</option>
                            @foreach($data['parents'] as $p)
                                <option value="{{ $p->id }}" {{ old('parent_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->type }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">{{ ___('common.description') }}</label>
                        <textarea class="form-control" name="description" rows="2">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">{{ ___('common.save') }}</button>
                        <a href="{{ route('chart-of-accounts.index') }}" class="btn btn-secondary">{{ ___('common.cancel') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
