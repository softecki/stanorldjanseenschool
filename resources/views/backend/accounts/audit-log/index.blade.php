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
                    <li class="breadcrumb-item">@if(Route::has('accounting.dashboard'))<a href="{{ route('accounting.dashboard') }}">{{ __('Accounting') }}</a>@else<span>{{ __('Accounting') }}</span>@endif</li>
                    <li class="breadcrumb-item">{{ $data['title'] }}</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">{{ $data['title'] }}</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ ___('account.date') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Action') }}</th>
                            <th>{{ __('Table') }}</th>
                            <th>{{ __('Record ID') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['logs'] as $log)
                        <tr>
                            <td>{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $log->user->name ?? '-' }}</td>
                            <td>{{ $log->action }}</td>
                            <td>{{ $log->table_name }}</td>
                            <td>{{ $log->record_id }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">{{ ___('common.no_data_available') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end py-2">
                {!! $data['logs']->links() !!}
            </div>
        </div>
    </div>
</div>
@endsection
