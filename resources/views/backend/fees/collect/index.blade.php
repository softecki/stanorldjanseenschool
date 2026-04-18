@extends('backend.master')
@section('title')
    {{ @$data['title'] }}
@endsection
@section('css')
<style>
    .main-content-inner { flex: 1 1 auto; width: 100%; max-width: 100%; min-height: 0; align-self: stretch; display: block; }
    .fees-collect-page { width: 100%; max-width: 100%; text-align: left; }
</style>
@endsection
@section('content')
    <div class="page-content fees-collect-page">

        {{-- bradecrumb Area S t a r t --}}
        <div class="">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="bradecrumb-title">{{ $data['title'] }}</h4>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ ___('common.home') }}</a></li>
                        <li class="breadcrumb-item">{{ $data['title'] }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <div class="row">
            <div class="col-12">
                <div class="card ot-card position-relative z_1">
                    <form action="{{ route('fees-collect-search') }}" enctype="multipart/form-data" method="post" id="fees-collect">
                        @csrf
                    <div class="card-header d-flex align-items-center gap-4 flex-wrap">
                        {{-- <h5 class="mb-0">{{ ___('common.Filtering') }}</h5> --}}
                        
                        <div class="card_header_right d-flex align-items-center gap-3 flex-fill justify-content-end flex-wrap">
                            <!-- table_searchBox -->
                           
                            <div class="single_selectBox">
                                <select id="getSections" class="class nice-select niceSelect bordered_style wide" name="class">
                                    <option value="">{{ ___('student_info.select_class') }}</option>
                                @foreach ($data['classes'] as $item)
                                    <option {{ old('class') == $item->id ? 'selected' : '' }} value="{{ $item->class->id }}">{{ $item->class->name }}</option>
                                @endforeach
                                </select>
                            </div>
                            <div class="single_selectBox">
                                <select class="sections section nice-select niceSelect bordered_style wide" name="section">
                                    <option value="">{{ ___('student_info.select_section') }}</option>
                                </select>
                            </div>
                            <div class="single_selectBox">
                                <select class="students nice-select niceSelect bordered_style wide @error('student') is-invalid @enderror"
                                    name="student">
                                    <option value="">{{ ___('student_info.select_student') }}</option>
                                </select>
                                @error('student')
                                    <div id="validationServer04Feedback" class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            <div class="input-group table_searchBox">
                                    <input name="name" type="text" class="form-control" id="searchName" placeholder="{{ ___('common.name') }}" aria-label="Search">
                                    
                                    <!-- Magnifying glass icon (optional click) -->
                                    <span class="input-group-text" id="searchIcon">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </span>
                                    
                                    <!-- Microphone icon for voice input -->
                                    <span class="input-group-text" id="voiceSearchBtn">
                                        <i class="fa-solid fa-microphone" id="voiceSearchIcon" style="cursor: pointer;"></i>
                                    </span>
                                </div>

                            <button class="btn btn-md btn-outline-primary">
                                {{ ___('common.Search')}}
                            </button>
                        </div>
                       

                    </div>
                </form>
                </div>
            </div>
        </div>

        @isset($data['students'])

        <div class="row mt-1">
            <div class="col-12">
                {{-- Main content: table + receipt panel + collect panel --}}
                <div id="fees-collect-main" class="table-content table-basic">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h6 class="mb-0">{{ $data['title'] }}</h6>
                            <div class="d-flex gap-2">
                                <a href="{{ route('student.updatefees') }}" class="btn btn-sm btn-outline-primary">
                                    <span><i class="fa-solid fa-save"></i> </span>
                                    <span>{{ 'Update Fees' }}</span>
                                </a>
                                <a href="{{ route('fees-collect.cancelled-list') }}" class="btn btn-sm btn-outline-secondary">
                                    <span><i class="fa-solid fa-ban"></i> </span>
                                    <span>Cancelled Collect</span>
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered role-table" id="students_table">
                                    <thead class="thead">
                                        <tr>
                                            <th class="purchase">{{___('student_info.student_name') }}</th>
                                            <th class="purchase">{{ 'Fees Type' }}</th>
                                            <th class="purchase">{{ ___('academic.class') }} </th>
                                            <th class="purchase">{{ 'Fee Amount' }}</th>
                                            <th class="purchase">{{ 'Paid ' }}</th>
                                            <th class="purchase">{{ 'Remained' }}</th>
                                            <th class="purchase">{{ 'Status' }}</th>
                                            @if (hasPermission('fees_collect_create'))
                                                <th class="purchase">{{ ___('common.action') }}</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($data['students'] as $item)
                                <tr class="text-sm clickable-row" id="row_{{ $item->assignId }}" data-student-id="{{ $item->student->id }}" data-assign-id="{{ $item->assignId }}" role="button" tabindex="0">
                                    <td class="text-sm">{{ @$item->student->first_name }} {{ @$item->student->last_name }}</td>
                                    <td> <p class="text-sm text-gray-500">{{ @$item->fees_name }}</p></td>
                                    <td class="text-sm">{{ $item->class_name ?? $item->class->name ?? '—' }}</td>
                                    <!-- <td>{{ @$item->student->parent->guardian_name }}</td> -->
                                    <!-- <td>{{ @$item->student->mobile }}</td> -->
                                    <td> @if (is_numeric($item->fees_amount))
                                            {{ number_format($item->fees_amount, 2, '.', ',') }}
                                        @else
                                           {{ @$item->fees_amount }}
                                        @endif </td>

                                    <td> @if (is_numeric($item->paid_amount))
                                            {{ number_format($item->paid_amount, 2, '.', ',') }}
                                        @else
                                            {{ @$item->paid_amount }}
                                        @endif </td>

                                    <td> @if (is_numeric($item->remained_amount))
                                            {{ number_format($item->remained_amount, 2, '.', ',') }}
                                        @else
                                            {{ @$item->remained_amount }}
                                        @endif </td>

                                    <td> @if(@$item->remained_amount > 0 )
                                            <span class="text-danger">Unpaid</span>
                                        @else
                                            <span class="text-success">Paid</span>
                                        @endif
                                    </td>

                                    @if (hasPermission('fees_collect_create'))
                                        <td class="action no-row-click">
                                            <a title="Collect Fees" href="javascript:void(0);" class="btn btn-sm btn-outline-primary btn-collect-fees" data-student-id="{{ $item->student->id }}" data-url="{{ route('fees-collect.collect', $item->student->id) }}"><span class="icon mr-1"><i class="fa-solid fa-hand-holding-dollar"></i></span></a>
                                            <a title="Cancel (move to Cancelled Collect)" class="btn btn-sm btn-outline-danger" href="javascript:void(0);" onclick="event.stopPropagation(); delete_row('fees-collect/deleteFees', {{ $item->assignId }}, true);">
                                                <span class="icon mr-8"><i class="fa-solid fa-trash-can"></i></span>
                                            </a>
                                        </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="100%" class="text-center gray-color">
                                        <img src="{{ asset('images/no_data.svg') }}" alt="" class="mb-primary" width="100">
                                        <p class="mb-0 text-center">{{ ___('common.no_data_available') }}</p>
                                        <p class="mb-0 text-center text-secondary font-size-90">
                                            {{ ___('common.please_add_new_entity_regarding_this_table') }}</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <!--  pagination start -->

                    <div class="ot-pagination pagination-content d-flex justify-content-end align-content-center py-3">
                        <nav aria-label="Page navigation example">
                            <ul class="pagination justify-content-between">
                                {!!$data['students']->appends(\Request::capture()->except('page'))->links() !!}
                            </ul>
                        </nav>
                    </div>

                <!--  pagination end -->
                        </div>
                    </div>
                </div>

                {{-- Receipt preview panel (hidden by default) --}}
                <div id="receipt-panel" class="card mt-3" style="display: none;">
                    <div class="card-header d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-back-from-receipt" aria-label="Back">
                            <i class="fa-solid fa-arrow-left"></i>
                        </button>
                        <h6 class="mb-0">Preview Receipt</h6>
                        <a id="receipt-panel-edit-btn" href="#" class="btn btn-sm btn-outline-success ms-auto" style="display: none;">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit Fees
                        </a>
                    </div>
                    <div class="card-body position-relative">
                        <div id="receipt-loader" class="text-center py-5" style="display: none;">
                            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                            <p class="mt-2 mb-0 small text-muted">Loading receipt...</p>
                        </div>
                        <div id="receipt-content" class="receipt-content-inner"></div>
                    </div>
                </div>

                {{-- Collect fees panel (hidden by default) --}}
                <div id="collect-panel" class="card mt-3" style="display: none;">
                    <div class="card-header d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-back-from-collect" aria-label="Back">
                            <i class="fa-solid fa-arrow-left"></i>
                        </button>
                        <h6 class="mb-0">Collect Fees</h6>
                    </div>
                    <div class="card-body position-relative">
                        <div id="collect-loader" class="text-center py-5" style="display: none;">
                            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                            <p class="mt-2 mb-0 small text-muted">Loading form...</p>
                        </div>
                        <div id="collect-content" class="collect-content-inner overflow-auto" style="max-height: 75vh;"></div>
                    </div>
                </div>
            </div>
        </div>
        <!--  table content end -->

        @endif

    </div>

    <script>
        function selectOnlyOne(checkbox) {
            const checkboxes = document.querySelectorAll('.form-check-input.child');
            checkboxes.forEach((cb) => {
                if (cb !== checkbox) {
                    cb.checked = false;
                }
            });
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {
    const synth = window.speechSynthesis;
    let isListening = false;
    let recognition = null; 
    
    let micRecognition = null;
    let speakRecognition = null;
    let isMicListening = false;
    let isSpeakListening = false;
// Initialize recognition object

    // Function to speak a message
    function speak(message, callback) {
        const utterance = new SpeechSynthesisUtterance(message);
        utterance.onend = callback; // When speaking ends, run callback
        synth.speak(utterance);
    }

    // Function to beep (440Hz tone, 0.2s)
    function beep() {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = ctx.createOscillator();
        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(440, ctx.currentTime);
        oscillator.connect(ctx.destination);
        oscillator.start();
        oscillator.stop(ctx.currentTime + 0.2);
    }

    // Perform AJAX search
    function performSearch(name, voice) {
        if (name.length >= 3 || name.length === 0) {
            $.ajax({
                url: "{{ route('fees-collect-searchs') }}",
                method: "GET",
                data: { name: name },
                success: function (response) {
                    $('#students_table tbody').html(response.html);

                    if (voice === 1) {
                        beep();

                        const students = response.students?.data || [];
                        const names = students.map(s => s.first_name + " " + s.last_name).slice(0, 3);

                        if (names.length > 0) {
                            let message = "";
                            if (names[0]) message += `The first student is ${names[0]}. `;
                            if (names[1]) message += `Secondly, ${names[1]}. `;
                            if (names[2]) message += `Third, ${names[2]}. `;
                            message += "Please say first, second, or third to view their details or click collection button for collection.";

                            // Speak the message and then start voice recognition after speaking ends
            //                 speak(message);
                            
                            speakThenListen(message, students);
                        } else {
                            speak("No student names found.");
                        }
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching data: " + error);
                }
            });
        }
    }

                // Start voice recognition
                function speakThenListen(message, students) {
                const utterance = new SpeechSynthesisUtterance(message);
                synth.speak(utterance);

                utterance.onend = () => {
                    console.log("Speaking finished, now listening for choice...");

                    if (!('webkitSpeechRecognition' in window)) {
                        alert("Voice recognition not supported.");
                        return;
                    }

                    if (!speakRecognition) {
                        speakRecognition = new webkitSpeechRecognition();
                        speakRecognition.continuous = false;
                        speakRecognition.interimResults = false;
                        speakRecognition.lang = 'en-US';

                        speakRecognition.onresult = (event) => {
                            const reply = event.results[0][0].transcript.toLowerCase();
                            console.log("User selected:", reply);

                            let selectedId = null;
                            console.log(students);
                            if (reply.includes("first")) {
                                selectedId = students[0]?.student_id;
                            } else if (reply.includes("second")) {
                                selectedId = students[1]?.student_id;
                            } else if (reply.includes("third")) {
                                selectedId = students[2]?.student_id;
                            }

                            if (selectedId && typeof window.openReceipt === 'function') {
                                window.openReceipt(selectedId, null);
                            } else {
                                speakThenListen("I did not understand. Please say first, second, or third.", students);
                            }

                            speakRecognition.stop();
                            isSpeakListening = false;
                        };

                        speakRecognition.onerror = () => {
                            isSpeakListening = false;
                        };

                        speakRecognition.onend = () => {
                            isSpeakListening = false;
                        };
                    }

                    if (!isSpeakListening) {
                        speakRecognition.start();
                        isSpeakListening = true;
                    }
                };
            }
//             function speakThenListen(message, students) {
//             const utterance = new SpeechSynthesisUtterance(message);
//             synth.speak(utterance);

//             utterance.onend = () => {
//                 console.log("Speech ended, starting recognition...");

//                 if (!('webkitSpeechRecognition' in window)) {
//                     alert('Voice recognition is not supported in this browser.');
//                     return;
//                 }

//                 if (!recognition) {
//                     recognition = new webkitSpeechRecognition();
//                     recognition.continuous = false;
//                     recognition.interimResults = false;
//                     recognition.lang = 'en-US';

//                     recognition.onerror = (event) => {
//                         console.error('Voice recognition error: ' + event.error);
//                         isListening = false;
//                     };

//                     recognition.onend = () => {
//                         console.log("Voice recognition ended.");
//                         isListening = false;
//                     };
//                 }

//                 recognition.onresult = (event) => {
//                     const reply = event.results[0][0].transcript.toLowerCase();
//                     console.log("User selected:", reply);

//                     let selectedId = null;
//                     console.log(students);
//                     if (reply.includes("first")) {
//                         selectedId = students[0]?.student_id;
//                     } else if (reply.includes("second")) {
//                         selectedId = students[1]?.student_id;
//                     } else if (reply.includes("third")) {
//                         selectedId = students[2]?.student_id;
//                     }

//                     if (selectedId) {
//                         // const url = "{{ url('fees-collect/printReceipt') }}/" + selectedId;
//                         // window.location.href = url;
//                         var url = "{{ url('fees-collect/printReceipt') }}/" + selectedId;

//                 $('#receiptModalBody').html('<div class="text-center p-5">Loading...</div>');
//                 $('#receiptModal').modal('show');

//                 $.ajax({
//                     url: url,
//                     type: 'GET',
//                     success: function (response) {
//                         $('#receiptModalBody').html(response);
//                          if (recognition && isListening) {
//                                 recognition.stop();
//                                 isListening = false;
//                             }

//                             voiceSearchIcon.classList.remove('fa-microphone-slash');
//                             voiceSearchIcon.classList.add('fa-microphone');
                        
//                     },
//                     error: function () {
//                         $('#receiptModalBody').html('<div class="text-danger p-4 text-center">Failed to load receipt. Please try again.</div>');
//                           if (recognition && isListening) {
//                                 recognition.stop();
//                                 isListening = false;
//                             }

//                             voiceSearchIcon.classList.remove('fa-microphone-slash');
//                             voiceSearchIcon.classList.add('fa-microphone');
//                     }
//                 });

//                     } else {
//                         speakThenListen("I did not understand. Please say first, second, or third.", students);
//                     }
//                 };

//                 if (!isListening) {
//                     recognition.start();
//                     isListening = true;
//                 }
//             };
// }


    // Trigger search when typing in input
    $('#searchName').on('keyup', function () {
        performSearch($(this).val(), 0); // No voice for manual input
    });

    // Voice recognition setup for microphone button
    const micBtn = document.getElementById('voiceSearchBtn');
    const voiceSearchIcon = document.getElementById('voiceSearchIcon');
    const inputField = document.getElementById('searchName');

    // micBtn.addEventListener('click', () => {
    //     if (!isListening) {
    //         recognition.start();
    //         isListening = true;
    //         voiceSearchIcon.classList.remove('fa-microphone');
    //         voiceSearchIcon.classList.add('fa-microphone-slash');
    //     } else {
    //         recognition.stop();
    //         isListening = false;
    //         voiceSearchIcon.classList.remove('fa-microphone-slash');
    //         voiceSearchIcon.classList.add('fa-microphone');
    //     }
    // });                                     

    // Mic button
        micBtn.addEventListener('click', () => {
            if (!('webkitSpeechRecognition' in window)) {
                alert("Voice recognition not supported.");
                return;
            }

            if (!micRecognition) {
                micRecognition = new webkitSpeechRecognition();
                micRecognition.continuous = false;
                micRecognition.interimResults = false;
                micRecognition.lang = 'en-US';

                micRecognition.onresult = (event) => {
                    const transcript = event.results[0][0].transcript;
                    inputField.value = transcript;
                    performSearch(transcript, 1);
                };

                micRecognition.onerror = () => {
                    isMicListening = false;
                };

                micRecognition.onend = () => {
                    isMicListening = false;
                    voiceSearchIcon.classList.remove('fa-microphone-slash');
                    voiceSearchIcon.classList.add('fa-microphone');
                };
            }

            if (!isMicListening) {
                micRecognition.start();
                isMicListening = true;
                voiceSearchIcon.classList.remove('fa-microphone');
                voiceSearchIcon.classList.add('fa-microphone-slash');
            } else {
                micRecognition.stop();
                isMicListening = false;
                voiceSearchIcon.classList.remove('fa-microphone-slash');
                voiceSearchIcon.classList.add('fa-microphone');
            }
        });

    // Ensure voice recognition stops when speaking ends
    // if ('webkitSpeechRecognition' in window) {
    //     recognition = new webkitSpeechRecognition();
    //     recognition.continuous = false;
    //     recognition.interimResults = false;
    //     recognition.lang = 'en-US';

    //     recognition.onresult = (event) => {
    //         const transcript = event.results[0][0].transcript;
    //         inputField.value = transcript;
    //         performSearch(transcript, 1); // Trigger search on voice input
    //     };

    //     recognition.onend = () => {
    //         if (isListening) {
    //             recognition.start(); // Continue listening if not manually stopped
    //         } else {
    //             voiceSearchIcon.classList.remove('fa-microphone-slash');
    //             voiceSearchIcon.classList.add('fa-microphone');
    //         }
    //     };

    //     recognition.onerror = (event) => {
    //         console.error('Voice recognition error:', event.error);
    //         isListening = false;
    //         voiceSearchIcon.classList.remove('fa-microphone-slash');
    //         voiceSearchIcon.classList.add('fa-microphone');
    //     };
    // } else {
    //     micBtn.style.display = 'none';
    //     alert('Voice search is not supported in this browser.');
    // }
});

</script>



<style>
    #students_table tbody tr.clickable-row { cursor: pointer; }
    #students_table tbody tr.clickable-row:hover { background-color: rgba(0,0,0,.03); }
</style>
<script>
    (function() {
        var $main = $('#fees-collect-main');
        var $receiptPanel = $('#receipt-panel');
        var $receiptContent = $('#receipt-content');
        var $receiptLoader = $('#receipt-loader');
        var $receiptEditBtn = $('#receipt-panel-edit-btn');
        var $collectPanel = $('#collect-panel');
        var $collectContent = $('#collect-content');
        var $collectLoader = $('#collect-loader');
        var editBaseUrl = "{{ url('fees-collect/edit') }}/";

        function showReceiptPanel() {
            $main.hide();
            $collectPanel.hide();
            $receiptPanel.show();
        }
        function showCollectPanel() {
            $main.hide();
            $receiptPanel.hide();
            $collectPanel.show();
        }
        function showMain() {
            $receiptPanel.hide();
            $collectPanel.hide();
            $main.show();
        }

        function openReceipt(studentId, assignId) {
            var url = "{{ url('fees-collect/printReceipt') }}/" + studentId;
            showReceiptPanel();
            $receiptContent.empty().hide();
            $receiptLoader.show();
            if (assignId) {
                $receiptEditBtn.attr('href', editBaseUrl + assignId).show();
            } else {
                $receiptEditBtn.hide();
            }
            $.ajax({
                url: url,
                type: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (html) {
                    $receiptLoader.hide();
                    $receiptContent.html(html).show();
                },
                error: function () {
                    $receiptLoader.hide();
                    $receiptContent.html('<div class="alert alert-danger">Failed to load receipt. Please try again.</div>').show();
                }
            });
        }

        $(document).on('click', '#students_table tbody tr.clickable-row', function (e) {
            if ($(e.target).closest('.no-row-click').length) return;
            var studentId = $(this).data('student-id');
            var assignId = $(this).data('assign-id');
            if (studentId) openReceipt(studentId, assignId);
        });

        $('.btn-back-from-receipt').on('click', function () { showMain(); });

        $(document).on('click', '.btn-collect-fees', function (e) {
            e.stopPropagation();
            var url = $(this).data('url');
            showCollectPanel();
            $collectContent.empty().hide();
            $collectLoader.show();
            $.ajax({
                url: url,
                type: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (html) {
                    $collectLoader.hide();
                    $collectContent.html(html).show();
                    if (typeof window.initCollectForm === 'function') window.initCollectForm();
                },
                error: function () {
                    $collectLoader.hide();
                    $collectContent.html('<div class="alert alert-danger">Failed to load form. <a href="' + url + '" target="_blank">Open in new tab</a></div>').show();
                }
            });
        });

        $('.btn-back-from-collect').on('click', function () { showMain(); });

        window.openReceipt = openReceipt;
    })();
</script>

@endsection

@push('script')
    @include('backend.partials.delete-ajax')
@endpush
