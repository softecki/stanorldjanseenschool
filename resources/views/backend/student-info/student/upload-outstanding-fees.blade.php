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
                            <li class="breadcrumb-item active" aria-current="page">{{ 'Upload Outstanding Fees' }}</li>
                        </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}
        <div class="card ot-card">
            <div class="card-body">
                <form action="{{ route('student.uploadOutstandingFees') }}" enctype="multipart/form-data" method="post" id="outstandingFeesForm">
                    @csrf
                <div class="row ">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table school_borderLess_table table_border_hide2" id="outstanding-fees-document">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ 'Excel File' }} <span class="text-danger">*</span></th>
                                                <th scope="col">{{ 'Action' }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <input type="file" name="document_files" class="form-control" accept=".xlsx,.xls,.csv" required>
                                                    <small class="text-muted">Upload Excel file with format: Date, Num, Name (CLASS X Y: STUDENT NAME), Amount, Open Balance</small>
                                                </td>
                                                <td>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fa fa-upload"></i> Upload Outstanding Fees
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <strong>Excel Format Required:</strong><br>
                                    <ul>
                                        <li><strong>Date:</strong> Transaction date</li>
                                        <li><strong>Num:</strong> Transaction number</li>
                                        <li><strong>Name:</strong> Format must be "CLASS [NUMBER] [SECTION]: [STUDENT NAME]" (e.g., "CLASS 1 B: JOHN DOE")</li>
                                        <li><strong>Amount:</strong> Total fees amount (e.g., 300,000.00)</li>
                                        <li><strong>Open Balance:</strong> Remaining balance (e.g., 70,000.00)</li>
                                    </ul>
                                    <strong>Note:</strong> The system will:
                                    <ul>
                                        <li>Search for students by name and class</li>
                                        <li>Assign outstanding fees if not already assigned</li>
                                        <li>Record Amount as fees_amount</li>
                                        <li>Record Open Balance as remained_amount</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        $('#outstandingFeesForm').on('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    // Show loading
                },
                success: function(response) {
                    if (response.status) {
                        alert(response.message);
                        if (response.data && response.data.errors && response.data.errors.length > 0) {
                            console.log('Errors:', response.data.errors);
                        }
                        // Optionally reload or redirect
                    } else {
                        alert(response.message || 'Error uploading file');
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'Error uploading file';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                }
            });
        });
    });
</script>
@endpush

