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
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"> {{ ___('common.home') }} </a></li>
                        <li class="breadcrumb-item"><a href="{{ route('sections.index') }}">{{ $data['title'] }}</a></li>
                        <li class="breadcrumb-item">{{ ___('common.edit') }}</li>

                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <div class="card ot-card">
            <div class="card-title">
                <h4>{{___('website.Key')}}: {{ @$data['sections']->key }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('sections.update', @$data['sections']->id) }}" enctype="multipart/form-data" method="post"
                    id="visitForm">
                    @csrf
                    @method('PUT')
                    <div class="row mb-3">
                        <div class="col-lg-12">

                            {{-- @dd($data['sections']) --}}

                            <div class="row">

                                @if (@$data['sections']->name)
                                    <div class="col-md-6">
                                        <label for="exampleDataList" class="form-label ">{{ ___('common.name') }}</label>
                                        <input class="form-control ot-input @error('name') is-invalid @enderror" name="name"
                                            value="{{ old('name',@$data['sections']->name) }}" list="datalistOptions" id="exampleDataList"
                                            placeholder="{{ ___('common.enter_name') }}">
                                        @error('name')
                                            <div id="validationServer04Feedback" class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                @endif

                                @if (@$data['sections']->upload_id)
                                    <div class="col-md-6">
                                        <label for="exampleDataList" class="form-label ">{{ ___('common.image') }} 
                                        
                                        @if(@$data['sections']->key == 'statement')
                                            {{ ___('common.(580 x 465 px)') }}
                                        @elseif(@$data['sections']->key == 'study_at')
                                            {{ ___('common.(1920 x 615 px)') }}
                                        @elseif(@$data['sections']->key == 'explore')
                                            {{ ___('common.(700 x 500 px)') }}
                                        @endif
                                        
                                        </label>
                                        <div class="ot_fileUploader left-side mb-3">
                                            <input class="form-control" type="text"
                                                placeholder="{{ ___('common.image') }}" readonly="" id="placeholder">
                                            <button class="primary-btn-small-input" type="button">
                                                <label class="btn btn-lg ot-btn-primary"
                                                    for="fileBrouse">{{ ___('common.browse') }}</label>
                                                <input type="file" class="d-none form-control" name="image" accept="image/*"
                                                    id="fileBrouse">
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                @if (@$data['sections']->description)
                                    <div class="col-md-12 mt-3">
                                        <label for="exampleDataList" class="form-label">{{ ___('common.Description') }}</label>
                                        <textarea class="form-control ot-textarea @error('description') is-invalid @enderror" name="description"
                                        list="datalistOptions" id="exampleDataList"
                                        placeholder="{{ ___('common.Enter description') }}">{{ old('description',@$data['sections']->description) }}</textarea>
                                        @error('description')
                                            <div id="validationServer04Feedback" class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                @endif


                                @if (@$data['sections']->key == 'social_links') {{-- -------------------------------- Social link --------------------------------- --}}
                                    <div class="col-md-12 mt-5">
                                        <div class="text-end">
                                            <button type="button" onclick="addSocialLink()" class="btn ot-btn-primary"><span><i class="fa-solid fa-add"></i> </span>{{ ___('common.add') }}</button>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <table class="table" id="social_links">
                                            <thead></thead>
                                            <tbody>
                                                @foreach (@$data['sections']->data as $key=>$item)
                                                    <tr>
                                                        <td>
                                                            <label class="form-label">{{ ___('common.name') }}</label>
                                                            <input class="form-control ot-input mb-4" value="{{ $item['name'] }}" name="data[name][]"placeholder="{{ ___('common.Enter name') }}">
                                                        </td>
                                                        <td>
                                                            <label class="form-label">{{ ___('common.Icon') }}</label>
                                                            <input class="form-control ot-input mb-4" value="{{ $item['icon'] }}" name="data[icon][]"placeholder="{{ ___('common.Enter icon') }}">
                                                        </td>
                                                        <td>
                                                            <label class="form-label">{{ ___('common.Link') }}</label>
                                                            <div class="d-flex align-items-center mb-4">
                                                                <input class="form-control ot-input mr-2" value="{{ $item['link'] }}" name="data[link][]"placeholder="{{ ___('common.Enter link') }}">
                                                                <button class="drax_close_icon mark_distribution_close" onclick="removeRow(this)">
                                                                    <i class="fa-solid fa-xmark"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                @if (@$data['sections']->key == 'statement') {{-- -------------------------------- Statement --------------------------------- --}}
                                    <h3 class="mt-3">{{___('common.Details')}}</h3>
                                    <div class="col-md-12">
                                        <div class="row">    
                                            <div class="col-md-6 mb-18">
                                                <div class="mb-18">
                                                    <label class="form-label">{{ ___('common.title') }}</label>
                                                    <input class="form-control ot-input mb-2" value="{{ @$data['sections']->data[0]['title'] }}" name="data[title][]" placeholder="{{ ___('common.Enter title') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">{{ ___('common.Description') }}</label>
                                                    <textarea class="form-control ot-textarea mt-0 mb-5" name="data[description][]" placeholder="{{ ___('common.Enter description') }}">{{ @$data['sections']->data[0]['description'] }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-18">
                                                <div class="mb-18">
                                                    <label class="form-label">{{ ___('common.title') }}</label>
                                                    <input class="form-control ot-input mb-2" value="{{ @$data['sections']->data[1]['title'] }}" name="data[title][]" placeholder="{{ ___('common.Enter title') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">{{ ___('common.Description') }}</label>
                                                    <textarea class="form-control ot-textarea mt-0" name="data[description][]" placeholder="{{ ___('common.Enter description') }}">{{ @$data['sections']->data[1]['description'] }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if (@$data['sections']->key == 'study_at') {{-- -------------------------------- study_at --------------------------------- --}}
                                    <h3 class="mt-3">{{___('common.Details')}}</h3>
                                    <div class="col-md-12">
                                        <div class="row">    
                                            <div class="col-6">
                                                <div>
                                                    <label class="form-label">{{ ___('common.Icon') }} {{ ___('common.(70 x 70 px)') }}</label>
                                                    <div class="ot_fileUploader left-side mb-3">
                                                        <input class="form-control" type="text" placeholder="{{ ___('common.Select icon') }}" readonly="" id="placeholder2">
                                                        <button class="primary-btn-small-input" type="button">
                                                            <label class="btn btn-lg ot-btn-primary" for="fileBrouse2">{{ ___('common.browse') }}</label>
                                                            <input type="file" class="d-none form-control" name="data[icon][0]" accept="image/*" id="fileBrouse2">
                                                        </button>
                                                    </div>
                                                    
                                                    
                                                    {{-- <input class="form-control ot-input mb-2" value="{{ @$data['sections']->data[0]['icon'] }}" name="data[icon][]" placeholder="{{ ___('common.Enter icon') }}"> --}}
                                                </div>
                                                <div>
                                                    <label class="form-label">{{ ___('common.title') }}</label>
                                                    <input class="form-control ot-input mb-2" value="{{ @$data['sections']->data[0]['title'] }}" name="data[title][]" placeholder="{{ ___('common.Enter title') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">{{ ___('common.Description') }}</label>
                                                    <textarea class="form-control ot-textarea mt-0 mb-5" name="data[description][]" placeholder="{{ ___('common.Enter description') }}">{{ @$data['sections']->data[0]['description'] }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div>
                                                    <label class="form-label">{{ ___('common.Icon') }} {{ ___('common.(70 x 70 px)') }}</label>
                                                    <div class="ot_fileUploader left-side mb-3">
                                                        <input class="form-control" type="text" placeholder="{{ ___('common.Select icon') }}" readonly="" id="placeholder3">
                                                        <button class="primary-btn-small-input" type="button">
                                                            <label class="btn btn-lg ot-btn-primary" for="fileBrouse3">{{ ___('common.browse') }}</label>
                                                            <input type="file" class="d-none form-control" name="data[icon][1]" accept="image/*" id="fileBrouse3">
                                                        </button>
                                                    </div>
                                                    
                                                    
                                                    {{-- <input class="form-control ot-input mb-2" value="{{ @$data['sections']->data[1]['icon'] }}" name="data[icon][]" placeholder="{{ ___('common.Enter icon') }}"> --}}
                                                </div>
                                                <div>
                                                    <label class="form-label">{{ ___('common.title') }}</label>
                                                    <input class="form-control ot-input mb-2" value="{{ @$data['sections']->data[1]['title'] }}" name="data[title][]" placeholder="{{ ___('common.Enter title') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">{{ ___('common.Description') }}</label>
                                                    <textarea class="form-control ot-textarea mt-0" name="data[description][]" placeholder="{{ ___('common.Enter description') }}">{{ @$data['sections']->data[1]['description'] }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div>
                                                    <label class="form-label">{{ ___('common.Icon') }} {{ ___('common.(70 x 70 px)') }}</label>
                                                    <div class="ot_fileUploader left-side mb-3">
                                                        <input class="form-control" type="text" placeholder="{{ ___('common.Select icon') }}" readonly="" id="placeholder4">
                                                        <button class="primary-btn-small-input" type="button">
                                                            <label class="btn btn-lg ot-btn-primary" for="fileBrouse4">{{ ___('common.browse') }}</label>
                                                            <input type="file" class="d-none form-control" name="data[icon][2]" accept="image/*" id="fileBrouse4">
                                                        </button>
                                                    </div>
                                                    
                                                    
                                                    {{-- <input class="form-control ot-input mb-2" value="{{ @$data['sections']->data[2]['icon'] }}" name="data[icon][]" placeholder="{{ ___('common.Enter icon') }}"> --}}
                                                </div>
                                                <div>
                                                    <label class="form-label">{{ ___('common.title') }}</label>
                                                    <input class="form-control ot-input mb-2" value="{{ @$data['sections']->data[2]['title'] }}" name="data[title][]" placeholder="{{ ___('common.Enter title') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">{{ ___('common.Description') }}</label>
                                                    <textarea class="form-control ot-textarea mt-0" name="data[description][]" placeholder="{{ ___('common.Enter description') }}">{{ @$data['sections']->data[2]['description'] }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if (@$data['sections']->key == 'explore') {{-- -------------------------------- explore --------------------------------- --}}
                                    <h3 class="mt-3">{{___('common.Details')}}</h3>
                                    <div class="col-md-12">
                                        <div class="row">    
                                            <div class="col-6">
                                                <div>
                                                    <label class="form-label">{{ ___('common.Tab') }}</label>
                                                    <input class="form-control ot-input mb-2" value="{{ @$data['sections']->data[0]['tab'] }}" name="data[tab][]" placeholder="{{ ___('common.Enter tab') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">{{ ___('common.title') }}</label>
                                                    <input class="form-control ot-input mb-2" value="{{ @$data['sections']->data[0]['title'] }}" name="data[title][]" placeholder="{{ ___('common.Enter title') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">{{ ___('common.Description') }}</label>
                                                    <textarea class="form-control ot-textarea mt-0 mb-5" name="data[description][]" placeholder="{{ ___('common.Enter description') }}">{{ @$data['sections']->data[0]['description'] }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div>
                                                    <label class="form-label">{{ ___('common.Tab') }}</label>
                                                    <input class="form-control ot-input mb-2" value="{{ @$data['sections']->data[1]['tab'] }}" name="data[tab][]" placeholder="{{ ___('common.Enter tab') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">{{ ___('common.title') }}</label>
                                                    <input class="form-control ot-input mb-2" value="{{ @$data['sections']->data[1]['title'] }}" name="data[title][]" placeholder="{{ ___('common.Enter title') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">{{ ___('common.Description') }}</label>
                                                    <textarea class="form-control ot-textarea mt-0" name="data[description][]" placeholder="{{ ___('common.Enter description') }}">{{ @$data['sections']->data[1]['description'] }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div>
                                                    <label class="form-label">{{ ___('common.Tab') }}</label>
                                                    <input class="form-control ot-input mb-2" value="{{ @$data['sections']->data[2]['tab'] }}" name="data[tab][]" placeholder="{{ ___('common.Enter tab') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">{{ ___('common.title') }}</label>
                                                    <input class="form-control ot-input mb-2" value="{{ @$data['sections']->data[2]['title'] }}" name="data[title][]" placeholder="{{ ___('common.Enter title') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">{{ ___('common.Description') }}</label>
                                                    <textarea class="form-control ot-textarea mt-0" name="data[description][]" placeholder="{{ ___('common.Enter description') }}">{{ @$data['sections']->data[2]['description'] }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div>
                                                    <label class="form-label">{{ ___('common.Tab') }}</label>
                                                    <input class="form-control ot-input mb-2" value="{{ @$data['sections']->data[2]['tab'] }}" name="data[tab][]" placeholder="{{ ___('common.Enter tab') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">{{ ___('common.title') }}</label>
                                                    <input class="form-control ot-input mb-2" value="{{ @$data['sections']->data[2]['title'] }}" name="data[title][]" placeholder="{{ ___('common.Enter title') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">{{ ___('common.Description') }}</label>
                                                    <textarea class="form-control ot-textarea mt-0" name="data[description][]" placeholder="{{ ___('common.Enter description') }}">{{ @$data['sections']->data[2]['description'] }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if (@$data['sections']->key == 'why_choose_us') {{-- -------------------------------- why_choose_us --------------------------------- --}}
                                    <h3 class="mt-3">{{___('common.Details')}}</h3>
                                    <div class="col-md-12">
                                        <div class="text-end">
                                            <button type="button" onclick="addChooseUs()" class="btn ot-btn-primary"><span><i class="fa-solid fa-add"></i> </span>{{ ___('common.Add new') }}</button>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <table class="table" id="why_choose_us">
                                            <thead></thead>
                                            <tbody>
                                                @foreach (@$data['sections']->data as $key=>$item)
                                                    <tr>
                                                        <td>
                                                            <label class="form-label">{{ ___('common.name') }}</label>
                                                            <div class="d-flex align-items-center mb-2">
                                                                <input class="form-control ot-input mr-2" value="{{ $item }}" name="data[]"placeholder="{{ ___('common.Enter name') }}">
                                                                <button class="drax_close_icon mark_distribution_close" onclick="removeRow(this)">
                                                                    <i class="fa-solid fa-xmark"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                @if (@$data['sections']->key == 'academic_curriculum') {{-- -------------------------------- academic_curriculum --------------------------------- --}}
                                    <h3 class="mt-3">{{___('common.Details')}}</h3>
                                    <div class="col-md-12">
                                        <div class="text-end">
                                            <button type="button" onclick="addAcademicCurriculum()" class="btn ot-btn-primary"><span><i class="fa-solid fa-add"></i> </span>{{ ___('common.Add new') }}</button>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <table class="table" id="academic_curriculum">
                                            <thead></thead>
                                            <tbody>
                                                @foreach (@$data['sections']->data as $key=>$item)
                                                    <tr>
                                                        <td>
                                                            <label class="form-label">{{ ___('common.name') }}</label>
                                                            <div class="d-flex align-items-center mb-2">
                                                                <input class="form-control ot-input mr-2" value="{{ $item }}" name="data[]"placeholder="{{ ___('common.Enter name') }}">
                                                                <button class="drax_close_icon mark_distribution_close" onclick="removeRow(this)">
                                                                    <i class="fa-solid fa-xmark"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                <div class="col-md-12 mt-24">
                                    <div class="text-end">
                                        <button class="btn btn-lg ot-btn-primary"><span><i class="fa-solid fa-save"></i>
                                            </span>{{ ___('common.update') }}</button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
@endsection
