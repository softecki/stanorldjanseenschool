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
                                <i class="fa-solid fa-users me-2"></i>{{ $data['title'] }}
                            </h4>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}">
                                        <i class="fa-solid fa-home me-1"></i>{{ ___('common.home') }}
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">{{ $data['title'] }}</li>
                            </ol>
                        </div>
                        @if (hasPermission('student_create'))
                        <div class="d-flex gap-2">
                            <a href="{{ route('student.upload') }}" class="btn btn-outline-primary">
                                <i class="fa-solid fa-upload me-2"></i>{{ 'Upload' }}
                            </a>
                            <a href="{{ route('student.create') }}" class="btn ot-btn-primary">
                                <i class="fa-solid fa-plus me-2"></i>{{ ___('common.add_new') }}
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="card ot-card filter-card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fa-solid fa-filter me-2"></i>{{ ___('common.Filter') ?? 'Filter Students' }}
                </h5>
            </div>
            <div class="card-body" style="overflow: visible;">
                <form action="{{ route('student.search') }}" method="post" id="marksheed" enctype="multipart/form-data">
                    @csrf
                    <div class="row align-items-end" style="overflow: visible;">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fa-solid fa-school me-1 text-primary"></i>{{ ___('student_info.class') }}
                            </label>
                            <select id="getSections" class="form-select @error('class') is-invalid @enderror" name="class">
                                <option value="">{{ ___('student_info.select_class') }}</option>
                                @foreach ($data['classes'] as $item)
                                    <option {{ old('class', @$data['request']->class) == $item->class->id ? 'selected' : '' }}
                                        value="{{ $item->class->id }}">{{ $item->class->name }}</option>
                                @endforeach
                            </select>
                            @error('class')
                                <div class="invalid-feedback">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fa-solid fa-layer-group me-1 text-success"></i>{{ ___('student_info.section') }}
                            </label>
                            <select class="sections section form-select @error('section') is-invalid @enderror" name="section">
                                <option value="">{{ ___('student_info.select_section') }}</option>
                                @foreach ($data['sections'] as $item)
                                    <option {{ old('section', @$data['request']->section) == $item->section->id ? 'selected' : '' }}
                                        value="{{ $item->section->id }}">{{ $item->section->name }}</option>
                                @endforeach
                            </select>
                            @error('section')
                                <div class="invalid-feedback">
                                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fa-solid fa-user me-1 text-info"></i>{{ ___('student_info.student_name') ?? 'Student Name' }}
                            </label>
                            <input class="form-control"
                                name="name" id="name" autocomplete="off"
                                placeholder="{{ ___('student_info.enter_student_name') ?? 'Enter student name (first or last name)' }}"
                                value="{{ old('name', @$data['request']->name) }}">
                        </div>

                        <div class="col-md-2 mb-3">
                            <button class="btn ot-btn-primary w-100" type="submit">
                                <i class="fa-solid fa-search me-2"></i>{{ ___('common.Search') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table Section (replaced by live search via AJAX) --}}
        <div id="student-table-container">
            @include('backend.student-info.student.partials.table', compact('data'))
        </div>
        <div id="student-table-spinner" class="text-center py-5 d-none">
            <span class="spinner-border spinner-border-lg text-primary" role="status"></span>
            <p class="mt-2 mb-0 text-muted">{{ ___('common.loading') ?? 'Loading...' }}</p>
        </div>

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

    .ot-card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
    }

    .ot-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 1.25rem 1.5rem;
    }

    .ot-card .card-header h5 {
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
    }

    .filter-card .card-header {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    /* Total Badge */
    .total-badge {
        display: inline-flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1rem;
        border-radius: 50px;
        color: white;
        font-size: 0.95rem;
        backdrop-filter: blur(10px);
    }

    .total-badge strong {
        color: white;
        font-weight: 700;
    }

    /* Form Elements */
    .form-label {
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .form-select,
    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 0.625rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .form-select:focus,
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }

    /* Buttons */
    .btn {
        font-weight: 600;
        padding: 0.625rem 1.25rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .ot-btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
    }

    .ot-btn-primary:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        color: white;
    }

    /* Table Styles */
    .student-table {
        font-size: 0.9rem;
    }

    .student-table thead {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .student-table thead th {
        font-weight: 700;
        color: #2d3748;
        padding: 1rem;
        border: none;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }

    .student-table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    .student-table tbody tr {
        transition: all 0.3s ease;
    }

    .student-table tbody tr:hover {
        background: #f8f9fa;
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    /* Serial Number */
    .serial-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: #e9ecef;
        border-radius: 50%;
        font-weight: 700;
        color: #495057;
        font-size: 0.85rem;
    }

    /* Admission Badge */
    .admission-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 0.75rem;
        background: #f0f4ff;
        color: #667eea;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    /* Student Info */
    .student-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .student-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .student-details {
        flex: 1;
    }

    .student-name {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.25rem;
        font-size: 0.95rem;
    }

    .student-email {
        font-size: 0.8rem;
        color: #6c757d;
    }

    /* Class Badge */
    .class-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 0.85rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    /* Section Badge */
    .section-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 0.85rem;
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    /* Gender Badge */
    .gender-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 0.85rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .gender-badge.male {
        background: #e3f2fd;
        color: #1976d2;
    }

    .gender-badge.female {
        background: #fce4ec;
        color: #c2185b;
    }

    /* Mobile Number */
    .mobile-number {
        display: inline-flex;
        align-items: center;
        color: #495057;
        font-size: 0.9rem;
    }

    .mobile-number i {
        color: #6c757d;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 0.85rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .status-badge.active {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }

    .status-badge.inactive {
        background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        color: white;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
        align-items: center;
    }

    .action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        transition: all 0.3s ease;
        text-decoration: none;
        background: transparent;
    }

    .action-icon i {
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    /* View Icon - Blue */
    .action-buttons .action-icon.view-icon i,
    a.action-icon.view-icon i.fa-eye {
        color: #3b82f6 !important;
    }

    .action-buttons .action-icon.view-icon:hover {
        background: rgba(59, 130, 246, 0.1) !important;
        transform: scale(1.15);
    }

    .action-buttons .action-icon.view-icon:hover i,
    a.action-icon.view-icon:hover i.fa-eye {
        color: #2563eb !important;
    }

    /* Edit Icon - Green */
    .action-buttons .action-icon.edit-icon i,
    a.action-icon.edit-icon i.fa-pen-to-square {
        color: #10b981 !important;
    }

    .action-buttons .action-icon.edit-icon:hover {
        background: rgba(16, 185, 129, 0.1) !important;
        transform: scale(1.15);
    }

    .action-buttons .action-icon.edit-icon:hover i,
    a.action-icon.edit-icon:hover i.fa-pen-to-square {
        color: #059669 !important;
    }

    /* Delete Icon - Red */
    .action-buttons .action-icon.delete-icon i,
    a.action-icon.delete-icon i.fa-trash-can {
        color: #ef4444 !important;
    }

    .action-buttons .action-icon.delete-icon:hover {
        background: rgba(239, 68, 68, 0.1) !important;
        transform: scale(1.15);
    }

    .action-buttons .action-icon.delete-icon:hover i,
    a.action-icon.delete-icon:hover i.fa-trash-can {
        color: #dc2626 !important;
    }

    /* Pagination */
    .card-footer {
        background: #f8f9fa;
        border-top: 2px solid #e9ecef;
        padding: 1.25rem 1.5rem;
    }

    .pagination-info {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .pagination-info strong {
        color: #2d3748;
        font-weight: 700;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-icon {
        font-size: 4rem;
        color: #cbd5e0;
        margin-bottom: 1.5rem;
    }

    .empty-state h5 {
        color: #2d3748;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    .empty-state p {
        color: #6c757d;
        margin-bottom: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-content {
            padding: 1rem 0;
        }

        .bradecrumb-title {
            font-size: 1.25rem;
        }

        .student-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .action-buttons {
            flex-direction: column;
        }

        .total-badge {
            margin-top: 0.75rem;
        }

        .pagination-info {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@push('script')
    @include('backend.partials.delete-ajax')
    <script>
(function() {
    var form = document.getElementById('marksheed');
    var container = document.getElementById('student-table-container');
    var tableSpinner = document.getElementById('student-table-spinner');
    var searchUrl = '{{ route("student.search") }}';
    var searchTimeout = null;

    if (!form || !container) return;

    function setLoading(show) {
        if (tableSpinner) tableSpinner.classList.toggle('d-none', !show);
        if (container) container.style.opacity = show ? '0.5' : '1';
    }

    function liveSearch(page) {
        var fd = new FormData(form);
        if (page) fd.set('page', page);
        setLoading(true);
        fetch(searchUrl, {
            method: 'POST',
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(function(r) { return r.text(); })
        .then(function(html) {
            container.innerHTML = html;
            setLoading(false);
        })
        .catch(function() {
            setLoading(false);
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        liveSearch();
    });

    var nameInput = document.getElementById('name');
    if (nameInput) {
        nameInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() { liveSearch(1); }, 400);
        });
    }

    var classSelect = document.getElementById('getSections');
    var sectionSelect = form.querySelector('select[name="section"]');
    if (classSelect) classSelect.addEventListener('change', function() { liveSearch(1); });
    if (sectionSelect) sectionSelect.addEventListener('change', function() { liveSearch(1); });

    container.addEventListener('click', function(e) {
        var a = e.target.closest('.pagination a[href*="page="]');
        if (!a) return;
        e.preventDefault();
        var href = a.getAttribute('href') || a.href || '';
        var m = href.match(/page=(\d+)/);
        if (m) liveSearch(m[1]);
    });
})();
    </script>
@endpush
