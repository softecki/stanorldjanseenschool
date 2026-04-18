@extends('backend.master')
@section('title')
    {{ @$data['title'] }}
@endsection
@section('content')
    <div class="page-content">
        {{-- breadcrumb Area Start --}}
        <div class="page-header">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="bradecrumb-title mb-1">{{ $data['title'] }}</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('smsmail.index') }}">SMS/Mail</a></li>
                        <li class="breadcrumb-item">{{ $data['title'] }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- breadcrumb Area End --}}
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Fee Reminder SMS Campaign</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5>Failed SMS</h5>
                                        <h2>{{ $data['failed_sms_count'] ?? 0 }}</h2>
                                        <p class="mb-0">SMS that need to be resent</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5>Last Campaign</h5>
                                        <h6>{{ $data['last_campaign']->created_at ?? 'Never' }}</h6>
                                        @if($data['last_campaign'])
                                            <p class="mb-0">Sent: {{ $data['last_campaign']->sent_count }}, Failed: {{ $data['last_campaign']->failed_count }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5>Current Quarter</h5>
                                        <h2>Q{{ date('n') <= 4 ? 1 : (date('n') <= 6 ? 2 : (date('n') <= 9 ? 3 : 4)) }}</h2>
                                        <p class="mb-0">Quarter {{ date('n') <= 4 ? '1 (Jan-Apr)' : (date('n') <= 6 ? '2 (May-Jun)' : (date('n') <= 9 ? '3 (Jul-Sep)' : '4 (Oct-Dec)')) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <strong>Note:</strong> This campaign sends reminder SMS every Friday to parents whose children have unpaid quarter fees. 
                            The system groups by student_id to send only one SMS per student even if they have multiple fee types.
                        </div>

                        @php
                            $isFriday = date('w') == 5; // 5 = Friday (0 = Sunday, 1 = Monday, ..., 5 = Friday)
                            $nextFriday = date('Y-m-d', strtotime('next friday'));
                        @endphp

                        <div class="row">
                            <div class="col-md-6">
                                @if($isFriday)
                                    <button type="button" class="btn btn-primary btn-lg" id="sendCampaignBtn">
                                        <i class="fa fa-paper-plane"></i> Send Campaign Now
                                    </button>
                                @else
                                    <button type="button" class="btn btn-secondary btn-lg" id="sendCampaignBtn" disabled>
                                        <i class="fa fa-paper-plane"></i> Send Campaign Now
                                    </button>
                                    <div class="alert alert-warning mt-2">
                                        <i class="fa fa-info-circle"></i> Campaign can only be sent on Fridays. Next available date: {{ date('l, F j, Y', strtotime('next friday')) }}
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-warning btn-lg" id="retryFailedBtn">
                                    <i class="fa fa-redo"></i> Retry Failed SMS
                                </button>
                            </div>
                        </div>

                        <div id="campaignResult" class="mt-4" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#sendCampaignBtn').on('click', function() {
                // Double check if it's Friday (client-side validation)
                var today = new Date();
                var dayOfWeek = today.getDay(); // 0 = Sunday, 5 = Friday
                
                if (dayOfWeek !== 5) {
                    alert('Campaign can only be sent on Fridays. Please try again on Friday.');
                    return;
                }
                
                if (!confirm('Are you sure you want to send the fee reminder campaign? This will send SMS to all parents with unpaid quarter fees.')) {
                    return;
                }

                $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');

                $.ajax({
                    url: '{{ route("smsmail.campaign.send") }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status) {
                            $('#campaignResult').html(
                                '<div class="alert alert-success">' +
                                '<strong>Success!</strong> ' + response.message + '<br>' +
                                'Total Students: ' + response.data.total_students + '<br>' +
                                'Sent: ' + response.data.sent_count + '<br>' +
                                'Failed: ' + response.data.failed_count +
                                '</div>'
                            ).show();
                        } else {
                            $('#campaignResult').html(
                                '<div class="alert alert-danger">' +
                                '<strong>Error!</strong> ' + response.message +
                                '</div>'
                            ).show();
                        }
                        $('#sendCampaignBtn').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send Campaign Now');
                    },
                    error: function(xhr) {
                        $('#campaignResult').html(
                            '<div class="alert alert-danger">' +
                            '<strong>Error!</strong> ' + (xhr.responseJSON?.message || 'An error occurred') +
                            '</div>'
                        ).show();
                        $('#sendCampaignBtn').prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send Campaign Now');
                    }
                });
            });

            $('#retryFailedBtn').on('click', function() {
                if (!confirm('Are you sure you want to retry failed SMS? This will attempt to resend up to 100 failed SMS.')) {
                    return;
                }

                $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Retrying...');

                $.ajax({
                    url: '{{ route("smsmail.campaign.retry") }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status) {
                            $('#campaignResult').html(
                                '<div class="alert alert-success">' +
                                '<strong>Success!</strong> ' + response.message + '<br>' +
                                'Retried: ' + response.data.retried_count + '<br>' +
                                'Success: ' + response.data.success_count +
                                '</div>'
                            ).show();
                        } else {
                            $('#campaignResult').html(
                                '<div class="alert alert-danger">' +
                                '<strong>Error!</strong> ' + response.message +
                                '</div>'
                            ).show();
                        }
                        $('#retryFailedBtn').prop('disabled', false).html('<i class="fa fa-redo"></i> Retry Failed SMS');
                    },
                    error: function(xhr) {
                        $('#campaignResult').html(
                            '<div class="alert alert-danger">' +
                            '<strong>Error!</strong> ' + (xhr.responseJSON?.message || 'An error occurred') +
                            '</div>'
                        ).show();
                        $('#retryFailedBtn').prop('disabled', false).html('<i class="fa fa-redo"></i> Retry Failed SMS');
                    }
                });
            });
        });
    </script>
@endsection

