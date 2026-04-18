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
                                href="{{ route('marks-register.index') }}">{{ $data['title'] }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ ___('common.add_new') }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}
        <div class="card ot-card">
            <div class="card-body">
                <form action="{{ route('marks-register.store') }}"  enctype="multipart/form-data"  method="post" id="markRegister">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-lg-12">
                            <div class="row">

                                <div class="col-md-3 mb-3">
                                    <label for="validationServer04" class="form-label">{{ ___('student_info.class') }} <span
                                        class="fillable">*</span></label>
                                    <select id="getSections"
                                        class="nice-select niceSelect bordered_style wide class @error('class') is-invalid @enderror"
                                        name="class" id="validationServer04"
                                        aria-describedby="validationServer04Feedback">
                                        <option value="">{{ ___('student_info.select_class') }}</option>
                                        @foreach ($data['classes'] as $item)
                                            <option {{ old('class') == $item->id ? 'selected' : '' }}
                                                value="{{ $item->class->id }}">{{ $item->class->name }}
                                        @endforeach
                                        </option>
                                    </select>

                                    @error('class')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                               
                               
                                <div class="col-lg-3">
                                    <label for="validationServer04" class="form-label">{{ ___('examination.exam_type') }}
                                        <span class="fillable">*</span></label>
                                    <select class="exam_types nice-select niceSelect bordered_style wide @error('exam_type') is-invalid @enderror" name="exam_type">
                                        <option value="">{{ ___('examination.select_exam_type') }}</option>
                                      </select>
                                      @error('exam_type')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="validationServer04" class="form-label">{{ ___('academic.subject') }} <span
                                        class="fillable">*</span></label>
                                    <select id="subjectt"
                                        class="nice-select niceSelect subjects_ bordered_style wide @error('subject') is-invalid @enderror"
                                        name="subject" id="validationServer04"
                                        aria-describedby="validationServer04Feedback">
                                        <option value="">{{ ___('examination.select_subject') }}</option>
                                        @foreach ($data['subjects'] as $item)
                                            <option {{ old('subject') == $item->id ? 'selected' : '' }}
                                                value="{{ $item->id }}">{{ $item->name }}
                                        @endforeach
                                       
                                    </select>

                                    @error('subject')
                                        <div id="validationServer04Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                
{{--                                <div class="col-md-12 mt-24">--}}
{{--                                    <div class="table-responsive">--}}
{{--                                        <table class="table table-bordered role-table" id="students_table">--}}
{{--                                            <thead class="thead">--}}
{{--                                                <tr>--}}
{{--                                                    <th>{{ ___('student_info.student_name') }}</th>--}}
{{--                                                    <th>{{ ___('examination.total_mark') }}</th>--}}
{{--                                                    <th>{{ ___('examination.mark_distribution') }}</th>--}}
{{--                                                </tr>--}}
{{--                                            </thead>--}}
{{--                                            <tbody class="tbody"></tbody>--}}
{{--                                        </table>--}}
{{--                                    </div>--}}
{{--                                </div>--}}

                                <div class="row ">
                                    <div class="col-lg-12">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="table-responsive">
                                                    <table class="table school_borderLess_table table_border_hide2" id="student-document">
                                                        <thead>
                                                        <tr>
                                                           
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
                                                    <button class="btn btn-lg ot-btn-primary"><span><i class="fa-solid fa-save"></i>
                                        </span>{{ ___('common.submit') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="">
                                                <p>Note : Below it is the format of excel to upload.</p>
                                            </div>
                                            <div class="row">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                    <select id="classSelector" class="form-control">
                                                        <option value="">{{ ___('student_info.select_class') }}</option>
                                                        @foreach ($data['classes'] as $item)
                                                            <option {{ old('class') == $item->id ? 'selected' : '' }}
                                                                    value="{{ $item->class->id }}">{{ $item->class->name }}
                                                        @endforeach
                                                    </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                    <select id="sectionSelector" class="form-control">
                                                        <option value="">{{ 'Sections' }}</option>
                                                        @foreach ($data['sections'] as $item)
                                                            <option {{ old('section') == $item->id ? 'selected' : '' }}
                                                                    value="{{ $item->id }}">{{ $item->name }}
                                                        @endforeach
                                                    </select>
                                                    </div>
                                                   

                                                    <a id="downloadLink" href="#" class="btn btn-outline-warning mt-3">
                                                        Download Exams Result Format
                                                    </a>
                                                </div>
{{--                                                <a href="{{ url('/export-exams') }}" class="btn btn-outline-warning">Download Exams Result Format</a>--}}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-24">
                                    <div class="text-end">
                                        <button class="btn btn-lg ot-btn-primary"><span><i class="fa-solid fa-save"></i>
                                            </span>{{ ___('common.submit') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>

    <!-- <script>
        document.getElementById('classSelector').addEventListener('change', function () {
            // Get the selected class value
            const selectedClass = this.value;

            // Find the download link
            const downloadLink = document.getElementById('downloadLink');

            // Update the href dynamically
            if (selectedClass) {
                downloadLink.href = `/export-exams?class=${selectedClass}`;
            } else {
                downloadLink.href = "#"; // Reset if no class is selected
            }
        });
    </script> -->
    <script>
    function updateDownloadLink() {
        const selectedClass = document.getElementById('classSelector').value;
        const selectedSection = document.getElementById('sectionSelector').value;
        const downloadLink = document.getElementById('downloadLink');

        if (selectedClass && selectedSection) {
            downloadLink.href = `/export-exams?class=${encodeURIComponent(selectedClass)}&section=${encodeURIComponent(selectedSection)}`;
            downloadLink.classList.remove('disabled');
        } else {
            downloadLink.href = "#";
            downloadLink.classList.add('disabled');
        }
    }

    document.getElementById('classSelector').addEventListener('change', updateDownloadLink);
    document.getElementById('sectionSelector').addEventListener('change', updateDownloadLink);
    document.addEventListener('DOMContentLoaded', updateDownloadLink);
</script>

<style>
    .btn.disabled {
        pointer-events: none;
        opacity: 0.5;
    }
</style>
@endsection
