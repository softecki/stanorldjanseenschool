@extends('backend.master')

@section('title')
    {{ $data['title'] }}
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
                                <i class="las la-qrcode me-2"></i>{{ $data['title'] }}
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
                                @if(isset($data['student_id']))
                                <li class="breadcrumb-item">
                                    <a href="{{ route('student.show', $data['student_id']) }}">
                                        {{ $data['student_name'] ?? 'Student' }}
                                    </a>
                                </li>
                                @endif
                                <li class="breadcrumb-item active">{{ $data['title'] }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ $data['title'] }}</h4>
                    </div>
                    <div class="card-body">
                        @if($data['control_number'])
                            <div class="text-center">
                                <h5 class="mb-3">Student: {{ $data['student_name'] }}</h5>
                                <p class="mb-3">Control Number: <strong>{{ $data['control_number'] }}</strong></p>
                                
                                <div class="qr-code-container mb-4" style="display: inline-block; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                    {!! $data['qr_code_html'] !!}
                                </div>
                                
                                <div class="mt-4">
                                    <p class="text-muted">Scan this QR code to check payment status</p>
                                </div>
                                
                                <div class="mt-3">
                                    <button onclick="window.print()" class="btn btn-primary">
                                        <i class="las la-print"></i> Print QR Code
                                    </button>
                                    <button onclick="downloadQR()" class="btn btn-success">
                                        <i class="las la-download"></i> Download QR Code
                                    </button>
                                    @if(isset($data['student_id']))
                                    <a href="{{ route('student.show', $data['student_id']) }}" class="btn btn-secondary">
                                        <i class="las la-arrow-left"></i> Back to Student Details
                                    </a>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning text-center">
                                <i class="las la-exclamation-triangle"></i> 
                                Control number not found for this student. Please contact administrator.
                            </div>
                            @if(isset($data['student_id']))
                            <div class="text-center mt-3">
                                <a href="{{ route('student.show', $data['student_id']) }}" class="btn btn-secondary">
                                    <i class="las la-arrow-left"></i> Back to Student Details
                                </a>
                            </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function downloadQR() {
            const qrCode = document.querySelector('.qr-code-container svg');
            if (qrCode) {
                const svgData = new XMLSerializer().serializeToString(qrCode);
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const img = new Image();
                
                img.onload = function() {
                    canvas.width = img.width;
                    canvas.height = img.height;
                    ctx.drawImage(img, 0, 0);
                    const pngFile = canvas.toDataURL('image/png');
                    const downloadLink = document.createElement('a');
                    downloadLink.download = 'qr-code-{{ $data["control_number"] ?? "student" }}.png';
                    downloadLink.href = pngFile;
                    downloadLink.click();
                };
                
                img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
            }
        }
    </script>

    <style>
        @media print {
            .page-header,
            .card-header,
            .btn,
            .breadcrumb {
                display: none !important;
            }
            .qr-code-container {
                box-shadow: none !important;
            }
        }
    </style>
@endsection

