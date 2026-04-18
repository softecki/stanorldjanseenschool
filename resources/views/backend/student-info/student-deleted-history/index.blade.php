@extends('backend.master')

@section('title')
    {{ $data['title'] }}
@endsection

@section('content')
    <div class="page-content">
        <div class="page-header mb-4">
            <div class="row">
                <div class="col-sm-12">
                    <h4 class="bradecrumb-title mb-2">
                        <i class="las la-history me-2"></i>{{ $data['title'] }}
                    </h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.index') }}">{{ ___('student_info.students') }}</a></li>
                        <li class="breadcrumb-item active">{{ $data['title'] }}</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="card ot-card">
            <div class="card-header">
                <h5 class="mb-0"><i class="las la-list me-2"></i>{{ ___('common.deleted_student_history') ?? 'Deleted student history' }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ ___('student_info.student_name') }}</th>
                                <th>{{ ___('student_info.admission_no') }}</th>
                                <th>{{ ___('common.mobile') ?? 'Mobile' }}</th>
                                <th>{{ ___('common.deleted_at') ?? 'Deleted at' }}</th>
                                <th>{{ ___('common.deleted_by') ?? 'Deleted by' }}</th>
                                <th class="text-center">{{ ___('common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['records'] as $key => $row)
                            <tr>
                                <td>{{ $data['records']->firstItem() + $key }}</td>
                                <td>{{ $row->full_name }}</td>
                                <td>{{ $row->admission_no ?? '—' }}</td>
                                <td>{{ $row->mobile ?? '—' }}</td>
                                <td>{{ $row->deleted_at ? $row->deleted_at->format('d M Y H:i') : '—' }}</td>
                                <td>{{ $row->deletedByUser->name ?? '—' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('student_deleted_history.show', $row->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('common.view') }}">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">{{ ___('common.no_data_available') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($data['records']->hasPages())
            <div class="card-footer">
                {!! $data['records']->links() !!}
            </div>
            @endif
        </div>
    </div>
@endsection
