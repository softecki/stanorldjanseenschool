@extends('backend.master')

@section('title')
    {{ @$data['title'] }}
@endsection
@section('content')
    <div class="page-content">

        {{-- bradecrumb Area S t a r t --}}
        <div class="page-header">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="bradecrumb-title mb-1">{{ $data['title'] }}</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                            <li class="breadcrumb-item" aria-current="page"><a
                                    href="{{ route('template.index') }}">{{ ___('common.templates') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $data['title'] }}</li>
                        </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}
        <div class="card ot-card">
            <div class="card-body">
                <form action="{{ route('smsmail.store') }}" enctype="multipart/form-data" method="post"
                    id="template-store">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label for="title" class="form-label ">{{ ___('common.title') }} <span
                                            class="fillable">*</span> </label>
                                    <input class="form-control ot-input" name="title"
                                        value="{{ old('title') }}" list="datalistOptions" id="title"
                                        placeholder="{{ ___('common.title') }}">
                               
                                    <div id="validationServer04Feedback" class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 mb-3">

                                    <label for="type" class="form-label">{{ ___('common.type') }} <span
                                            class="fillable">*</span></label>
                                    <select
                                        class="type nice-select niceSelect bordered_style wide"
                                        name="type" id="type" aria-describedby="validationServer04Feedback">
                                        <option value="{{ App\Enums\TemplateType::SMS }}"
                                            {{ old('type') == App\Enums\TemplateType::SMS ? 'selected' : '' }}>
                                            {{ ___('common.sms') }}</option>
                                        <option value="{{ App\Enums\TemplateType::MAIL }}"
                                            {{ old('type') == App\Enums\TemplateType::MAIL ? 'selected' : '' }}>
                                            {{ ___('common.mail') }}</option>
                                        </option>
                                    </select>

                                   
                                        <div id="validationServer04Feedback" class="invalid-feedback"></div>

                                </div>

                                {{-- <div class="col-md-6 mb-3  __sms">

                                    <label for="template_sms" class="form-label">{{ ___('common.template') }} <span
                                            class="fillable">*</span></label>
                                    <select
                                        class="template nice-select niceSelect bordered_style wide"
                                        name="template_sms" id="template_sms" aria-describedby="validationServer04Feedback">
                                        <option value="">{{ ___('common.select template') }}</option>
                                        @foreach ($data['templates'] as $template)
                                            @if ($template->type == 'sms')
                                                <option value="{{ $template->id }}">{{ $template->title }}</option>
                                            @endif
                                        @endforeach
                                    </select>

                                    <div id="validationServer04Feedback" class="invalid-feedback"></div>

                                </div> --}}

                                <div class="col-md-6 mb-3  __mail">

                                    <label for="template_mail" class="form-label">{{ ___('common.template') }} <span
                                            class="fillable">*</span></label>
                                    <select
                                        class="template nice-select niceSelect bordered_style wide"
                                        name="template_mail" id="template_mail"
                                        aria-describedby="validationServer04Feedback">
                                        <option value="">{{ ___('common.select template') }}</option>
                                        @foreach ($data['templates'] as $template)
                                            @if ($template->type == 'mail')
                                                <option value="{{ $template->id }}">{{ $template->title }}</option>
                                            @endif
                                        @endforeach
                                    </select>

                                        <div id="validationServer04Feedback" class="invalid-feedback"></div>

                                </div>



                                {{-- mail --}}
                                <div class="col-md-12 __mail">
                                    <label for="exampleDataList" class="form-label ">{{ ___('common.Attachment') }} <span
                                            class="fillable"></span></label>
                                    <div class="ot_fileUploader left-side mb-3">
                                        <input class="form-control" type="text"
                                            placeholder="{{ ___('common.Attachment') }}" readonly="" id="placeholder">
                                        <button class="primary-btn-small-input" type="button">
                                            <label class="btn btn-lg ot-btn-primary"
                                                for="fileBrouse">{{ ___('common.browse') }}</label>
                                            <input type="file" class="d-none form-control attachment" name="attachment"
                                                id="fileBrouse">
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3 __mail">
                                    <label for="mail_description"
                                        class="form-label ">{{ ___('common.mail_description') }}</label>
                                    <textarea
                                        class="form-control mail_description ot-textarea @error('mail_description') is-invalid @enderror"
                                        name="mail_description" list="datalistOptions" id="mail_description"
                                        placeholder="{{ ___('account.enter_description') }}">{{ old('mail_description') }}</textarea>
                                    @error('mail_description')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                {{-- sms --}}
                                <div class="col-md-12 mb-3 __sms">
                                    <label
                                        class="form-label ">{{ ___('common.sms_description') }}</label>
                                    <textarea  class="form-control sms_description ot-textarea @error('sms_description') is-invalid @enderror"
                                        name="sms_description" list="datalistOptions" id="sms_description" placeholder="Example: Hello {name}, unadaiwa kiasi cha {balance} lipa mapema.">{{ old('sms_description') }}</textarea>
                                    <small class="form-text text-muted">
                                        <strong>Placeholders:</strong> Use {name} for student name and {balance} for opening balance. Example: "Hello {name}, unadaiwa kiasi cha {balance} lipa mapema."
                                    </small>
                                    @error('sms_description')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                {{-- Excel Upload Option for SMS --}}
                                <div class="col-md-12 mb-3 __sms">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">Send SMS via Excel Upload (Optional)</h6>
                                                <small>Upload Excel file with student names. Phone numbers will be fetched automatically.</small>
                                            </div>
                                            <a href="{{ route('smsmail.downloadTemplate') }}" class="btn btn-sm btn-light">
                                                <i class="fa-solid fa-download"></i> Download Excel Template
                                            </a>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info mb-3">
                                                <strong>Excel Format:</strong>
                                                <table class="table table-bordered table-sm mt-2 mb-0">
                                                    <thead class="table-primary">
                                                        <tr>
                                                            <th>Name</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>CLASS 1 B: SHURAIMU MRISHO</td>
                                                        </tr>
                                                        <tr>
                                                            <td>CLASS 1 B: PIO AMOGAST MALLYA</td>
                                                        </tr>
                                                        <tr>
                                                            <td>HEAVENLIGHT PRAYGOD TESHA</td>
                                                        </tr>
                                                        <tr>
                                                            <td>JOHN DOE</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <small class="d-block mt-2">
                                                    <strong>Supported Formats:</strong><br>
                                                    • "CLASS [NUMBER] [SECTION]: STUDENT NAME" (e.g., "CLASS 1 B: SHURAIMU MRISHO")<br>
                                                    • "STUDENT NAME" (e.g., "HEAVENLIGHT PRAYGOD TESHA" or "JOHN DOE")
                                                </small>
                                            </div>
                                            <div class="mb-3">
                                                <label for="excel_file" class="form-label">Upload Excel File</label>
                                                <input type="file" class="form-control" name="excel_file" id="excel_file" accept=".xlsx,.xls,.csv">
                                                <small class="form-text text-muted">
                                                    The Excel file must have a "Name" column (case-sensitive). 
                                                    <a href="{{ route('smsmail.downloadTemplate') }}" class="text-primary">Download template</a> for the correct format.
                                                </small>
                                            </div>
                                            <button type="button" class="btn btn-outline-primary" id="preview-excel-btn" onclick="previewExcelSms()">
                                                <i class="fa-solid fa-eye"></i> Preview Students
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Preview Section --}}
                                <div class="col-md-12 mb-3 __sms" id="preview-section" style="display: none;">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">Preview - Students and Messages</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="preview-content">
                                                <p class="text-muted">Click "Preview Students" to see the list of students and their messages.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">

                                    <label for="user_type" class="form-label">Choose Group
                                        <span class="fillable">*</span></label>
                                    <select
                                        class="user_type nice-select niceSelect bordered_style wide"
                                        name="user_type" id="user_type"
                                        aria-describedby="validationServer04Feedback">
                                        <option value="{{ App\Enums\UserType::ROLE }}"
                                            {{ old('user_type') == App\Enums\UserType::ROLE ? 'selected' : '' }}>
                                            {{ ___('common.role') }}</option>
                                        <option value="{{ App\Enums\UserType::INDIVIDUAL }}"
                                            {{ old('user_type') == App\Enums\UserType::INDIVIDUAL ? 'selected' : '' }}>
                                            {{ ___('common.individual') }}</option>
                                        <option value="{{ App\Enums\UserType::CLASSSECTION }}"
                                            {{ old('user_type') == App\Enums\UserType::CLASSSECTION ? 'selected' : '' }}>
                                            {{ ___('common.class') }}</option>
                                    </select>

                                        <div id="validationServer04Feedback" class="invalid-feedback"></div>

                                </div>


                                <div class="col-lg-12 __role">
                                    <label for="validationServer04" class="form-label">{{ ___('common.roles') }}
                                        <span class="fillable">*</span></label>
                                    <select
                                        class="form-control role_ids select2_multy wide nice-select"
                                        name="role_ids" id="role_ids" multiple="multiple">
                                        <option value="">{{ ___('staff.select_role') }}</option>
                                        @foreach ($data['roles'] as $role)
                                            <option {{ old('role') == $role->id ? 'selected' : '' }}
                                                value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <div id="validationServer04Feedback" class="invalid-feedback"></div>
                                </div>


                                <div class="col-lg-6 col-md-6 mb-3 __individual">
                                    <label for="role" class="form-label">{{ ___('common.role') }} <span
                                            class="fillable">*</span></label>
                                    <select
                                        class="role nice-select niceSelect bordered_style wide"
                                        name="role" id="role"
                                        aria-describedby="validationServer04Feedback">
                                        <option value="">{{ ___('staff.select_role') }}</option>
                                        @foreach ($data['roles'] as $role)
                                            <option {{ old('role') == $role->id ? 'selected' : '' }}
                                                value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <div id="validationServer04Feedback" class="invalid-feedback"></div>
                               
                                </div>

                                <div class="col-lg-6 col-md-6 mb-3 __individual">
                                    <label for="users" class="form-label">{{ ___('common.user') }} <span
                                            class="fillable">*</span></label>
                                    <select
                                        class="form-control users select2_multy wide nice-select"
                                        name="users" id="users" multiple="multiple"
                                        aria-describedby="validationServer04Feedback">
                                        <option value="">{{ ___('staff.select_name') }}</option>

                                    </select>
                                        <div id="validationServer04Feedback" class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 mb-3 __class">
                                    <label for="getSections" class="form-label">{{ ___('student_info.class') }}
                                        <span class="fillable">*</span></label>
                                    <select id="getSections"
                                        class="nice-select niceSelect bordered_style wide class"
                                        name="class"
                                        aria-describedby="validationServer04Feedback">
                                        <option value="">{{ ___('student_info.select_class') }}</option>
                                        @foreach ($data['classes'] as $item)
                                            <option {{ old('class') == $item->id ? 'selected' : '' }}
                                                value="{{ $item->class->id }}">{{ $item->class->name }}
                                        @endforeach
                                        </option>
                                    </select>

                                        <div id="validationServer04Feedback" class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 mb-3 __class">

                                    <label for="validationServer04" class="form-label">{{ ___('student_info.section') }}
                                        <span class="fillable">*</span></label>
                                    <select id="section_ids"
                                        class="form-control sections select2_multy wide nice-select bordered_style"
                                        name="section_ids" aria-describedby="validationServer04Feedback"
                                        multiple="multiple">
                                        <option value="" disabled>{{ ___('examination.select_subject') }}</option>
                                    </select>

                                    <div id="validationServer04Feedback" class="invalid-feedback"></div>

                                </div>

                                <div class="col-md-12 mt-24">
                                    <div class="text-end">
                                        <button type="button" class="btn btn-lg ot-btn-primary"
                                            onclick="return smsMailSubmit(event)"><span><i class="fa-solid fa-save"></i>
                                            </span>{{ ___('common.submit') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ global_asset('backend') }}/assets/js/__sms_mail.js"></script>
    <script>
        function previewExcelSms() {
            const fileInput = document.getElementById('excel_file');
            const smsDescription = document.getElementById('sms_description').value;
            const previewSection = document.getElementById('preview-section');
            const previewContent = document.getElementById('preview-content');
            const previewBtn = document.getElementById('preview-excel-btn');

            if (!fileInput.files.length) {
                alert('Please select an Excel file first.');
                return;
            }

            if (!smsDescription.trim()) {
                alert('Please enter SMS description first.');
                return;
            }

            const formData = new FormData();
            formData.append('excel_file', fileInput.files[0]);
            formData.append('sms_description', smsDescription);
            formData.append('_token', '{{ csrf_token() }}');

            previewBtn.disabled = true;
            previewBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
            previewContent.innerHTML = '<p class="text-center"><i class="fa-solid fa-spinner fa-spin"></i> Processing Excel file...</p>';

            fetch('{{ route("smsmail.preview") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                previewBtn.disabled = false;
                previewBtn.innerHTML = '<i class="fa-solid fa-eye"></i> Preview Students';

                if (data.status) {
                    let html = '<div class="table-responsive"><table class="table table-bordered table-sm">';
                    html += '<thead><tr><th>#</th><th>Student Name</th><th>Opening Balance</th><th>Phone Number</th><th>Message Preview</th><th>Status</th></tr></thead>';
                    html += '<tbody>';

                    let foundCount = 0;
                    let notFoundCount = 0;

                    data.data.students.forEach((student, index) => {
                        const statusClass = student.phone ? 'text-success' : 'text-danger';
                        const statusText = student.phone ? 'Found' : 'Not Found';
                        const phoneDisplay = student.phone || 'N/A';
                        const balanceDisplay = student.balance ? new Intl.NumberFormat().format(student.balance) : '0';
                        const messagePreview = student.message_preview || data.data.message_template || '';

                        if (student.phone) foundCount++;
                        else notFoundCount++;

                        html += `<tr class="${student.phone ? '' : 'table-warning'}">`;
                        html += `<td>${index + 1}</td>`;
                        html += `<td>${student.name}</td>`;
                        html += `<td>${balanceDisplay}</td>`;
                        html += `<td>${phoneDisplay}</td>`;
                        html += `<td><small class="text-muted">${messagePreview}</small></td>`;
                        html += `<td><span class="${statusClass}">${statusText}</span></td>`;
                        html += `</tr>`;
                    });

                    html += '</tbody></table></div>';
                    html += `<div class="alert alert-info mt-3">`;
                    html += `<strong>Summary:</strong> Found: ${foundCount}, Not Found: ${notFoundCount}, Total: ${data.data.students.length}`;
                    html += `<br><small><strong>Note:</strong> Use {name} for student name and {balance} for opening balance in your message.</small>`;
                    html += `</div>`;

                    previewContent.innerHTML = html;
                    previewSection.style.display = 'block';

                    // Store preview data for sending
                    window.previewData = data.data;
                } else {
                    previewContent.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(error => {
                previewBtn.disabled = false;
                previewBtn.innerHTML = '<i class="fa-solid fa-eye"></i> Preview Students';
                previewContent.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                console.error('Error:', error);
            });
        }

        // Update preview when SMS description changes
        document.getElementById('sms_description').addEventListener('input', function() {
            if (window.previewData) {
                const previewSection = document.getElementById('preview-section');
                if (previewSection.style.display !== 'none') {
                    previewExcelSms();
                }
            }
        });
    </script>
@endpush
