@extends('backend.master')
@section('title')
    {{ @$data['title'] }}
@endsection
@section('content')
    <div class="page-content">

        {{-- bradecrumb Area S t a r t --}}
        {{-- <div class="page-header">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="bradecrumb-title mb-1">{{ $data['title'] }}</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item">{{ $data['title'] }}</li>
                    </ol>
                </div>
            </div>
        </div> --}}
        {{-- bradecrumb Area E n d --}}

        <!--  table content start -->
        {{-- <div class="table-content table-basic mt-20">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ 'Order Now' }}</h4>
                
                </div>
                <div class="row d-flex ">
                    @foreach ($data['products'] as $product)
                        <div class="m-auto col-md-3 col-sm-3 my-2" style="width: 15rem;">
                            <div class="card">
                                <img src="/backend/Acoustic.png" class="card-img-top" alt="...">
                                <div class="card-body">
                                    <h5 class="card-title text-center">{{ $product->name }}</h5>
                                    <p class="card-text text-center">
                                        {{ \Illuminate\Support\Str::limit($product->description, $limit = 50, $end = '...') }}
                                    </p>
                                    <hr class="w-100 mb-2 m-auto">
                                    <h5 class="card-text text-center my-3">{{ $product->price }}$</h5>
                                    </div>
                                </div>
                                <a href="{{ route('products.show', $product->id) }}"
                                    class="btn btn-dark w-100 rounded-top-0" style="background-color: #102C57">View</a>
                            </div>
                        </div>
                    @endforeach
                </div>
                   
                
                </div>
        </div> --}}

        <div class="table-content table-basic mt-20">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Order Now</h4>
        </div>

        <!-- Grid row -->
        <div class="row">
            @foreach ($data['products'] as $product)
                <div class="col-lg-4 col-md-6 col-sm-12 mb-4 d-flex align-items-stretch">
                    <div class="card w-100">
                        <img style="height: 150px" src="/backend/Acoustic.png" class="card-img-top" alt="...">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-center">{{ $product->name }}</h5>
                            {{-- <p class="card-text text-center">
                                {{ \Illuminate\Support\Str::limit($product->description, 50, '...') }}
                            </p> --}}
                            {{-- <hr class="w-100 mb-2"> --}}
                            <h5 class="card-text text-center my-1">{{ $product->price }}$</h5>
                        </div>
                        <a href="{{ route('products.show', $product->id) }}"
                           class="btn btn-dark w-100 rounded-top-0 mt-auto"
                           style="background-color: #102C57">View</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>


    </div>
@endsection

@push('script')
    @include('backend.partials.delete-ajax')
@endpush
