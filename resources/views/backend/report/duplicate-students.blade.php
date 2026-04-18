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
                    <h1 class="bradecrumb-title mb-1">{{ $data['title'] }}</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item">{{ ___('settings.Report') }}</li>
                        <li class="breadcrumb-item active">{{ $data['title'] }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- Breadcrumb Area End --}}

        <div class="table-content table-basic mt-20">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $data['title'] }}</h4>
                    <form action="{{ route('report-duplicate-students.search') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-lg btn-outline-primary">
                            <span><i class="fa-solid fa-search"></i></span>
                            <span>Search Duplicate Students</span>
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(!empty($data['duplicates']))
                        <div class="alert alert-info">
                            <strong>Found {{ count($data['duplicates']) }} duplicate(s):</strong>
                            <ul class="mb-0 mt-2">
                                <li><strong>By Name:</strong> Students with same name in the same class</li>
                                <li><strong>By Phone:</strong> Students with same phone number in the same class</li>
                            </ul>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered role-table">
                                <thead class="thead">
                                    <tr>
                                        <th class="serial">{{ ___('common.sr_no') }}</th>
                                        <th class="purchase">Duplicate Type</th>
                                        <th class="purchase">Class (Section)</th>
                                        <th class="purchase">Student 1</th>
                                        <th class="purchase">Student 2</th>
                                        <th class="action">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="tbody">
                                    @foreach($data['duplicates'] as $key => $duplicate)
                                    <tr>
                                        <td class="serial">{{ ++$key }}</td>
                                        <td>
                                            @if($duplicate['type'] == 'name')
                                                <span class="badge bg-warning">Same Name</span>
                                            @else
                                                <span class="badge bg-danger">Same Phone</span>
                                            @endif
                                        </td>
                                        <td>{{ $duplicate['class'] }} ({{ $duplicate['section'] }})</td>
                                        <td>
                                            <strong>ID:</strong> {{ $duplicate['student_1']['id'] }}<br>
                                            <strong>Name:</strong> {{ $duplicate['student_1']['name'] }}<br>
                                            <strong>Phone:</strong> {{ $duplicate['student_1']['mobile'] ?? 'N/A' }}
                                        </td>
                                        <td>
                                            <strong>ID:</strong> {{ $duplicate['student_2']['id'] }}<br>
                                            <strong>Name:</strong> {{ $duplicate['student_2']['name'] }}<br>
                                            <strong>Phone:</strong> {{ $duplicate['student_2']['mobile'] ?? 'N/A' }}
                                        </td>
                                        <td class="action">
                                            <a href="{{ route('student.show', $duplicate['student_1']['id']) }}" 
                                               class="btn btn-sm btn-outline-info" target="_blank">
                                                <i class="fa-solid fa-eye"></i> View 1
                                            </a>
                                            <a href="{{ route('student.show', $duplicate['student_2']['id']) }}" 
                                               class="btn btn-sm btn-outline-info" target="_blank">
                                                <i class="fa-solid fa-eye"></i> View 2
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <img src="{{ asset('images/no_data.svg') }}" alt="" class="mb-primary" width="100">
                            <p class="mb-0 text-center">{{ ___('common.no_data_available') }}</p>
                            <p class="mb-0 text-center text-secondary font-size-90">
                                Click "Search Duplicate Students" to find duplicates
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        // Auto-submit on page load if coming from search
        @if(request()->has('search'))
            // Already showing results
        @endif
    });
</script>
@endpush

