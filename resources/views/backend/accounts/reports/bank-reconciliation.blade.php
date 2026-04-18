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
                    <li class="breadcrumb-item">
                        @if(Route::has('accounting.dashboard'))
                            <a href="{{ route('accounting.dashboard') }}">{{ __('Accounting') }}</a>
                        @else
                            <span>{{ __('Accounting') }}</span>
                        @endif
                    </li>
                    <li class="breadcrumb-item">{{ $data['title'] }}</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Upload Bank Statement PDF') }}</h5>
            <p class="mb-0 text-muted">{{ __('Upload your bank reconciliation statement in PDF format') }}</p>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>{{ __('Errors:') }}</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ Session::get('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (Session::has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ Session::get('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('accounting.bank-reconciliation.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="excel_file" class="form-label">{{ __('Excel File') }} <span class="text-danger">*</span></label>
                    <input type="file" class="form-control @error('excel_file') is-invalid @enderror" 
                           id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                    @error('excel_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">{{ __('Maximum file size: 10MB. Supported formats: XLSX, XLS, CSV') }}</small>
                </div>

                <div class="alert alert-info" role="alert">
                    <h6 class="alert-heading">{{ __('Expected Excel Format:') }}</h6>
                    <p class="mb-1">{{ __('Your bank statement Excel file should contain the following columns:') }}</p>
                    <ul class="mb-0">
                        <li>{{ __('Posting Date') }}</li>
                        <li>{{ __('Details') }}</li>
                        <li>{{ __('Value Date') }}</li>
                        <li>{{ __('Debit') }}</li>
                        <li>{{ __('Credit') }}</li>
                        <li>{{ __('Book Balance') }}</li>
                    </ul>
                    <p class="mt-2 mb-0"><small><strong>{{ __('Note:') }}</strong> {{ __('The first row should contain headers. Data rows should start from row 2.') }}</small></p>
                </div>

                <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="me-2 las la-upload"></i>{{ __('Upload Excel File') }}
                    </button>
                    <a href="{{ route('accounting.dashboard') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tips Section -->
    <div class="card mt-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('How it works') }}</h5>
        </div>
        <div class="card-body">
            <ol>
                <li>{{ __('Export your bank statement from your bank as Excel format (.xlsx or .csv)') }}</li>
                <li>{{ __('Ensure the file has columns: Posting Date, Details, Value Date, Debit, Credit, Book Balance') }}</li>
                <li>{{ __('Upload the Excel file using the form above') }}</li>
                <li>{{ __('The system will extract all transactions from the Excel sheet') }}</li>
                <li>{{ __('Student names will be automatically matched to transaction details') }}</li>
                <li>{{ __('Review the processed data and download the final PDF report') }}</li>
            </ol>
            <div class="alert alert-warning mt-3" role="alert">
                <strong>{{ __('Tips:') }}</strong>
                <ul class="mb-0">
                    <li>{{ __('Make sure the first row contains column headers') }}</li>
                    <li>{{ __('Data should start from row 2') }}</li>
                    <li>{{ __('Each transaction should be a separate row') }}</li>
                    <li>{{ __('The Details column should contain the reference number (e.g., REF:19c50498040a7ae6)') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
