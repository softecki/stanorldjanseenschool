{{-- Partial for AJAX load in fees-collect index panel (no full layout) --}}
<div class="fees-collect-form-content">
    <style>
        .wf-collect-scope { --wf-border:#e2e8f0; --wf-muted:#64748b; --wf-ink:#0f172a; font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; }
        .wf-collect-card { overflow:hidden; border:1px solid var(--wf-border); border-radius:18px; background:#fff; box-shadow:0 1px 3px rgba(15,23,42,.06),0 10px 24px rgba(15,23,42,.05); }
        .wf-collect-hero { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:18px 20px; background:linear-gradient(135deg,#0f766e,#0891b2 55%,#0284c7); color:#fff; }
        .wf-collect-hero-title { display:flex; align-items:center; gap:12px; margin:0; font-size:18px; font-weight:800; letter-spacing:-.01em; }
        .wf-collect-hero-icon,.wf-stat-icon { display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; border-radius:14px; background:rgba(255,255,255,.16); color:#fff; flex:0 0 auto; }
        .wf-collect-hero-text { margin:4px 0 0 52px; color:rgba(255,255,255,.86); font-size:13px; }
        .wf-collect-button { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:42px; padding:10px 16px; border-radius:12px; background:#fff; color:#0f766e; font-weight:800; text-decoration:none; box-shadow:0 8px 22px rgba(15,23,42,.18); border:1px solid rgba(255,255,255,.45); white-space:nowrap; }
        .wf-collect-button:hover { color:#115e59; background:#ecfeff; text-decoration:none; }
        .wf-stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; padding:16px 20px; background:#f8fafc; border-bottom:1px solid var(--wf-border); }
        .wf-stat { display:flex; align-items:center; gap:12px; padding:12px; border:1px solid #e5edf5; border-radius:14px; background:#fff; }
        .wf-stat-icon { width:36px; height:36px; background:#ecfeff; color:#0e7490; }
        .wf-stat-label { margin:0; font-size:11px; font-weight:800; letter-spacing:.08em; color:var(--wf-muted); text-transform:uppercase; }
        .wf-stat-value { margin:2px 0 0; font-size:15px; font-weight:800; color:var(--wf-ink); }
        .wf-table-section { padding:18px 20px 20px; }
        .wf-section-title { display:flex; align-items:center; gap:10px; margin:0 0 10px; font-size:14px; font-weight:800; color:var(--wf-ink); }
        .wf-table-wrap { overflow-x:auto; border:1px solid var(--wf-border); border-radius:14px; background:#fff; }
        .wf-table { width:100%; margin:0; border-collapse:separate; border-spacing:0; font-size:13px; }
        .wf-table thead th { padding:11px 12px; background:#f1f5f9; color:#475569; font-size:11px; font-weight:900; letter-spacing:.07em; text-transform:uppercase; border-bottom:1px solid var(--wf-border); white-space:nowrap; }
        .wf-table tbody td { padding:12px; border-bottom:1px solid #edf2f7; color:#334155; vertical-align:middle; }
        .wf-table tbody tr:last-child td { border-bottom:0; }
        .wf-table tbody tr:hover { background:#f8fafc; }
        .wf-money,.wf-table .wf-money { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
        .wf-strong { color:var(--wf-ink); font-weight:800; }
        .wf-remained-pill { display:inline-flex; min-width:96px; justify-content:flex-end; padding:5px 9px; border-radius:999px; background:#fffbeb; color:#92400e; font-weight:800; }
        .wf-status { display:inline-flex; align-items:center; gap:6px; border-radius:999px; padding:5px 10px; font-size:12px; font-weight:800; white-space:nowrap; }
        .wf-status-paid { background:#ecfdf5; color:#047857; }
        .wf-status-unpaid { background:#fff1f2; color:#be123c; }
        .wf-action-btn { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:10px; border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; text-decoration:none; }
        .wf-action-btn:hover { background:#dbeafe; color:#1e40af; }
        .wf-check { display:flex; justify-content:center; }
        .wf-check input { width:18px; height:18px; cursor:pointer; }
        @media (max-width:768px) { .wf-collect-hero { align-items:stretch; flex-direction:column; } .wf-collect-hero-text { margin-left:0; } .wf-collect-button { width:100%; } .wf-stats { grid-template-columns:1fr; } }
    </style>

    <style>
        .wf-collect-scope {
            --wf-border: #e2e8f0;
            --wf-muted: #64748b;
            --wf-ink: #0f172a;
            --wf-soft: #f8fafc;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .wf-collect-card {
            border: 1px solid var(--wf-border);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .06), 0 10px 24px rgba(15, 23, 42, .05);
            overflow: hidden;
        }
        .wf-collect-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 20px;
            background: linear-gradient(135deg, #0f766e, #0891b2 55%, #0284c7);
            color: #fff;
        }
        .wf-collect-hero-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -.01em;
        }
        .wf-collect-hero-icon,
        .wf-stat-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .16);
            color: #fff;
            flex: 0 0 auto;
        }
        .wf-collect-hero-text {
            margin: 4px 0 0 52px;
            color: rgba(255, 255, 255, .86);
            font-size: 13px;
        }
        .wf-collect-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            padding: 10px 16px;
            border-radius: 12px;
            background: #fff;
            color: #0f766e;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .18);
            border: 1px solid rgba(255, 255, 255, .45);
            white-space: nowrap;
        }
        .wf-collect-button:hover {
            color: #115e59;
            background: #ecfeff;
            text-decoration: none;
        }
        .wf-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            padding: 16px 20px;
            background: #f8fafc;
            border-bottom: 1px solid var(--wf-border);
        }
        .wf-stat {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid #e5edf5;
            border-radius: 14px;
            background: #fff;
        }
        .wf-stat-icon {
            width: 36px;
            height: 36px;
            background: #ecfeff;
            color: #0e7490;
        }
        .wf-stat-label {
            margin: 0;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            color: var(--wf-muted);
            text-transform: uppercase;
        }
        .wf-stat-value {
            margin: 2px 0 0;
            font-size: 15px;
            font-weight: 800;
            color: var(--wf-ink);
        }
        .wf-table-section {
            padding: 18px 20px 20px;
        }
        .wf-section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 0 10px;
            font-size: 14px;
            font-weight: 800;
            color: var(--wf-ink);
        }
        .wf-table-wrap {
            overflow-x: auto;
            border: 1px solid var(--wf-border);
            border-radius: 14px;
            background: #fff;
        }
        .wf-table {
            width: 100%;
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
        }
        .wf-table thead th {
            padding: 11px 12px;
            background: #f1f5f9;
            color: #475569;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .07em;
            text-transform: uppercase;
            border-bottom: 1px solid var(--wf-border);
            white-space: nowrap;
        }
        .wf-table tbody td {
            padding: 12px;
            border-bottom: 1px solid #edf2f7;
            color: #334155;
            vertical-align: middle;
        }
        .wf-table tbody tr:last-child td {
            border-bottom: 0;
        }
        .wf-table tbody tr:hover {
            background: #f8fafc;
        }
        .wf-money,
        .wf-table .wf-money {
            text-align: right;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .wf-strong {
            color: var(--wf-ink);
            font-weight: 800;
        }
        .wf-remained-pill {
            display: inline-flex;
            min-width: 96px;
            justify-content: flex-end;
            padding: 5px 9px;
            border-radius: 999px;
            background: #fffbeb;
            color: #92400e;
            font-weight: 800;
        }
        .wf-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }
        .wf-status-paid {
            background: #ecfdf5;
            color: #047857;
        }
        .wf-status-unpaid {
            background: #fff1f2;
            color: #be123c;
        }
        .wf-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
            text-decoration: none;
        }
        .wf-action-btn:hover {
            background: #dbeafe;
            color: #1e40af;
        }
        .wf-check {
            display: flex;
            justify-content: center;
        }
        .wf-check input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .wf-collect-hero {
                align-items: stretch;
                flex-direction: column;
            }
            .wf-collect-hero-text {
                margin-left: 0;
            }
            .wf-collect-button {
                width: 100%;
            }
            .wf-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="page-header mb-3">
        <div class="row">
            <div class="col-sm-12">
                <h5 class="bradecrumb-title mb-1">{{ $data['title'] }} — {{ @$data['student']->first_name }} {{ @$data['student']->last_name }}</h5>
            </div>
        </div>
    </div>

    <div class="card ot-card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-sm-4">
                    <div class="d-flex justify-content-between align-content-center mb-3">
                        <div class="align-self-center">
                            <h5 class="title">{{ ___('student_info.admission_no') }}</h5>
                            <p class="paragraph">{{ @$data['student']->admission_no }}</p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-content-center mb-3">
                        <div class="align-self-center">
                            <h5 class="title">{{ ___('student_info.student_name') }}</h5>
                            <p class="paragraph">{{ @$data['student']->first_name }}
                                {{ @$data['student']->last_name }}</p>
                            <input type="hidden" name="student_id" id="student_id"
                                value="{{ $data['student']->id }}" />
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="d-flex justify-content-between align-content-center mb-3">
                        <div class="align-self-center">
                            <h5 class="title">{{ ___('academic.class') }}</h5>
                            <p class="paragraph">{{ @$data['student']->sessionStudentDetails->class->name }}</p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-content-center mb-3">
                        <div class="align-self-center">
                            <h5 class="title">{{ ___('academic.section') }}</h5>
                            <p class="paragraph">{{ @$data['student']->sessionStudentDetails->section->name }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="d-flex justify-content-between align-content-center mb-3">
                        <div class="align-self-center">
                            <h5 class="title">{{ ___('student_info.guardian_name') }}</h5>
                            <p class="paragraph">{{ @$data['student']->parent->guardian_name }}</p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-content-center mb-3">
                        <div class="align-self-center">
                            <h5 class="title">{{ ___('student_info.mobile_number') }}</h5>
                            <p class="paragraph">{{ @$data['student']->mobile }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $assignedLines = collect(@$data['fees_assigned'] ?? []);
                $totalFees = $assignedLines->sum(fn ($line) => (float) ($line->fees_amount ?? 0));
                $totalPaid = $assignedLines->sum(fn ($line) => (float) ($line->paid_amount ?? 0));
                $totalRemaining = $assignedLines->sum(fn ($line) => (float) ($line->remained_amount ?? 0));
                $currency = Setting('currency_symbol');
            @endphp

            <div class="wf-collect-scope">
                <div class="wf-collect-card">
                    <div class="wf-collect-hero">
                        <div>
                            <h4 class="wf-collect-hero-title">
                                <span class="wf-collect-hero-icon"><i class="fa-solid fa-receipt"></i></span>
                                <span>{{ ___('fees.fees_details') }}</span>
                            </h4>
                            <p class="wf-collect-hero-text">Select one fee line, then use Collect to record payment details.</p>
                        </div>
                        @if (hasPermission('fees_collect_create'))
                            <a href="#" class="wf-collect-button" data-bs-toggle="modal"
                                data-bs-target="#modalCustomizeWidth" onclick="feesCollect()">
                                <i class="fa-solid fa-hand-holding-dollar"></i>
                                <span>{{ ___('fees.Collect') }}</span>
                            </a>
                        @endif
                    </div>

                    <div class="wf-stats">
                        <div class="wf-stat">
                            <span class="wf-stat-icon"><i class="fa-solid fa-layer-group"></i></span>
                            <div>
                                <p class="wf-stat-label">Fee lines</p>
                                <p class="wf-stat-value">{{ $assignedLines->count() }}</p>
                            </div>
                        </div>
                        <div class="wf-stat">
                            <span class="wf-stat-icon"><i class="fa-solid fa-check-circle"></i></span>
                            <div>
                                <p class="wf-stat-label">Paid</p>
                                <p class="wf-stat-value">{{ $currency }} {{ number_format($totalPaid, 2) }}</p>
                            </div>
                        </div>
                        <div class="wf-stat">
                            <span class="wf-stat-icon"><i class="fa-solid fa-clock"></i></span>
                            <div>
                                <p class="wf-stat-label">Remaining</p>
                                <p class="wf-stat-value">{{ $currency }} {{ number_format($totalRemaining, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="wf-table-section">
                        <h5 class="wf-section-title">
                            <i class="fa-solid fa-list-check text-info"></i>
                            Assigned fee lines
                        </h5>
                        <div class="wf-table-wrap">
                            <table class="wf-table" id="students_table">
                                <thead>
                                    <tr>
                                        <th class="text-center">{{ ___('common.All') }}</th>
                                        <th>{{ ___('fees.group') }}</th>
                                        <th>{{ ___('fees.type') }}</th>
                                        <th class="wf-money">{{ 'Fees Amount'}} ({{ $currency }})</th>
                                        <th class="wf-money">{{ 'Paid Amount' }}</th>
                                        <th class="wf-money">{{ 'Remained Amount' }} ({{ $currency }})</th>
                                        <th>{{ ___('common.status') }}</th>
                                        @if (hasPermission('fees_collect_delete'))
                                            <th class="text-center">{{ ___('common.action') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                   @php $firstCheckedSet = false; @endphp
                                    @forelse ($assignedLines as $item)
                                      @php
                                            $shouldCheck = false;
                                            $remained = (float) ($item->remained_amount ?? 0);
                                            if (!$firstCheckedSet && $remained > 1) {
                                                $shouldCheck = true;
                                                $firstCheckedSet = true;
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="wf-check">
                                                    <input class="form-check-input child" type="checkbox" name="fees_assign_childrens[]"
                                                        value="{{ $item->id }}" onclick="selectOnlyOne(this)"
                                                        {{ $shouldCheck ? 'checked' : '' }}>
                                                </span>
                                            </td>
                                            <td class="wf-strong">{{ @$item->feesMaster->group->name }}</td>
                                            <td>{{ @$item->feesMaster->type->name }}</td>
                                            <td class="wf-money">{{ number_format((float) (@$item->fees_amount ?? 0), 2) }}</td>
                                            <td class="wf-money">{{ number_format((float) (@$item->paid_amount ?? 0), 2) }}</td>
                                            <td class="wf-money">
                                                <span class="wf-remained-pill">{{ number_format($remained, 2) }}</span>
                                            </td>
                                            <td>
                                                @if ($remained < 1)
                                                    <span class="wf-status wf-status-paid">
                                                        <i class="fa-solid fa-circle-check"></i>
                                                        {{ ___('fees.Paid') }}
                                                    </span>
                                                @else
                                                    <span class="wf-status wf-status-unpaid">
                                                        <i class="fa-solid fa-circle-exclamation"></i>
                                                        {{ ___('fees.Unpaid') }}
                                                    </span>
                                                @endif
                                            </td>
                                            @if (hasPermission('fees_collect_delete'))
                                                <td class="text-center">
                                                    @if ($remained > 1)
                                                        <a title="Amendment" href="{{ route('fees-collect.amendment', $item->id ) }}" class="wf-action-btn">
                                                            <i class="fa-solid fa-rotate-right"></i>
                                                        </a>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ hasPermission('fees_collect_delete') ? 8 : 7 }}" class="text-center text-muted py-4">
                                                No fee lines available.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="wf-table-section pt-0">
                        <h5 class="wf-section-title">
                            <i class="fa-solid fa-calendar-days text-info"></i>
                            Term payment breakdown
                        </h5>
                        <div class="wf-table-wrap">
                            <table class="wf-table" id="fees_terms_table">
                                <thead>
                                    <tr>
                                        <th>{{ ___('fees.type') }}</th>
                                        <th class="wf-money">{{ 'Due Term One' }}</th>
                                        <th class="wf-money">{{ 'Paid Term One' }}</th>
                                        <th class="wf-money">{{ 'Fees Term Two'}} </th>
                                        <th class="wf-money">{{ 'Due Term Two' }}</th>
                                        <th class="wf-money">{{ 'Paid Term Two' }}</th>
                                        <th class="wf-money">{{ 'Remained Amount' }} </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($assignedLines as $item)
                                        @php
                                            $isOutstanding = @$item->feesMaster->group->name == 'Outstanding Balance';
                                            $termOneDue = $isOutstanding ? null : ((float) (@$item->quater_one ?? 0) + (float) (@$item->quater_two ?? 0));
                                            $termOnePaid = $isOutstanding ? (float) (@$item->fees_amount ?? 0) : ((float) (@$item->fees_amount ?? 0) / 2) - $termOneDue;
                                            $termTwoFees = $isOutstanding ? ((float) (@$item->quater_three ?? 0) + (float) (@$item->quater_four ?? 0)) : (float) (@$item->fees_amount ?? 0) / 2;
                                            $termTwoDue = $isOutstanding ? (float) (@$item->paid_amount ?? 0) : ((float) (@$item->quater_three ?? 0) + (float) (@$item->quater_four ?? 0));
                                            $termTwoPaid = $isOutstanding ? (float) (@$item->remained_amount ?? 0) : ((float) (@$item->fees_amount ?? 0) / 2) - $termTwoDue;
                                        @endphp
                                        <tr>
                                            <td class="wf-strong">{{ @$item->feesMaster->type->name }}</td>
                                            <td class="wf-money">{{ $termOneDue === null ? '—' : number_format($termOneDue, 2) }}</td>
                                            <td class="wf-money">{{ number_format($termOnePaid, 2) }}</td>
                                            <td class="wf-money">{{ number_format($termTwoFees, 2) }}</td>
                                            <td class="wf-money">{{ number_format($termTwoDue, 2) }}</td>
                                            <td class="wf-money">{{ number_format($termTwoPaid, 2) }}</td>
                                            <td class="wf-money">
                                                <span class="wf-remained-pill">{{ number_format((float) (@$item->remained_amount ?? 0), 2) }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                No term breakdown available.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="view-modal">
        <div class="modal fade" id="modalCustomizeWidth" tabindex="-1" aria-labelledby="modalWidth"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
                {{-- modal content loaded by feesCollect() --}}
            </div>
        </div>
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
</div>
