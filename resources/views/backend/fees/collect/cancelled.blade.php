@extends('backend.master')
@section('title')
    {{ @$data['title'] }}
@endsection
@section('content')
    <div class="page-content">
        <div class="">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="bradecrumb-title">{{ $data['title'] }}</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('fees-collect.index') }}">{{ ___('fees.fees_collect') }}</a></li>
                        <li class="breadcrumb-item active">Cancelled Collect</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="row mt-1">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Cancelled Collect</h6>
                        <a href="{{ route('fees-collect.index') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-arrow-left me-1"></i> Back to Fees Collect
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered role-table">
                                <thead class="thead">
                                    <tr>
                                        <th class="purchase">{{ ___('student_info.student_name') }}</th>
                                        <th class="purchase">Fees Type</th>
                                        <th class="purchase">{{ ___('academic.class') }}</th>
                                        <th class="purchase">Fee Amount</th>
                                        <th class="purchase">Paid</th>
                                        <th class="purchase">Remained</th>
                                        <th class="purchase">Cancelled On</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data['cancelled'] as $item)
                                        <tr class="text-sm">
                                            <td>{{ $item->first_name }} {{ $item->last_name }}</td>
                                            <td>{{ $item->fees_name ?? '—' }}</td>
                                            <td>{{ $item->class_name ?? '—' }}</td>
                                            <td>{{ is_numeric($item->fees_amount) ? number_format($item->fees_amount, 2, '.', ',') : ($item->fees_amount ?? '—') }}</td>
                                            <td>{{ is_numeric($item->paid_amount) ? number_format($item->paid_amount, 2, '.', ',') : ($item->paid_amount ?? '—') }}</td>
                                            <td>{{ is_numeric($item->remained_amount) ? number_format($item->remained_amount, 2, '.', ',') : ($item->remained_amount ?? '—') }}</td>
                                            <td>{{ $item->cancelled_at ? \Carbon\Carbon::parse($item->cancelled_at)->format('d M Y H:i') : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center gray-color py-4">
                                                <img src="{{ asset('images/no_data.svg') }}" alt="" class="mb-primary" width="100">
                                                <p class="mb-0">{{ ___('common.no_data_available') }}</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="ot-pagination pagination-content d-flex justify-content-end align-content-center py-3">
                            <nav aria-label="Page navigation example">
                                <ul class="pagination justify-content-between mb-0">
                                    {!! $data['cancelled']->appends(request()->query())->links() !!}
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
