@extends('backend.master')

@section('title')
    {{ @$data['title'] }}
@endsection
@section('content')
    <div class="page-content">

        {{-- Breadcrumb Area --}}
        <div class="page-header mb-4">
            <div class="row">
                <div class="col-sm-12">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h4 class="bradecrumb-title mb-2">
                                <i class="fa-solid fa-user-plus me-2"></i>{{ $data['title'] }}
                            </h4>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}">
                                        <i class="fa-solid fa-home me-1"></i>{{ ___('common.home') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('student.index') }}">
                                        <i class="fa-solid fa-users me-1"></i>{{ ___('student_info.student_list') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">{{ ___('common.add_new') }}</li>
                            </ol>
                        </div>
                        <div>
                            <a href="{{ route('student.index') }}" class="btn btn-outline-primary">
                                <i class="fa-solid fa-arrow-left me-2"></i>{{ ___('common.back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('student.store') }}" enctype="multipart/form-data" method="post" id="visitForm">
            @csrf
            
            {{-- Basic Information Section --}}
            <div class="card ot-card section-card mb-4">
                <div class="card-header">
                    <div class="section-header-content">
                        <div class="section-icon basic-info">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">{{ ___('student_info.basic_information') ?? 'Basic Information' }}</h5>
                            <p class="mb-0">Enter the student's personal details</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label for="admission_no" class="form-label">
                                <i class="fa-solid fa-id-card text-primary me-2"></i>{{ ___('student_info.admission_no') }}
                            </label>
                            <input class="form-control modern-input @error('admission_no') is-invalid @enderror" 
                                type="text" name="admission_no" id="admission_no"
                                placeholder="{{ ___('student_info.enter_admission_no') }}"
                                value="{{ old('admission_no') }}">
                            @error('admission_no')
                                <div class="invalid-feedback">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-4">
                            <label for="first_name" class="form-label">
                                <i class="fa-solid fa-signature text-primary me-2"></i>{{ ___('student_info.first_name') }} 
                                <span class="required-star">*</span>
                            </label>
                            <input class="form-control modern-input @error('first_name') is-invalid @enderror"
                                name="first_name" id="first_name" required
                                placeholder="{{ ___('student_info.enter_first_name') }}" 
                                value="{{ old('first_name') }}">
                            @error('first_name')
                                <div class="invalid-feedback">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-4">
                            <label for="last_name" class="form-label">
                                <i class="fa-solid fa-signature text-primary me-2"></i>{{ ___('student_info.last_name') }} 
                                <span class="required-star">*</span>
                            </label>
                            <input class="form-control modern-input @error('last_name') is-invalid @enderror"
                                name="last_name" id="last_name" required
                                placeholder="{{ ___('student_info.enter_last_name') }}" 
                                value="{{ old('last_name') }}">
                            @error('last_name')
                                <div class="invalid-feedback">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-4">
                            <label for="date_of_birth" class="form-label">
                                <i class="fa-solid fa-calendar-days text-success me-2"></i>{{ ___('common.date_of_birth') }}
                            </label>
                            <input type="date" class="form-control modern-input @error('date_of_birth') is-invalid @enderror"
                                name="date_of_birth" id="date_of_birth"
                                value="{{ old('date_of_birth') }}">
                            @error('date_of_birth')
                                <div class="invalid-feedback">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-4">
                            <label for="gender" class="form-label">
                                <i class="fa-solid fa-venus-mars text-info me-2"></i>{{ ___('common.gender') }}
                            </label>
                            <select class="form-select modern-input @error('gender') is-invalid @enderror"
                                name="gender" id="gender">
                                <option value="">{{ ___('student_info.select_gender') }}</option>
                                @foreach ($data['genders'] as $item)
                                    <option {{ old('gender') == $item->id ? 'selected':'' }} value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-4">
                            <label for="category" class="form-label">
                                <i class="fa-solid fa-tags text-warning me-2"></i>{{ ___('common.category') }}
                            </label>
                            <select class="form-select modern-input @error('category') is-invalid @enderror"
                                name="category" id="category">
                                <option value="">{{ ___('student_info.select_category') }}</option>
                                @foreach ($data['categories'] as $item)
                                    <option {{ old('category') == $item->id ? 'selected':'' }} value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="invalid-feedback">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-4">
                            <label for="residance_address" class="form-label">
                                <i class="fa-solid fa-location-dot text-danger me-2"></i>{{ ___('frontend.Residance_Address') ?? 'Residence Address' }}
                            </label>
                            <input name="residance_address" id="residance_address"
                                placeholder="{{ ___('frontend.Residance_Address') }}"
                                class="form-control modern-input" type="text"
                                value="{{ old('residance_address') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Academic Information Section --}}
            <div class="card ot-card section-card mb-4">
                <div class="card-header">
                    <div class="section-header-content">
                        <div class="section-icon academic-info">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">{{ ___('student_info.academic_information') ?? 'Academic Information' }}</h5>
                            <p class="mb-0">Assign class, section and admission details</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label for="class" class="form-label">
                                <i class="fa-solid fa-school text-primary me-2"></i>{{ ___('student_info.class') }} 
                                <span class="required-star">*</span>
                            </label>
                            <select id="getSections" required
                                class="form-select modern-input @error('class') is-invalid @enderror"
                                name="class">
                                <option value="">{{ ___('student_info.select_class') }}</option>
                                @foreach ($data['classes'] as $item)
                                    <option {{ old('class') == $item->id ? 'selected':'' }} value="{{ $item->class->id }}">{{ $item->class->name }}</option>
                                @endforeach
                            </select>
                            @error('class')
                                <div class="invalid-feedback">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-4">
                            <label for="section" class="form-label">
                                <i class="fa-solid fa-layer-group text-success me-2"></i>{{ ___('student_info.section') }} 
                                <span class="required-star">*</span>
                            </label>
                            <select id="section" required
                                class="form-select sections modern-input @error('section') is-invalid @enderror"
                                name="section">
                                <option value="">{{ ___('student_info.select_section') }}</option>
                            </select>
                            @error('section')
                                <div class="invalid-feedback">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-4">
                            <label for="admission_date" class="form-label">
                                <i class="fa-solid fa-calendar-check text-info me-2"></i>{{ ___('student_info.admission_date') }} 
                                <span class="required-star">*</span>
                            </label>
                            <input type="date" required
                                class="form-control modern-input @error('admission_date') is-invalid @enderror"
                                name="admission_date" id="admission_date"
                                value="{{ old('admission_date') }}">
                            @error('admission_date')
                                <div class="invalid-feedback">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Information Section --}}
            <div class="card ot-card section-card mb-4">
                <div class="card-header">
                    <div class="section-header-content">
                        <div class="section-icon contact-info">
                            <i class="fa-solid fa-address-book"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">{{ ___('student_info.contact_information') ?? 'Contact Information' }}</h5>
                            <p class="mb-0">Parent and student contact details</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label for="mobile" class="form-label">
                                <i class="fa-solid fa-mobile-screen-button text-primary me-2"></i>{{ 'Parent Mobile' }}
                            </label>
                            <input class="form-control modern-input @error('mobile') is-invalid @enderror"
                                name="mobile" id="mobile" type="tel"
                                placeholder="{{ ___('student_info.enter_mobile') }}" 
                                value="{{ old('mobile') }}">
                            @error('mobile')
                                <div class="invalid-feedback">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-4">
                            <label for="second_mobile" class="form-label">
                                <i class="fa-solid fa-phone text-success me-2"></i>{{ 'Second Mobile Number' }}
                            </label>
                            <input class="form-control modern-input @error('second_mobile') is-invalid @enderror"
                                name="second_mobile" id="second_mobile" type="tel"
                                placeholder="{{ 'Enter Second Mobile' }}" 
                                value="{{ old('second_mobile') }}">
                            @error('second_mobile')
                                <div class="invalid-feedback">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-4">
                            <label for="email" class="form-label">
                                <i class="fa-solid fa-envelope text-danger me-2"></i>{{ ___('common.email') }}
                            </label>
                            <input class="form-control modern-input @error('email') is-invalid @enderror"
                                name="email" id="email" type="email"
                                placeholder="{{ ___('student_info.enter_email') }}" 
                                value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="card ot-card action-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="action-buttons-wrapper">
                                <div class="required-info">
                                    <i class="fa-solid fa-circle-info me-2"></i>
                                    Fields marked with <span class="required-star">*</span> are required
                                </div>
                                <div class="action-buttons">
                                    <a href="{{ route('student.index') }}" class="btn btn-outline-secondary btn-lg">
                                        <i class="fa-solid fa-xmark me-2"></i>{{ ___('common.cancel') ?? 'Cancel' }}
                                    </a>
                                    <button type="submit" class="btn btn-outline-primary btn-lg">
                                        <i class="fa-solid fa-check me-2"></i>{{ ___('common.submit') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
@endsection

@push('style')
<style>
    /* General Layout */
    .page-content {
        background: #f5f7fa;
        padding: 2rem 0;
    }

    /* Breadcrumb */
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

    /* Cards */
    .ot-card {
        border: none;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        overflow: hidden;
        background: white;
        transition: all 0.3s ease;
    }

    .section-card {
        margin-bottom: 1.5rem;
    }

    .section-card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }

    /* Section Headers */
    .section-card .card-header {
        background: white;
        border-bottom: 3px solid #e9ecef;
        padding: 1.5rem;
    }

    .section-header-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .section-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        flex-shrink: 0;
    }

    .section-icon.basic-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .section-icon.academic-info {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .section-icon.contact-info {
        background: linear-gradient(135deg, #2196F3 0%, #00BCD4 100%);
    }

    .section-header-content h5 {
        font-size: 1.15rem;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
    }

    .section-header-content p {
        font-size: 0.875rem;
        color: #6c757d;
        margin: 0;
    }

    /* Card Body */
    .section-card .card-body {
        padding: 2rem;
    }

    /* Form Elements */
    .form-label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
    }

    .form-label i {
        width: 20px;
    }

    .required-star {
        color: #ef4444;
        font-weight: 700;
        margin-left: 0.25rem;
    }

    .modern-input {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    .modern-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        background: white;
        transform: translateY(-1px);
    }

    .modern-input:hover {
        border-color: #cbd5e0;
    }

    .form-select.modern-input {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23667eea' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-size: 16px 12px;
    }

    /* Invalid Feedback */
    .invalid-feedback {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: #ef4444;
        font-weight: 500;
    }

    .is-invalid {
        border-color: #ef4444 !important;
    }

    .is-invalid:focus {
        box-shadow: 0 0 0 0.2rem rgba(239, 68, 68, 0.15) !important;
    }

    /* Action Card */
    .action-card {
        background: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .action-card .card-body {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.5rem 2rem;
    }

    .action-buttons-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .required-info {
        color: #6c757d;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
    }

    /* Buttons */
    .btn {
        font-weight: 600;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .btn-outline-primary {
        border: 2px solid #667eea;
        color: #667eea;
        background: white;
    }

    .btn-outline-primary:hover {
        background: #667eea;
        border-color: #667eea;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
    }

    .btn-outline-secondary {
        border: 2px solid #6c757d;
        color: #6c757d;
        background: white;
    }

    .btn-outline-secondary:hover {
        background: #6c757d;
        border-color: #6c757d;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-content {
            padding: 1rem 0;
        }

        .bradecrumb-title {
            font-size: 1.25rem;
        }

        .section-card .card-body {
            padding: 1.5rem;
        }

        .action-buttons-wrapper {
            flex-direction: column;
            align-items: stretch;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }

        .section-header-content {
            gap: 0.75rem;
        }

        .section-icon {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }
    }

    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .section-card {
        animation: fadeIn 0.5s ease-out;
    }

    .section-card:nth-child(2) {
        animation-delay: 0.1s;
    }

    .section-card:nth-child(3) {
        animation-delay: 0.2s;
    }

    .action-card {
        animation: fadeIn 0.5s ease-out;
        animation-delay: 0.3s;
    }
</style>
@endpush

@push('script')
<script>
    $(document).ready(function() {
        // Any existing functionality can go here
    });
</script>
@endpush
