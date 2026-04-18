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
                <form action="{{ route('expense.store') }}" enctype="multipart/form-data" method="post" id="visitForm">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <div class="row">
                              <div class="col-md-6 mb-3">
                                <label for="exampleDataList" class="form-label">{{ 'Expenses Name' }} <span class="fillable">*</span></label>
                                <input class="form-control ot-input @error('name') is-invalid @enderror"
                                    name="name"
                                    value="{{ old('name') }}"
                                    list="datalistOptions"
                                    id="expenseNameInput"
                                    placeholder="{{ ___('common.enter_name') }}">
                                @error('name')
                                    <div id="validationServer04Feedback" class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                                <div class="col-md-6">
                                    <label for="validationServer04" class="form-label">{{ ___('account.expense_head') }} <span
                                        class="fillable">*</span></label>

                                    <select class="nice-select niceSelect bordered_style wide @error('expense_head') is-invalid @enderror"
                                    name="expense_head" id="validationServer04"
                                    aria-describedby="validationServer04Feedback">
                                    @foreach ($data['heads'] as $item)
                                        <option value="{{ $item->id }}" {{ old('expense_head') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                    </select>
                                </div>
                                @error('expense_head')
                                    <div id="validationServer04Feedback" class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                                   <div class="col-md-6" id="driver-select-wrapper" style="display: none;">
                                    <label for="validationServer04" class="form-label">Driver </label>

                                    <select class="nice-select niceSelect bordered_style wide @error('driver') is-invalid @enderror"
                                    name="driver" id="validationServer04"
                                    aria-describedby="validationServer04Feedback">
                                    <option value="">N/A</option>
                                    @foreach ($data['drivers'] as $item)
                                        <option value="{{  $item->driver_name }}" >{{ $item->driver_name }}</option>
                                    @endforeach
                                    </select>
                                </div>
                                @error('driver')
                                    <div id="validationServer04Feedback" class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                                {{-- <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ ___('account.date') }} <span
                                            class="fillable">*</span></label>
                                    <input class="form-control ot-input @error('date') is-invalid @enderror" name="date" type="date"
                                        value="{{ old('date') }}" list="datalistOptions" id="exampleDataList"
                                        placeholder="{{ ___('common.enter_date') }}">
                                    @error('date')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div> --}}
                                <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Receiver Name' }} </label>
                                    <input class="form-control ot-input @error('invoice_number') is-invalid @enderror" name="invoice_number"
                                        value="{{ old('invoice_number') }}" list="datalistOptions" id="exampleDataList"
                                        placeholder="{{ 'Enter Receivers Name' }}">
                                    @error('invoice_number')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ ___('account.amount') }} ({{ Setting('currency_symbol') }}) <span
                                            class="fillable">*</span></label>
                                    <input class="form-control ot-input @error('amount') is-invalid @enderror" name="amount" type="number"
                                        value="{{ old('amount') }}" list="datalistOptions" id="exampleDataList"
                                        placeholder="{{ ___('account.enter_amount') }}">
                                    @error('amount')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                 <div class="col-md-6 mb-3">
                                    <label for="accountNumberSelect" class="form-label">{{ 'Account Number' }} <span class="fillable">*</span></label>
                                    <select class="form-control ot-input" name="account_number" id="accountNumberSelect" required>
                                        <option value="" disabled selected>{{ 'Select Account Number' }}</option>
                                        @foreach ($data['account_number'] as $account)
                                            <option value="{{ $account->id}}">{{ $account->account_number }} {{ $account->bank_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Account Number/Phone Number' }}  <span
                                                class="fillable">*</span></label>
                                    <input class="form-control ot-input @error('account_number') is-invalid @enderror" name="account_number" type="text"
                                           value="{{ old('account_number') }}" list="datalistOptions" id="exampleDataList"
                                           placeholder="{{ 'Account Number/Phone Number' }}">
                                    @error('account_number')
                                    <div id="validationServer04Feedback" class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div> --}}

                                 {{-- <div class="col-md-6 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ 'Date' }}  <span
                                                class="fillable">*</span></label>
                                    <input class="form-control ot-input @error('date') is-invalid @enderror" name="date" type="date"
                                           value="{{ old('date') }}" list="datalistOptions" id="exampleDataList"
                                           placeholder="{{ 'Date' }}">
                                    @error('bank_name')
                                    <div id="validationServer04Feedback" class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>  --}}


                                <div class="col-md-6">
                                    <label for="exampleDataList"
                                        class="form-label ">{{ ___('common.document') }} <span
                                            class="fillable"></span></label>
                                    <div class="ot_fileUploader left-side mb-3">
                                        <input class="form-control" type="text"
                                            placeholder="{{ ___('common.document') }}" readonly="" id="placeholder">
                                        <button class="primary-btn-small-input" type="button">
                                            <label class="btn btn-lg ot-btn-primary"
                                                for="fileBrouse">{{ ___('common.browse') }}</label>
                                            <input type="file" class="d-none form-control" name="document"
                                                id="fileBrouse">
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="exampleDataList" class="form-label ">{{ ___('account.description') }}</label>
                                    <textarea class="form-control ot-textarea @error('description') is-invalid @enderror" name="description"
                                    list="datalistOptions" id="exampleDataList"
                                    placeholder="{{ ___('account.enter_description') }}">{{ old('description') }}</textarea>
                                    @error('description')
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

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('expenseNameInput');
        if (!input.value) {
            const today = new Date();
            const formatted = String(today.getDate()).padStart(2, '0') +
                              String(today.getMonth() + 1).padStart(2, '0') +
                              today.getFullYear();
            input.value = formatted;
        }
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const expenseHeadSelect = document.querySelector("select[name='expense_head']");
        const driverSelectWrapper = document.querySelector("select[name='driver']").closest('.col-md-6');

        function toggleDriverSelect() {
            if (expenseHeadSelect.value === "2") {
                driverSelectWrapper.style.display = "block";
            } else {
                driverSelectWrapper.style.display = "none";
                document.querySelector("select[name='driver']").value = ""; // Optional: Reset driver select
            }
        }

        // Initial check on page load
        toggleDriverSelect();

        // Add event listener
        expenseHeadSelect.addEventListener("change", toggleDriverSelect);
    });
</script>


@endsection
