{{-- Rendered by index and by AJAX search --}}
<div class="card ot-card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0">
                <i class="fa-solid fa-table-list me-2"></i>{{ ___('student_info.student_list') }}
            </h5>
            @if (@$data['students'] && $data['students']->total() > 0)
            <div class="total-badge">
                <i class="fa-solid fa-users me-2"></i>
                <span>Total: <strong>{{ $data['students']->total() }}</strong> students</span>
            </div>
            @endif
        </div>
    </div>
    @if (@$data['students'] && $data['students']->count() > 0)
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover student-table mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="min-width: 250px;">{{ ___('student_info.student_name') }}</th>
                        <th style="min-width: 150px;">{{ ___('academic.class') }}</th>
                        <th style="min-width: 80px;">{{ ___('student_info.section') }}</th>
                        <th style="min-width: 150px;">{{ ___('student_info.mobile_number') }}</th>
                        <th class="text-center" style="min-width: 100px;">{{ ___('common.status') }}</th>
                        @if (hasPermission('student_update') || hasPermission('student_delete'))
                            <th class="text-center" style="width: 150px;">{{ ___('common.action') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['students'] as $key => $row)
                    <tr id="row_{{ @$row->student->id }}">
                        <td class="text-center">
                            <span class="serial-number">{{ $data['students']->firstItem() + $key }}</span>
                        </td>
                        <td>
                            <div class="student-info">
                                <div class="student-details">
                                    <div class="student-name">{{ @$row->student->first_name }} {{ @$row->student->last_name }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="class-badge">
                                <i class="fa-solid fa-graduation-cap me-1"></i>{{ @$row->class->name }}
                            </span>
                        </td>
                        <td>
                            <span class="section-badge">
                                <i class="fa-solid fa-door-open me-1"></i>{{ @$row->section->name }}
                            </span>
                        </td>
                        <td>
                            <span class="mobile-number">
                                <i class="fa-solid fa-phone me-1"></i>{{ @$row->student->mobile }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if (@$row->student->status == App\Enums\Status::ACTIVE)
                                <span class="status-badge active">
                                    <i class="fa-solid fa-circle-check me-1"></i>{{ ___('common.active') }}
                                </span>
                            @else
                                <span class="status-badge inactive">
                                    <i class="fa-solid fa-circle-xmark me-1"></i>{{ ___('common.inactive') }}
                                </span>
                            @endif
                        </td>
                        @if (hasPermission('student_update') || hasPermission('student_delete'))
                            <td class="text-center">
                                <div class="action-buttons" style="display: flex; gap: 0.75rem; justify-content: center; align-items: center;">
                                    <a class="action-icon view-icon" href="{{ route('student.show',@$row->student->id) }}" title="{{ ___('common.show') }}" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; transition: all 0.3s ease; text-decoration: none; background: transparent;">
                                        <i class="fa-solid fa-eye" style="color: #3b82f6 !important; font-size: 1rem;"></i>
                                    </a>
                                    @if (hasPermission('student_read'))
                                    <a class="action-icon qr-icon" href="{{ route('student.qr-code', @$row->student->id) }}" title="{{ ___('common.qr_code') }}" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; transition: all 0.3s ease; text-decoration: none; background: transparent;">
                                        <i class="las la-qrcode" style="color: #8b5cf6 !important; font-size: 1.1rem;"></i>
                                    </a>
                                    @endif
                                    <a class="action-icon edit-icon" href="{{ route('student.edit', @$row->student->id) }}" title="{{ ___('common.edit') }}" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; transition: all 0.3s ease; text-decoration: none; background: transparent;">
                                        <i class="fa-solid fa-pen-to-square" style="color: #10b981 !important; font-size: 1rem;"></i>
                                    </a>
                                    <a class="action-icon delete-icon" href="javascript:void(0);" onclick="delete_row('student/delete', {{ @$row->student->id }})" title="{{ ___('common.delete') }}" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; transition: all 0.3s ease; text-decoration: none; background: transparent;">
                                        <i class="fa-solid fa-trash-can" style="color: #ef4444 !important; font-size: 1rem;"></i>
                                    </a>
                                </div>
                            </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($data['students']->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="pagination-info">
                <i class="fa-solid fa-circle-info me-1"></i>
                Showing <strong>{{ $data['students']->firstItem() }}</strong> to <strong>{{ $data['students']->lastItem() }}</strong> of <strong>{{ $data['students']->total() }}</strong> entries
            </div>
            <nav class="pagination-nav">
                {!! $data['students']->appends(\Request::capture()->except('page'))->links() !!}
            </nav>
        </div>
    </div>
    @endif
    @else
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fa-solid fa-inbox"></i>
            </div>
            <h5>{{ ___('common.no_data_available') }}</h5>
            <p>{{ ___('common.please_add_new_entity_regarding_this_table') }}</p>
            @if (hasPermission('student_create'))
            <a href="{{ route('student.create') }}" class="btn ot-btn-primary mt-3">
                <i class="fa-solid fa-plus me-2"></i>{{ ___('common.add_new') }}
            </a>
            @endif
        </div>
    </div>
    @endif
</div>
