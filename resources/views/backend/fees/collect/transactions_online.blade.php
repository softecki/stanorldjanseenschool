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
                                    href="{{ route('fees-collect.index') }}">{{ $data['title'] }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ ___('common.details') }}</li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- bradecrumb Area E n d --}}

        <div class="row">
            <div class="col-12">
                <div class="card ot-card mb-2 position-relative z_1">
                    <form action="{{ route('fees-collect-searcha') }}" enctype="multipart/form-data" method="post" id="fees-collect-searcha">
                        @csrf
                        <div class="card-header d-flex align-items-center gap-4 flex-wrap">
                            <h3 class="mb-0">{{ ___('common.Filtering') }}</h3>

                            <div class="card_header_right d-flex align-items-center gap-3 flex-fill justify-content-end flex-wrap">
                                <!-- table_searchBox -->

                                <div class="single_selectBox">
                                    <input name="start_date" type="date" class="form-control" placeholder="{{'Start Date'}} " aria-label="Search " aria-describedby="searchIcon">
                                </div>
                                <div class="single_selectBox">
                                    <input name="start_date" type="date" class="form-control" placeholder="{{'End Date'}} " aria-label="Search " aria-describedby="searchIcon">
                                </div>

                                <div class="input-group table_searchBox">
                                    <input name="name" type="text" class="form-control" placeholder="{{___('common.name')}} " aria-label="Search " aria-describedby="searchIcon">
                                    <span class="input-group-text" id="searchIcon">
                                    <i class="fa-solid fa-magnifying-glass"></i>
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


        <div class="card ot-card">
            <div class="card-body">
            <form action="{{ route('fees-collect.print-receipt') }}" enctype="multipart/form-data" method="post" id="fees-collect-print">
            @csrf
            <div class="card-header d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">{{___('fees.fees_details')}}</h4>
                    <button class="btn btn-md btn-outline-primary">Download</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered role-table" id="students_table">
                        <thead class="thead">
                        <tr>
                            <th></th>
                        <th class="purchase">{{ 'Recept No.' }}</th>
                            <th class="purchase">{{ 'Student Name' }}</th>
                            <th class="purchase">Settlement receipt</th>
                            <th class="purchase">{{ ' Date' }}</th>
                            <th class="purchase">{{ ' Amount' }} ({{ Setting('currency_symbol') }})</th>
                            <th class="purchase">{{ ___('common.status') }}</th>
                            <th class="purchase">{{ 'Bank Name' }}</th>
                           
                            @if (hasPermission('fees_collect_delete'))
                                <th class="purchase">{{ ___('common.action') }}</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody class="tbody">

                        @foreach (@$data['fees_assigned'] as $item)
                            <tr class="push-transaction-row" data-push-id="{{ $item->fees_collect_id }}">
                            <td class="no-row-click">
                                        <input class="form-check-input child" type="checkbox" name="fees_assign_ids[]" value="{{ $item->fees_collect_id }}">
                                    </td>
                            <td>{{ $item->payment_receipt }}</td>
                                <td>{{ @$item->first_name }} {{ @$item->last_name }}</td>
                                <td>{{ @$item->settlement_receipt ?? '—' }}</td>
                                <td>{{ dateFormat(@$item->transaction_date) }}</td>
                                <td>{{ @$item->transaction_amount }}</td>
                                <td>
                                        <span class="badge-basic-success-text">{{ ___('fees.Paid') }}</span>
                                </td>
                                <td>
                                    {{ @$item->bank_name }}({{ @$item->account_number }})
                                </td>
                                @if (hasPermission('fees_collect_read'))
                                    <td class="action no-row-click">
                                        <a title="{{ ___('common.view') }}" class="btn btn-sm btn-outline-primary btn-view-push" href="{{ route('fees-collect.push-transaction-details', $item->fees_collect_id) }}" data-push-id="{{ $item->fees_collect_id }}">
                                            <span class="icon mr-1"><i class="fa-solid fa-eye"></i></span> {{ ___('common.view') }}
                                        </a>
                                    </td>
                                @endif
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
                </form>
            </div>
        </div>
    </div>
    <div id="view-modal">
        <div class="modal fade" id="modalCustomizeWidth" tabindex="-1" aria-labelledby="modalWidth"
             aria-hidden="true">
            <div class="modal-dialog modal-xl">
                {{--  --}}
            </div>
        </div>
    </div>
    {{-- Modal: Push transaction details (View button / row click) --}}
    <div class="modal fade" id="pushDetailsModal" tabindex="-1" aria-labelledby="pushDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pushDetailsModalLabel">{{ ___('common.details') }} — Push Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="push-details-loader" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 mb-0 small text-muted">Loading...</p>
                    </div>
                    <div id="push-details-content"></div>
                </div>
            </div>
        </div>
    </div>
    <style>
        #students_table tbody tr.push-transaction-row { cursor: pointer; }
        #students_table tbody tr.push-transaction-row:hover { background-color: rgba(0,0,0,.03); }
    </style>
@endsection
@push('script')
    <script>
        function selectOnlyOne(checkbox) {
            var checkboxes = document.querySelectorAll('.form-check-input.child');
            checkboxes.forEach(function(cb) {
                if (cb !== checkbox) { cb.checked = false; }
            });
        }

        (function() {
            var detailsUrlTpl = "{{ route('fees-collect.push-transaction-details', ['id' => '__ID__']) }}".replace(/&amp;/g, '&');
            var cancelUrlTpl = "{{ route('fees-collect.cancel-push-transaction', ['id' => '__ID__']) }}".replace(/&amp;/g, '&');
            var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).getAttribute ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
            var modalEl = document.getElementById('pushDetailsModal');
            var contentEl = document.getElementById('push-details-content');
            var loaderEl = document.getElementById('push-details-loader');
            var modal = (typeof bootstrap !== 'undefined' && bootstrap.Modal && modalEl) ? new bootstrap.Modal(modalEl) : null;

            function openDetails(pushId) {
                if (!pushId || !contentEl || !loaderEl) return;
                contentEl.innerHTML = '';
                contentEl.style.display = 'none';
                loaderEl.style.display = 'block';
                if (modal) modal.show();
                fetch(detailsUrlTpl.replace('__ID__', pushId), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                })
                .then(function(r) {
                    if (!r.ok) throw new Error('Load failed');
                    return r.text();
                })
                .then(function(html) {
                    loaderEl.style.display = 'none';
                    contentEl.innerHTML = html;
                    contentEl.style.display = 'block';
                })
                .catch(function() {
                    loaderEl.style.display = 'none';
                    contentEl.innerHTML = '<div class="alert alert-danger">Failed to load details.</div>';
                    contentEl.style.display = 'block';
                });
            }

            document.addEventListener('click', function(e) {
                var row = e.target.closest('.push-transaction-row');
                if (row && !e.target.closest('.no-row-click')) {
                    var pushId = row.getAttribute('data-push-id');
                    if (pushId) { e.preventDefault(); openDetails(pushId); return; }
                }
                var viewBtn = e.target.closest('.btn-view-push');
                if (viewBtn) {
                    e.preventDefault();
                    var pushId = viewBtn.getAttribute('data-push-id');
                    if (pushId) openDetails(pushId);
                    return;
                }
                var cancelBtn = e.target.closest('#pushDetailsModal .btn-cancel-push-transaction');
                if (cancelBtn) {
                    e.preventDefault();
                    var pushId = cancelBtn.getAttribute('data-push-id');
                    if (!pushId) return;
                    if (!confirm('{{ ___("common.are_you_sure") }} This will reverse the collection and cancel the transaction.')) return;
                    cancelBtn.disabled = true;
                    fetch(cancelUrlTpl.replace('__ID__', pushId), {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ _token: csrfToken })
                    })
                    .then(function(r) { return r.json().catch(function() { return {}; }).then(function(data) { return { ok: r.ok, data: data }; }); })
                    .then(function(res) {
                        if (res.ok && res.data && res.data.status) {
                            if (modal) modal.hide();
                            window.location.reload();
                        } else {
                            alert(res.data && res.data.message ? res.data.message : '{{ ___("common.something_went_wrong_please_try_again") }}');
                            cancelBtn.disabled = false;
                        }
                    })
                    .catch(function() {
                        alert('{{ ___("common.something_went_wrong_please_try_again") }}');
                        cancelBtn.disabled = false;
                    });
                }
            });
        })();
    </script>
    @include('backend.partials.delete-ajax')
@endpush
