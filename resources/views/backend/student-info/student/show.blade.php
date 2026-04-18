@extends('backend.master')

@section('title')
    {{ @$data->first_name }} {{ @$data->last_name }} - {{ ___('report.details_view') }}
@endsection

@section('content')
    <div class="page-content">
        {{-- Breadcrumb Area --}}
        <div class="page-header mb-4">
            <div class="row">
                <div class="col-sm-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="bradecrumb-title mb-2">
                                <i class="fa-solid fa-user-graduate me-2"></i>{{ ___('common.Student Details') }}
                            </h4>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}">
                                        <i class="fa-solid fa-home me-1"></i>{{ ___('common.home') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('student.index') }}">{{ ___('student_info.students') }}</a>
                                </li>
                                <li class="breadcrumb-item active">{{ @$data->first_name }} {{ @$data->last_name }}</li>
                            </ol>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('student.index') }}" class="btn btn-lg btn-outline-secondary">
                                <i class="fa-solid fa-arrow-left me-2"></i>{{ ___('common.back') }}
                            </a>
                            <a href="{{ route('student.edit', @$data->id) }}" class="btn btn-lg ot-btn-primary">
                                <i class="fa-solid fa-pen-to-square me-2"></i>{{ ___('common.edit') }}
                            </a>
                            @if (hasPermission('student_delete'))
                            <form action="{{ route('student.delete_with_history', @$data->id) }}" method="post" class="d-inline" onsubmit="return confirm('{{ ___('alert.are_you_sure_delete_student_and_move_to_history') ?? 'Are you sure? This will move the student and their fees data to history and then delete the student. This cannot be undone.' }}');">
                                @csrf
                                <button type="submit" class="btn btn-lg btn-danger">
                                    <i class="fa-solid fa-trash-can me-2"></i>{{ ___('common.delete_student') ?? 'Delete student' }}
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Profile Header Card --}}
        <div class="card ot-card profile-header-card mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="student-name mb-2">
                            <i class="fa-solid fa-user-circle me-2"></i>
                            {{ @$data->first_name }} {{ @$data->last_name }}
                        </h2>
                        <p class="text-muted mb-0">
                            <i class="fa-solid fa-id-card me-2"></i>{{ ___('student_info.admission_no') }}: <strong>{{ @$data->admission_no }}</strong>
                        </p>
                    </div>
                    <div>
                        @if (@$data->status == App\Enums\Status::ACTIVE)
                            <span class="status-badge active">
                                <i class="fa-solid fa-check-circle me-2"></i>{{ ___('common.active') }}
                            </span>
                        @else
                            <span class="status-badge inactive">
                                <i class="fa-solid fa-times-circle me-2"></i>{{ ___('common.inactive') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Personal Information --}}
            <div class="col-lg-6 mb-4">
                <div class="card ot-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-user me-2"></i>{{ ___('common.Personal Information') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="detail-item">
                            <strong><i class="fa-solid fa-signature text-primary me-2"></i>{{ ___('staff.first_name') }}:</strong>
                            <span>{{ @$data->first_name }}</span>
                        </div>

                        <div class="detail-item">
                            <strong><i class="fa-solid fa-signature text-primary me-2"></i>{{ ___('staff.last_name') }}:</strong>
                            <span>{{ @$data->last_name }}</span>
                        </div>

                        <div class="detail-item">
                            <strong><i class="fa-solid fa-calendar text-success me-2"></i>{{ ___('common.date_of_birth') }}:</strong>
                            <span>{{ @$data->dob }}</span>
                        </div>

                        <div class="detail-item">
                            <strong><i class="fa-solid {{ @$data->gender->name == 'Male' ? 'fa-mars' : 'fa-venus' }} text-info me-2"></i>{{ ___('staff.genders') }}:</strong>
                            <span>{{ @$data->gender->name }}</span>
                        </div>

                        <div class="detail-item">
                            <strong><i class="fa-solid fa-pray text-warning me-2"></i>{{ ___('student_info.religion') }}:</strong>
                            <span>{{ @$data->religion->name }}</span>
                        </div>

                        <div class="detail-item">
                            <strong><i class="fa-solid fa-map-marker-alt text-danger me-2"></i>{{ ___('frontend.place_of_birth') }}:</strong>
                            <span>{{ @$data->place_of_birth ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="col-lg-6 mb-4">
                <div class="card ot-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-address-book me-2"></i>{{ ___('common.Contact Information') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="detail-item">
                            <strong><i class="fa-solid fa-phone text-primary me-2"></i>{{ ___('student_info.mobile') }}:</strong>
                            <span>{{ @$data->mobile }}</span>
                        </div>

                        <div class="detail-item">
                            <strong><i class="fa-solid fa-envelope text-danger me-2"></i>{{ ___('common.email') }}:</strong>
                            <span>{{ @$data->email }}</span>
                        </div>

                        <div class="detail-item">
                            <strong><i class="fa-solid fa-home text-info me-2"></i>{{ ___('frontend.residance_address') }}:</strong>
                            <span>{{ @$data->residance_address ?? 'N/A' }}</span>
                        </div>

                        <div class="detail-item">
                            <strong><i class="fa-solid fa-user-friends text-warning me-2"></i>Parent Name:</strong>
                            <span>{{ @$data->parent->guardian_name ?? 'N/A' }}</span>
                        </div>

                        <div class="detail-item">
                            <strong><i class="fa-solid fa-phone-alt text-success me-2"></i>Parent Mobile:</strong>
                            <span>{{ @$data->parent->guardian_mobile ?? 'N/A' }}</span>
                        </div>

                        <div class="detail-item">
                            <strong><i class="fa-solid fa-envelope-open text-secondary me-2"></i>Parent Email:</strong>
                            <span>{{ @$data->parent->guardian_email ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Academic Information --}}
            <div class="col-lg-12 mb-4">
                <div class="card ot-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-book-open me-2"></i>{{ ___('common.Academic Information') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <strong><i class="fa-solid fa-calendar-check text-primary me-2"></i>{{ ___('student_info.admission_date') }}:</strong>
                                    <span>{{ dateFormat(@$data->admission_date) }}</span>
                                </div>

                                <div class="detail-item">
                                    <strong><i class="fa-solid fa-graduation-cap text-success me-2"></i>{{ ___('student_info.class') }}:</strong>
                                    <span>{{ @$data->session_class_student->class->name ?? 'N/A' }}</span>
                                </div>

                                <div class="detail-item">
                                    <strong><i class="fa-solid fa-door-open text-info me-2"></i>{{ ___('student_info.section') }}:</strong>
                                    <span>{{ @$data->session_class_student->section->name ?? 'N/A' }}</span>
                                </div>

                                <div class="detail-item">
                                    <strong><i class="fa-solid fa-id-badge text-warning me-2"></i>Roll No:</strong>
                                    <span>{{ @$data->roll_no ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <strong><i class="fa-solid fa-school text-warning me-2"></i>{{ ___('frontend.attend_school_previously') }}:</strong>
                                    <span>
                                        @if(@$data->previous_school)
                                            <span class="badge bg-success">{{ ___('student_info.yes') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ___('student_info.no') }}</span>
                                        @endif
                                    </span>
                                </div>

                                @if(@$data->previous_school)
                                <div class="detail-item">
                                    <strong><i class="fa-solid fa-info-circle text-danger me-2"></i>{{ ___('frontend.previous_school_info') }}:</strong>
                                    <span>{{ @$data->previous_school_info ?? 'N/A' }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
<style>
    /* Page Layout */
    .page-content {
        background: #f8f9fa;
        padding: 2rem 0;
    }

    /* Profile Header Card */
    .profile-header-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        overflow: hidden;
    }

    .profile-header-card .card-body {
        background: white;
    }

    .student-name {
        font-size: 1.75rem;
        font-weight: 700;
        color: #2d3748;
        display: flex;
        align-items: center;
    }

    .student-name i {
        color: #667eea;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-size: 1rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }

    .status-badge.active {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }

    .status-badge.inactive {
        background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        color: white;
    }

    .status-badge:hover {
        transform: scale(1.05);
    }

    /* Cards */
    .ot-card {
        border: none;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        background: white;
    }

    .ot-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    .ot-card .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 3px solid #667eea;
        padding: 1.25rem 1.5rem;
    }

    .ot-card .card-header h5 {
        font-weight: 700;
        color: #2d3748;
        font-size: 1.15rem;
        display: flex;
        align-items: center;
    }

    .ot-card .card-header h5 i {
        color: #667eea;
    }

    .ot-card .card-body {
        padding: 1.75rem;
    }

    /* Detail Items - Title: Value Format */
    .detail-item {
        padding: 1rem 0;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        transition: all 0.3s ease;
    }

    .detail-item:last-child {
        border-bottom: none;
    }

    .detail-item:hover {
        background: #f8f9fa;
        padding-left: 1rem;
        padding-right: 1rem;
        margin-left: -1rem;
        margin-right: -1rem;
        border-radius: 8px;
    }

    .detail-item strong {
        color: #495057;
        font-weight: 600;
        font-size: 0.95rem;
        min-width: 200px;
        display: flex;
        align-items: center;
    }

    .detail-item strong i {
        width: 20px;
    }

    .detail-item span {
        color: #2d3748;
        font-weight: 500;
        font-size: 0.95rem;
        flex: 1;
    }

    /* Breadcrumb */
    .page-header {
        margin-bottom: 2rem;
    }

    .bradecrumb-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
    }

    .breadcrumb {
        background: transparent;
        padding: 0;
        margin: 0;
    }

    .breadcrumb-item a {
        color: #667eea;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .breadcrumb-item a:hover {
        color: #764ba2;
    }

    .breadcrumb-item.active {
        color: #6c757d;
    }

    /* Buttons */
    .btn {
        transition: all 0.3s ease;
        font-weight: 600;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .ot-btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
    }

    .ot-btn-primary:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    }

    /* Badge Styles */
    .badge {
        padding: 0.4rem 0.8rem;
        font-weight: 600;
        font-size: 0.85rem;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .student-name {
            font-size: 1.5rem;
        }

        .profile-header-card .card-body {
            text-align: center;
        }

        .profile-header-card .card-body > div:last-child {
            margin-top: 1rem;
        }
    }

    @media (max-width: 768px) {
        .detail-item {
            flex-direction: column;
            gap: 0.5rem;
        }

        .detail-item strong {
            min-width: auto;
        }
    }
</style>
@endpush
