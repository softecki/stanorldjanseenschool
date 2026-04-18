@extends('backend.master')

@section('title')
    {{ $data['title'] }} - {{ $data['record']->full_name }}
@endsection

@section('content')
    <div class="page-content">
        <div class="page-header mb-4">
            <div class="row">
                <div class="col-sm-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="bradecrumb-title mb-2">
                                <i class="las la-history me-2"></i>{{ ___('common.view_deleted_student') ?? 'View deleted student' }}
                            </h4>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('student_deleted_history.index') }}">{{ ___('common.deleted_student_history') ?? 'Deleted student history' }}</a></li>
                                <li class="breadcrumb-item active">{{ $data['record']->full_name }}</li>
                            </ol>
                        </div>
                        <a href="{{ route('student_deleted_history.index') }}" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-arrow-left me-2"></i>{{ ___('common.back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info mb-4">
            <i class="fa-solid fa-info-circle me-2"></i>
            {{ ___('common.view_only_no_edit_delete') ?? 'This is a historical record. View only — no edit or delete.' }}
        </div>

        {{-- Student details snapshot --}}
        <div class="card ot-card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa-solid fa-user me-2"></i>{{ ___('common.student_details') ?? 'Student details' }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>{{ ___('student_info.student_name') }}:</strong> {{ $data['record']->full_name }}</p>
                        <p><strong>{{ ___('student_info.admission_no') }}:</strong> {{ $data['record']->admission_no ?? '—' }}</p>
                        <p><strong>{{ ___('common.date_of_birth') }}:</strong> {{ $data['record']->dob ?? '—' }}</p>
                        <p><strong>{{ ___('student_info.mobile_number') }}:</strong> {{ $data['record']->mobile ?? '—' }}</p>
                        <p><strong>{{ ___('common.email') }}:</strong> {{ $data['record']->email ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>{{ ___('common.deleted_at') }}:</strong> {{ $data['record']->deleted_at ? $data['record']->deleted_at->format('d M Y H:i') : '—' }}</p>
                        <p><strong>{{ ___('common.deleted_by') }}:</strong> {{ $data['record']->deletedByUser->name ?? '—' }}</p>
                        <p><strong>{{ ___('frontend.residance_address') }}:</strong> {{ $data['record']->residance_address ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fees assign history --}}
        <div class="card ot-card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa-solid fa-file-invoice-dollar me-2"></i>{{ ___('common.fees_assign_history') ?? 'Fees assign history' }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fees assign ID</th>
                                <th>Fees master ID</th>
                                <th>Fees amount</th>
                                <th>Paid amount</th>
                                <th>Remained amount</th>
                                <th>Deleted at</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['record']->feesAssignHistory as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $item->fees_assign_id ?? '—' }}</td>
                                <td>{{ $item->fees_master_id ?? '—' }}</td>
                                <td>{{ $item->fees_amount !== null ? number_format($item->fees_amount, 2) : '—' }}</td>
                                <td>{{ $item->paid_amount !== null ? number_format($item->paid_amount, 2) : '—' }}</td>
                                <td>{{ $item->remained_amount !== null ? number_format($item->remained_amount, 2) : '—' }}</td>
                                <td>{{ $item->deleted_at ? $item->deleted_at->format('d M Y') : '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-3 text-muted">{{ ___('common.no_data_available') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Fees collect history --}}
        <div class="card ot-card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa-solid fa-money-bill-wave me-2"></i>{{ ___('common.fees_collect_history') ?? 'Fees collect history' }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Fine amount</th>
                                <th>Payment method</th>
                                <th>Deleted at</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['record']->feesCollectHistory as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $item->date ? \Carbon\Carbon::parse($item->date)->format('d M Y') : '—' }}</td>
                                <td>{{ $item->amount !== null ? number_format($item->amount, 2) : '—' }}</td>
                                <td>{{ $item->fine_amount !== null ? number_format($item->fine_amount, 2) : '—' }}</td>
                                <td>{{ $item->payment_method ?? '—' }}</td>
                                <td>{{ $item->deleted_at ? $item->deleted_at->format('d M Y') : '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-3 text-muted">{{ ___('common.no_data_available') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
