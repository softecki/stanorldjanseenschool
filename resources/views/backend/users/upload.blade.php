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
                                    href="{{ route('users.upload') }}">{{ ___('student_info.student_list') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ 'Upload Students Details' }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}
        <div class="card ot-card">
            <div class="card-body">
                <form action="{{ route('users.uploadTeacher') }}" enctype="multipart/form-data"   method="post"
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
                                                    <input type="text" name="document_names"
                                                           class="form-control ot-input min_width_200 " placeholder="{{___('student_info.enter_name')}}" required>
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
                                        <button class="btn btn-lg ot-btn-primary"><span><i class="fa-solid fa-save"></i>
                                        </span>{{ ___('common.submit') }}</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="">
                                    <p>Note : Below it is the format of excel to upload.</p>
                                    <table class="table table-success table-striped">
                                        <thead>
                                        <tr>
                                            <th scope="col">first_name</th>
                                            <th scope="col">last_name</th>
                                            <th scope="col">email</th>
                                            <th scope="col">gender</th>
                                            <th scope="col">phone</th>
                                            <th scope="col">subject</th>
                                            <th scope="col">class</th>
                                            <th scope="col">Section</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>Teacher</td>
                                            <td> Number1</td>
                                            <td>teacher@gmail.com</td>
                                            <td>Male</td>
                                            <td>255678998877</td>
                                            <td>Mathematics</td>
                                            <td>Form 1</td>
                                            <td>Section A</td>
                                        </tr>
                                        <tr>
                                            <td>Teacher</td>
                                            <td>Number2</td>
                                            <td>teacgernumber2@gmail.com</td>
                                            <td>Female</td>
                                            <td>255987665533</td>
                                            <td>English</td>
                                            <td>Form 2</td>
                                            <td>Section B</td>
                                        </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
@endsection
