@extends('backend.master')

@section('title')
    {{ @$data['title'] }}
@endsection

@section('content')
    <div class="page-content">

        {{-- Breadcrumb Area Start --}}
        <div class="page-header">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="breadcrumb-title mb-1">{{ $data['title'] }}</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item" aria-current="page"><a
                                href="{{ route('order.index') }}">{{ 'Orders' }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ ___('common.add_new') }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- Breadcrumb Area End --}}

        <div class="card ot-card">
            <div class="card-body">
                <form action="{{ route('order.store') }}" enctype="multipart/form-data" method="post" id="orderForm">
                    @csrf
                    <div id="itemsContainer">
                        {{-- Example of an Item --}}
                            <div class="row mb-3 item-group">
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Product Name</label>
                                    <div>Maize</div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Price</label>
                                    <div>1000 TZS/Kg</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Supplier</label>
                                    <div>Monaban</div>
                                    <div>P.O.Box 123 Arusha Tanzania</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Stock Available</label>
                                    <div>1000 Kg</div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" class="form-control @error('quantities.{{ 1 }}') is-invalid @enderror"
                                        name="quantities[{{ 1 }}]" 
                                        placeholder="Enter Quantity" 
                                        value="{{ old('quantities') }}" 
                                        min="0" max="{{ 1000 }}">
                                    <input type="hidden" name="product_ids[{{1 }}]" value="{{ 1 }}">
                                </div>
                            </div>
                    </div>

                    <div class="text-end mt-4">
                        <button class="btn btn-md btn-outline-primary" type="submit">
                            <i class="fa-solid fa-plus"></i>Place Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
