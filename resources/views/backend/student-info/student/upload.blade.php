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
                    <h4 class="bradecrumb-title mb-1">{{ $data['title'] }}</h4>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                            <li class="breadcrumb-item" aria-current="page"><a
                                    href="{{ route('student.index') }}">{{ ___('student_info.student_list') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ 'Upload Students Details' }}</li>
                        </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}
        <div class="card ot-card">
            <div class="card-body">
                <form action="{{ route('student.uploadStudent') }}" enctype="multipart/form-data"   method="post"
                    id="visitForm">
                    @csrf
                <div class="row ">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table school_borderLess_table table_border_hide2" id="student-document">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ ___('common.name') }} <span
                                                        class="text-danger"></span>
                                                    @if ($errors->any())
                                                        @if ($errors->has('document_names.*'))
                                                            <span class="text-danger">{{ 'the fields are required' }}
                                                        @endif
                                                    @endif
                                                </th>
                                                <th scope="col">
                                                    {{ 'Students List' }}
                                                    <span class="text-danger"></span>
                                                    @if ($errors->any())
                                                        @if ($errors->has('document_files.*'))
                                                            <span class="text-danger">{{ 'The fields are required' }}
                                                        @endif
                                                    @endif
                                                </th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                        <tr id="document-file">
                                            <td>
                                                <select name="document_format" class="form-control ot-input min_width_200" required>
                                                    <option value="1" {{ old('document_format', '1') == '1' ? 'selected' : '' }}>Format 1</option>
                                                    <option value="2" {{ old('document_format') == '2' ? 'selected' : '' }}>Format 2</option>
                                                </select>
{{--                                                <input type="hidden" name="document_rows" value="">--}}

                                            </td>
                                            <td>
                                                <div class="school_primary_fileUplaoder mb-3">
                                                    <label for="awesomefile" class="filelabel">{{ ___('common.browse') }}</label>
                                                    <input type="file" name="document_files" id="awesomefile" >
                                                    <input type="text" class="redonly_input" readonly placeholder="{{ ___('student_info.upload_documents') }}">
                                                </div>
                                            </td>

                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="text-end">
                                    <button class="btn btn-lg btn-outline-primary"><span><i class="fa-solid fa-save"></i>
                                        </span>{{ ___('common.submit') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <strong>Format 1:</strong> current implementation (includes fees assignment).<br>
                                    <strong>Format 2:</strong> class-only student import (no fees assignment).<br>
                                    <small><strong>Note:</strong> Choose the matching format before uploading the file.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <a href="{{ route('student.downloadTemplate') }}" class="btn btn-outline-warning">
                                    <i class="fas fa-download"></i> Download Students Excel Format
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        var fileInp1 = document.getElementById("fileBrouse1");
        if (fileInp1) {
            fileInp1.addEventListener("change", showFileName);

            function showFileName(event) {
                var fileInp = event.srcElement;
                var fileName = fileInp.files[0].name;
                document.getElementById("placeholder1").placeholder = fileName;
            }
        }
        function checkCheckboxState() {
            var isChecked = $('#previous_school').prop('checked');
            console.log(isChecked)
            if (isChecked) {
                $('#previous_school_info').removeClass('d-none');
                $('#previous_school_doc').removeClass('d-none');
            } else {
                $('#previous_school_info').addClass('d-none');
                $('#previous_school_doc').addClass('d-none');
            }
        }

        $('#previous_school').change(checkCheckboxState);
        checkCheckboxState();
    });
</script>
@endpush
