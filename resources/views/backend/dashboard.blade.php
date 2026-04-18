@extends('backend.master')

@section('title')
    {{ ___('dashboard.Dashboard') }}
@endsection

@section('content')
    <div class="page-content dashboard-compact" id="dashboard-content">
        {{-- Download PDF (backend) --}}
        <div class="d-flex justify-content-end mb-2 no-print">
            <a href="{{ route('dashboard.export_pdf') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-2" id="dashboardExportPdf" style="background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%); border: none; border-radius: 8px; padding: 8px 16px; text-decoration: none; color: #fff;">
                <i class="fas fa-file-pdf"></i>
                <span>Download PDF</span>
            </a>
        </div>
        <div class="row g-2">
            {{-- Counter --}}
            @if (hasPermission('counter_read'))
                <div class="col-xl-3 col-lg-3 col-md-6 mb-2">
                    <div class="ot_crm_summeryBox2 d-flex align-items-center dashboard-counter-card" style="background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%); padding: 18px 20px; min-height: 100px; border-radius: 10px; box-shadow: 0 8px 20px rgba(61, 93, 148, 0.15); transition: all 0.3s ease; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
                        <div class="icon" style="width: 48px; height: 48px; min-width: 48px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 14px; position: relative; z-index: 1;">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" fill="white"/>
                            </svg>
                        </div>
                        <div class="summeryContent" style="position: relative; z-index: 1; min-width: 0;">
                            <h4 style="color: #fff; font-size: 1.35rem; font-weight: 600; margin: 0 0 6px 0; letter-spacing: 0.3px; line-height: 1.2;">{{ $data['student'] }}</h4>
                            <h6 style="color: rgba(255,255,255,0.95); font-size: 0.875rem; margin: 0; font-weight: 500; line-height: 1.3;">{{ ___('dashboard.Student') }}</h6>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 mb-2">
                    <div class="ot_crm_summeryBox2 d-flex align-items-center dashboard-counter-card" style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); padding: 18px 20px; min-height: 100px; border-radius: 10px; box-shadow: 0 8px 20px rgba(255, 215, 0, 0.15); transition: all 0.3s ease; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.2); border-radius: 50%;"></div>
                        <div class="icon" style="width: 48px; height: 48px; min-width: 48px; background: rgba(255,255,255,0.3); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 14px; position: relative; z-index: 1;">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="white"/>
                            </svg>
                        </div>
                        <div class="summeryContent" style="position: relative; z-index: 1; min-width: 0;">
                            <h4 style="color: #2c3e50; font-size: 1.35rem; font-weight: 600; margin: 0 0 6px 0; letter-spacing: 0.3px; line-height: 1.2;">{{ $data['parent'] }}</h4>
                            <h6 style="color: #2c3e50; font-size: 0.875rem; margin: 0; font-weight: 500; line-height: 1.3; opacity: 0.95;">{{ ___('dashboard.Parent') }}</h6>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 mb-2">
                    <div class="ot_crm_summeryBox2 d-flex align-items-center dashboard-counter-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 18px 20px; min-height: 100px; border-radius: 10px; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.15); transition: all 0.3s ease; position: relative; overflow: hidden;">
                        <div class="summeryContent" style="position: relative; z-index: 1; flex: 1; min-width: 0;">
                            <div class="d-flex align-items-center" style="gap: 10px; margin-bottom: 6px;">
                                <div style="flex: 1; min-width: 0;">
                                    <h4 class="amount-value" data-amount="{{ number_format($data['fees_collect'], 2) }}" style="color: #fff; font-size: 0.9rem; font-weight: 600; margin: 0; letter-spacing: 0.3px; display: none;">{{ number_format($data['fees_collect'], 2) }}</h4>
                                    <h4 class="amount-hidden" style="color: #fff; font-size: 0.9rem; font-weight: 600; margin: 0; letter-spacing: 0.3px;">••••••</h4>
                                </div>
                                <button type="button" class="toggle-amount-btn" data-target="fees-collect" style="background: rgba(255,255,255,0.2); border: none; border-radius: 8px; padding: 6px 10px; cursor: pointer; transition: all 0.3s ease; flex-shrink: 0;" title="Click to show/hide amount">
                                    <i class="fas fa-eye" style="color: #fff; font-size: 0.85rem;"></i>
                                </button>
                            </div>
                            <h6 style="color: rgba(255,255,255,0.95); font-size: 0.875rem; margin: 0; font-weight: 500; line-height: 1.3;">{{ 'Total Collection (TZS)' }}</h6>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 mb-2">
                    <div class="ot_crm_summeryBox2 d-flex align-items-center dashboard-counter-card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); padding: 18px 20px; min-height: 100px; border-radius: 10px; box-shadow: 0 8px 20px rgba(239, 68, 68, 0.15); transition: all 0.3s ease; position: relative; overflow: hidden;">
                        <div class="summeryContent" style="position: relative; z-index: 1; flex: 1; min-width: 0;">
                            <div class="d-flex align-items-center" style="gap: 10px; margin-bottom: 6px;">
                                <div style="flex: 1; min-width: 0;">
                                    <h4 class="amount-value" data-amount="{{ number_format($data['unpaid_amount'], 2) }}" style="color: #fff; font-size: 0.9rem; font-weight: 600; margin: 0; letter-spacing: 0.3px; display: none;">{{ number_format($data['unpaid_amount'], 2) }}</h4>
                                    <h4 class="amount-hidden" style="color: #fff; font-size: 0.9rem; font-weight: 600; margin: 0; letter-spacing: 0.3px;">••••••</h4>
                                </div>
                                <button type="button" class="toggle-amount-btn" data-target="unpaid-amount" style="background: rgba(255,255,255,0.2); border: none; border-radius: 8px; padding: 6px 10px; cursor: pointer; transition: all 0.3s ease; flex-shrink: 0;" title="Click to show/hide amount">
                                    <i class="fas fa-eye" style="color: #fff; font-size: 0.85rem;"></i>
                                </button>
                            </div>
                            <h6 style="color: rgba(255,255,255,0.95); font-size: 0.875rem; margin: 0; font-weight: 500; line-height: 1.3;">{{ 'Due Amount (TZS)' }}</h6>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Start Of Fees Collection -->

            <!-- End Of Fees Collection -->

            {{-- Fees collection (shown in print/PDF) --}}
            @if (hasPermission('fees_collesction_read'))
                <div class="col-12 mb-2 dashboard-print-fees">
                    <div class="ot-card chart-card2 ot_heightFull dashboard-card" style="background: #fff; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); overflow: hidden;">

                        {{-- Tittle and Hide Amounts on one row --}}
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap_20 dashboard-card-header" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 12px 16px; border-bottom: 1px solid #e0e0e0;">
                            <div class="card-title">
                                <h4 class="mb-0" style="color: #2c3e50; font-weight: 600; font-size: 1.25rem; letter-spacing: 0.2px;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle; margin-right: 10px;">
                                        <path d="M19 14V6c0-1.1-.9-2-2-2H3c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zm-9-1c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm13-6v11c0 1.1-.9 2-2 2H4v-2h17V7h2z" fill="#3d5d94"/>
                                    </svg>
                                    {{ ___('dashboard.fees_collection') }} ({{ date('Y') }})
                                </h4>
                            </div>
                            <button type="button" class="toggle-table-amounts-btn btn btn-sm" style="background: #3d5d94; color: #fff; border: none; border-radius: 8px; padding: 8px 15px; cursor: pointer; transition: all 0.3s ease;" title="Click to show/hide all amounts">
                                <i class="fas fa-eye"></i> <span>Show/Hide Amounts</span>
                            </button>
                        </div>

                        <div class="container py-2 px-3">
                            <div class="card" style="border: none; box-shadow: none;">
                                    <table class="table table-bordered text-center dashboard-table">
                                        <thead class="fw-bold">
                                            <tr>
                                                <th></th>
                                                <?php foreach ($data['fees_groups'] as $group): ?>
                                                    <th><?= htmlspecialchars($group->name) ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Initialize arrays for storing totals, amounts paid, and amounts due
                                            $totals = [];
                                            $paid = [];
                                            $due = [];
                                            
                                            // Loop through the collection summary to get totals and paid amounts for each group
                                            foreach ($data['collection_summary'] as $groupName => $summary) {
                                                $totals[] = $summary['total'];
                                                $paid[] = $summary['paid'];
                                                $due[] = $summary['total'] - $summary['paid']; // Calculate amount due
                                            }
                                            ?>
                                            <tr>
                                                <th class="fw-bold">TOTAL</th>
                                                <?php foreach ($totals as $total): ?>
                                                    <td style="font-weight: 400; font-size: 0.95rem;">
                                                        <span class="table-amount-value" data-amount="{{ number_format($total, 2) }}" style="display: none;">{{ number_format($total, 2) }}</span>
                                                        <span class="table-amount-hidden">••••••</span>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                            <tr>
                                                <th class="fw-bold">AMOUNT PAID</th>
                                                <?php foreach ($paid as $paidAmount): ?>
                                                    <td style="font-weight: 400; font-size: 0.95rem;">
                                                        <span class="table-amount-value" data-amount="{{ number_format($paidAmount, 2) }}" style="display: none;">{{ number_format($paidAmount, 2) }}</span>
                                                        <span class="table-amount-hidden">••••••</span>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                            <tr>
                                                <th class="fw-bold">AMOUNT DUE</th>
                                                <?php foreach ($due as $dueAmount): ?>
                                                    <td style="font-weight: 400; font-size: 0.95rem;">
                                                        <span class="table-amount-value" data-amount="{{ number_format($dueAmount, 2) }}" style="display: none;">{{ number_format($dueAmount, 2) }}</span>
                                                        <span class="table-amount-hidden">••••••</span>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        </tbody>
                                    </table>



                                </div>
                        </div>

                    </div>
                </div>
            @endif

            {{-- Revenue --}}
            @if (hasPermission('revenue_read'))
                <!-- <div class="col-12 col-lg-12 col-xl-6 col-xxl-4">
                        <div class="ot-card ot_heightFull mb-24">
                            <div class="card-header d-flex justify-content-between">
                                <div class="card-title">
                                    <h4>{{ ___('dashboard.Revenue') }} ({{ date('Y') }})</h4>
                                </div>
                            </div>
                            <div class="d-flex flex-column align-items-center w-100">
                                <div class="d-flex justify-content-between align-items-center w-100">
                                    <div id="ot-line-chart-income"></div>
                                    <div class="chart-custom-content gap-0 flex-column align-items-start">
                                        <h3>{{ ___('dashboard.total_income') }}</h3>
                                        <div class="d-flex align-items-baseline gap-2">
                                            <h2 class="counter">{{ number_format($data['income'], 2, '.', ',') }}</h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center w-100">
                                    <div id="ot-line-chart-expense"></div>
                                    <div class="chart-custom-content gap-0 flex-column align-items-start">
                                        <h3>{{ ___('dashboard.total_expense') }}</h3>
                                        <div class="d-flex align-items-baseline gap-2">
                                            <h2 class="counter">{{ number_format($data['expense'], 2, '.', ',') }}</h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center w-100">
                                    <div id="ot-line-chart-revenue"></div>
                                    <div class="chart-custom-content gap-0 flex-column align-items-start">
                                        <h3>{{ ___('dashboard.total_balance') }}</h3>
                                        <div class="d-flex align-items-baseline gap-2">
                                            <h2 class="counter">{{ number_format($data['balance'], 2, '.', ',') }}</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> -->
            @endif

            {{-- Fees collection this month --}}
            @if (hasPermission('fees_collection_this_month_read'))
                <!-- <div class="col-12 col-lg-12 col-xl-6 col-xxl-6">
                        <div class="ot-card mb-24 ot_heightFull">
                            <div class="card-header d-flex justify-content-between">
                                <div class="card-title">
                                    <h4>{{ ___('dashboard.fees_collection') }} ({{ date('M Y') }})</h4>
                                </div>
                            </div>
                            <div id="fees_collection_this_month"></div>
                        </div>
                    </div> -->
            @endif

            {{-- Quarter summary tables (one per fee group; skip Outstanding Balance) --}}
            @if (hasPermission('fees_collesction_read') && !empty($data['quarter_summary_by_group'] ?? []))
                @foreach ($data['quarter_summary_by_group'] as $groupName => $summary)
                    @if (strtolower(trim($groupName)) === 'outstanding balance')
                        @continue
                    @endif
                    <div class="col-12 mb-2">
                        <div class="ot-card chart-card2 ot_heightFull dashboard-card" style="background: #fff; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); overflow: hidden;">
                            <div class="card-header dashboard-card-header" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 12px 16px; border-bottom: 1px solid #e0e0e0;">
                                <h4 class="mb-0" style="color: #2c3e50; font-weight: 600; font-size: 1.25rem;">{{ $groupName }} – By quarter ({{ date('Y') }})</h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-center mb-0 dashboard-table">
                                        <thead class="fw-bold">
                                            <tr>
                                                <th>Quarter</th>
                                                <th>Expected (TZS)</th>
                                                <th>Paid (TZS)</th>
                                                <th>Remained (TZS)</th>
                                                <th>% Remaining</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($summary['quarters'] as $qName => $q)
                                                <tr>
                                                    <td>{{ $qName }}</td>
                                                    <td><span class="table-amount-value">{{ number_format($q['expected'], 2) }}</span><span class="table-amount-hidden" style="display: none;">••••••</span></td>
                                                    <td><span class="table-amount-value">{{ number_format($q['paid'], 2) }}</span><span class="table-amount-hidden" style="display: none;">••••••</span></td>
                                                    <td><span class="table-amount-value">{{ number_format($q['remained'], 2) }}</span><span class="table-amount-hidden" style="display: none;">••••••</span></td>
                                                    <td>{{ $q['pct_remaining'] }}%</td>
                                                </tr>
                                            @endforeach
                                            <tr class="fw-bold" style="background: #f8f9fa;">
                                                <td>Total</td>
                                                <td><span class="table-amount-value">{{ number_format($summary['total_expected'], 2) }}</span><span class="table-amount-hidden" style="display: none;">••••••</span></td>
                                                <td><span class="table-amount-value">{{ number_format($summary['total_paid'], 2) }}</span><span class="table-amount-hidden" style="display: none;">••••••</span></td>
                                                <td><span class="table-amount-value">{{ number_format($summary['total_remained'], 2) }}</span><span class="table-amount-hidden" style="display: none;">••••••</span></td>
                                                <td>—</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- Income vs Expenses summary --}}
            <div class="col-12 mb-2">
                <div class="ot-card chart-card2 dashboard-card" style="background: #fff; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); overflow: hidden;">
                    <div class="card-header dashboard-card-header d-flex flex-wrap justify-content-between align-items-center gap-2" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 12px 16px; border-bottom: 1px solid #e0e0e0;">
                        <h4 class="mb-0" style="color: #2c3e50; font-weight: 600; font-size: 1.25rem;">Income vs Expenses (<span id="ie-period-label">{{ date('Y') }}</span>)</h4>
                        <div class="d-flex flex-wrap align-items-center gap-2 no-print">
                            <select id="ie-period-select" class="form-select form-select-sm" style="width: auto; min-width: 120px;">
                                <option value="day">Day</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly" selected>Yearly</option>
                                <option value="custom">Custom</option>
                            </select>
                            <div id="ie-custom-dates" class="d-none d-md-flex align-items-center gap-2">
                                <input type="date" id="ie-start-date" class="form-control form-control-sm" style="width: auto;">
                                <span>to</span>
                                <input type="date" id="ie-end-date" class="form-control form-control-sm" style="width: auto;">
                                <button type="button" class="btn btn-sm btn-primary" id="ie-apply-custom" style="background: #3d5d94; border: none;">Apply</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered text-center mb-0 dashboard-table">
                                <thead class="fw-bold">
                                    <tr>
                                        <th>Total Income (TZS)</th>
                                        <th>Total Expenses (TZS)</th>
                                        <th>Balance (TZS)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td id="ie-income-cell"><span class="table-amount-value" id="ie-income">{{ number_format($data['income'] ?? 0, 2) }}</span><span class="table-amount-hidden" style="display: none;">••••••</span></td>
                                        <td id="ie-expense-cell"><span class="table-amount-value" id="ie-expense">{{ number_format($data['expense'] ?? 0, 2) }}</span><span class="table-amount-hidden" style="display: none;">••••••</span></td>
                                        <td id="ie-balance-cell"><span class="table-amount-value" id="ie-balance">{{ number_format($data['balance'] ?? 0, 2) }}</span><span class="table-amount-hidden" style="display: none;">••••••</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Last 5 fee collections (hidden when printing/PDF) --}}
            @if (hasPermission('fees_collesction_read') && !empty($data['last_fees_collects'] ?? []))
                <div class="col-12 mb-2 no-print">
                    <div class="ot-card chart-card2 dashboard-card" style="background: #fff; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); overflow: hidden;">
                        <div class="card-header dashboard-card-header" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 12px 16px; border-bottom: 1px solid #e0e0e0;">
                            <h4 class="mb-0" style="color: #2c3e50; font-weight: 600; font-size: 1.25rem;">Last 5 fee collections</h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered text-center mb-0 dashboard-table">
                                    <thead class="fw-bold">
                                        <tr>
                                            <th>#</th>
                                            <th>Student</th>
                                            <th>Class</th>
                                            <th>Amount (TZS)</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data['last_fees_collects'] as $idx => $fc)
                                            <tr>
                                                <td>{{ $idx + 1 }}</td>
                                                <td>{{ $fc->student ? trim($fc->student->first_name . ' ' . $fc->student->last_name) : '—' }}</td>
                                                <td>{{ $fc->student_class_name ?? '—' }}</td>
                                                <td><span class="table-amount-value">{{ number_format($fc->amount ?? 0, 2) }}</span><span class="table-amount-hidden" style="display: none;">••••••</span></td>
                                                <td>{{ $fc->date ? dateFormat($fc->date) : ($fc->created_at ? $fc->created_at->format('d M Y') : '—') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- Password Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Enter Password to View Amounts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="passwordForm">
                    <div class="modal-body">
                        <input type="password" id="amountPassword" class="form-control" placeholder="Enter Password"
                            required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="checkPassword()">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
<!-- JavaScript for modal logic -->
<script>
    function showPasswordModal() {
        var passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'));
        passwordModal.show();
    }
    function checkPassword() {
        const enteredPassword = document.getElementById('amountPassword').value;
        const correctPassword = '12348765';

        if (enteredPassword !== correctPassword) {
            alert('Incorrect password!');
            return;
        }

        // Mark amounts as unlocked for this page load
        window.dashboardAmountsUnlocked = true;

        // Show all card amounts
        document.querySelectorAll('.amount-value').forEach(function (el) {
            el.style.display = 'block';
        });
        document.querySelectorAll('.amount-hidden').forEach(function (el) {
            el.style.display = 'none';
        });

        // Show all table amounts
        document.querySelectorAll('.table-amount-value').forEach(function (el) {
            // Inline cells use inline or block; inline is fine for spans
            el.style.display = 'inline';
        });
        document.querySelectorAll('.table-amount-hidden').forEach(function (el) {
            el.style.display = 'none';
        });

        // Update icons/text on buttons
        document.querySelectorAll('.toggle-amount-btn').forEach(function (btn) {
            const icon = btn.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
        const toggleTableBtn = document.querySelector('.toggle-table-amounts-btn');
        if (toggleTableBtn) {
            const tableIcon = toggleTableBtn.querySelector('i');
            const tableText = toggleTableBtn.querySelector('span');
            if (tableIcon) {
                tableIcon.classList.remove('fa-eye');
                tableIcon.classList.add('fa-eye-slash');
            }
            if (tableText) {
                tableText.textContent = 'Hide Amounts';
            }
        }

        // Clear password field and hide modal
        document.getElementById('amountPassword').value = '';
        bootstrap.Modal.getInstance(document.getElementById('passwordModal')).hide();
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const dropdownItems = document.querySelectorAll(".term-select");

        dropdownItems.forEach(item => {
            item.addEventListener("click", function (e) {
                e.preventDefault();
                const term = this.dataset.term;

                fetch(`dashboardUpdate/${term}`)
                    .then(response => response.json())
                    .then(data => {
                        updateFeeTable(data);
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            });
        });

        function updateFeeTable(data) {
            const tbody = document.querySelector("table tbody");
            if (!tbody) return;
            tbody.innerHTML = data.html;
        }
    });
</script>

<!-- Income vs Expenses period filter and Export PDF -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    var iePeriodSelect = document.getElementById('ie-period-select');
    var ieCustomDates = document.getElementById('ie-custom-dates');
    var ieStartDate = document.getElementById('ie-start-date');
    var ieEndDate = document.getElementById('ie-end-date');
    var ieApplyCustom = document.getElementById('ie-apply-custom');
    var iePeriodLabel = document.getElementById('ie-period-label');
    var ieIncome = document.getElementById('ie-income');
    var ieExpense = document.getElementById('ie-expense');
    var ieBalance = document.getElementById('ie-balance');
    var ieUrl = '{{ route("dashboard.income_expense_by_period") }}';

    function formatDateRange(from, to) {
        if (!from || !to) return '{{ date("Y") }}';
        var a = from.split('-'), b = to.split('-');
        if (from === to) return (a[2] + ' ' + ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][a[1]-1] + ' ' + a[0]);
        if (a[0] === b[0] && a[1] === b[1]) return (a[2] + '-' + b[2] + ' ' + ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][a[1]-1] + ' ' + a[0]);
        if (a[0] === b[0] && a[1] === '01' && b[1] === '12' && a[2] === '01' && b[2] === '31') return a[0];
        return from + ' to ' + to;
    }

    function fetchIncomeExpense(period, startDate, endDate) {
        var params = new URLSearchParams({ period: period });
        if (period === 'custom' && startDate && endDate) {
            params.set('start_date', startDate);
            params.set('end_date', endDate);
        }
        fetch(ieUrl + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (ieIncome) ieIncome.textContent = Number(data.income).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                if (ieExpense) ieExpense.textContent = Number(data.expense).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                if (ieBalance) ieBalance.textContent = Number(data.balance).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                if (iePeriodLabel) iePeriodLabel.textContent = formatDateRange(data.from || '', data.to || '');
            })
            .catch(function() {});
    }

    if (iePeriodSelect) {
        iePeriodSelect.addEventListener('change', function() {
            var period = this.value;
            if (period === 'custom') {
                ieCustomDates.classList.remove('d-none');
                ieCustomDates.classList.add('d-flex');
            } else {
                ieCustomDates.classList.add('d-none');
                ieCustomDates.classList.remove('d-flex');
                fetchIncomeExpense(period);
            }
        });
    }
    if (ieApplyCustom && ieStartDate && ieEndDate) {
        ieApplyCustom.addEventListener('click', function() {
            var from = ieStartDate.value;
            var to = ieEndDate.value;
            if (from && to) fetchIncomeExpense('custom', from, to);
        });
    }

});
</script>

<!-- JavaScript for Eye Icon Toggle -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Always start with amounts hidden on each page load
        window.dashboardAmountsUnlocked = false;

        document.querySelectorAll('.amount-value').forEach(function (el) {
            el.style.display = 'none';
        });
        document.querySelectorAll('.amount-hidden').forEach(function (el) {
            el.style.display = 'block';
        });
        document.querySelectorAll('.table-amount-value').forEach(function (el) {
            el.style.display = 'none';
        });
        document.querySelectorAll('.table-amount-hidden').forEach(function (el) {
            el.style.display = 'inline';
        });

        // Toggle individual card amounts
        document.querySelectorAll('.toggle-amount-btn').forEach(btn => {
            const target = btn.getAttribute('data-target');
            const card = btn.closest('.ot_crm_summeryBox2');
            const amountValue = card.querySelector('.amount-value');
            const amountHidden = card.querySelector('.amount-hidden');
            const icon = btn.querySelector('i');

            // Toggle on click
            btn.addEventListener('click', function() {
                // If not unlocked yet, ask for password
                if (!window.dashboardAmountsUnlocked) {
                    showPasswordModal();
                    return;
                }

                const isVisible = amountValue.style.display === 'block';
                
                if (isVisible) {
                    amountValue.style.display = 'none';
                    amountHidden.style.display = 'block';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    amountValue.style.display = 'block';
                    amountHidden.style.display = 'none';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });

            // Hover effect
            btn.addEventListener('mouseenter', function() {
                this.style.background = 'rgba(255,255,255,0.3)';
                this.style.transform = 'scale(1.05)';
            });

            btn.addEventListener('mouseleave', function() {
                this.style.background = 'rgba(255,255,255,0.2)';
                this.style.transform = 'scale(1)';
            });
        });

        // Toggle table amounts
        const toggleTableBtn = document.querySelector('.toggle-table-amounts-btn');
        if (toggleTableBtn) {
            const tableAmountValues = document.querySelectorAll('.table-amount-value');
            const tableAmountHidden = document.querySelectorAll('.table-amount-hidden');
            const tableIcon = toggleTableBtn.querySelector('i');
            const tableText = toggleTableBtn.querySelector('span');

            // Toggle on click
            toggleTableBtn.addEventListener('click', function() {
                // If not unlocked yet, ask for password
                if (!window.dashboardAmountsUnlocked) {
                    showPasswordModal();
                    return;
                }

                const isVisible = tableAmountValues[0] && tableAmountValues[0].style.display === 'inline';
                
                if (isVisible) {
                    tableAmountValues.forEach(el => el.style.display = 'none');
                    tableAmountHidden.forEach(el => el.style.display = 'inline');
                    tableIcon.classList.remove('fa-eye-slash');
                    tableIcon.classList.add('fa-eye');
                    tableText.textContent = 'Show Amounts';
                } else {
                    tableAmountValues.forEach(el => el.style.display = 'inline');
                    tableAmountHidden.forEach(el => el.style.display = 'none');
                    tableIcon.classList.remove('fa-eye');
                    tableIcon.classList.add('fa-eye-slash');
                    tableText.textContent = 'Hide Amounts';
                }
            });

            // Hover effect
            toggleTableBtn.addEventListener('mouseenter', function() {
                this.style.background = '#2d4a7a';
                this.style.transform = 'translateY(-2px)';
            });

            toggleTableBtn.addEventListener('mouseleave', function() {
                this.style.background = '#3d5d94';
                this.style.transform = 'translateY(0)';
            });
        }
    });
</script>


@push('script')
    <script src="{{ global_asset('backend') }}/assets/js/apex-chart.js"></script>
@endpush

<style>
/* Dashboard compact layout */
.dashboard-compact .page-content { padding-bottom: 0.5rem; }
.dashboard-compact .row.g-2 { margin-bottom: -0.5rem; }
.dashboard-card .card-body { padding: 0.5rem 0.75rem; }
.dashboard-card-header h4 { font-size: 1.1rem !important; margin: 0; }

/* Counter cards: ensure description (h6) is clearly visible */
.dashboard-counter-card .summeryContent h6 { display: block; white-space: normal; word-break: break-word; }
.dashboard-counter-card .summeryContent h4 { display: block; }

/* Dashboard Card Hover Effects */
.ot_crm_summeryBox2:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1) !important;
}

.dashboard-card.ot-card:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,0.08) !important;
}

/* Table Styling - compact */
.dashboard-compact .table thead {
    background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);
    color: #fff;
}

.dashboard-compact .table thead th {
    color: #fff !important;
    font-weight: 600;
    padding: 10px 12px;
    border: none;
    font-size: 0.9rem;
}

.dashboard-compact .table tbody tr {
    transition: all 0.2s ease;
}

.dashboard-compact .table tbody tr:hover {
    background: #f8f9fa;
}

.dashboard-compact .table tbody td {
    padding: 10px 12px;
    vertical-align: middle;
    font-size: 0.9rem;
    color: #495057;
}

/* Dashboard Table Specific Styling */
.dashboard-table tbody td {
    font-weight: 400;
    font-size: 0.9rem;
    letter-spacing: 0.2px;
    color: #495057;
}

.dashboard-table tbody th {
    font-weight: 600;
    font-size: 0.85rem;
    color: #2c3e50;
    padding: 10px 12px;
}

/* Event List Styling */
.event_upcoming_single {
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 15px;
    background: #f8f9fa;
    transition: all 0.3s ease;
    border-left: 4px solid #3d5d94;
}

.event_upcoming_single:hover {
    background: #fff;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transform: translateX(3px);
}

.event_upcoming_single .icon {
    background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%);
    color: #fff;
    width: 60px;
    height: 60px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(61, 93, 148, 0.25);
}

.event_upcoming_single .icon h4 {
    color: #fff;
    font-size: 1.3rem;
    font-weight: 400;
    margin: 0;
    letter-spacing: 0.5px;
}

.event_upcoming_single .icon h5 {
    color: rgba(255,255,255,0.9);
    font-size: 0.8rem;
    margin: 0;
}

/* Button Styling */
.btn-outline-warning {
    border: 2px solid #FFD700 !important;
    color: #FFD700 !important;
    font-weight: 500;
    font-size: 0.9rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    padding: 8px 16px;
}

.btn-outline-warning:hover {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%) !important;
    color: #2c3e50 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 215, 0, 0.25);
}

/* Eye Icon Toggle Button Styling */
.toggle-amount-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
}

.toggle-amount-btn:hover {
    background: rgba(255,255,255,0.3) !important;
    transform: scale(1.1);
}

.toggle-amount-btn:active {
    transform: scale(0.95);
}

.toggle-amount-btn i {
    transition: all 0.3s ease;
}

.toggle-table-amounts-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.toggle-table-amounts-btn:hover {
    box-shadow: 0 4px 12px rgba(61, 93, 148, 0.3);
}

.toggle-table-amounts-btn:active {
    transform: translateY(0) !important;
}

/* Amount Display Styling */
.amount-value,
.table-amount-value {
    transition: all 0.3s ease;
}

.amount-hidden,
.table-amount-hidden {
    font-family: 'Courier New', monospace;
    letter-spacing: 2px;
    user-select: none;
}

/* Professional Typography Improvements */
.summeryContent h4 {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.4;
}

.summeryContent h6 {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.5;
    opacity: 0.9;
}

/* Card Content Refinement */
.ot_crm_summeryBox2 {
    border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Table Number Formatting */
.dashboard-table tbody td {
    font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
    font-variant-numeric: tabular-nums;
}

/* Responsive */
@media (max-width: 768px) {
    .ot_crm_summeryBox2 {
        margin-bottom: 15px;
    }
    
    .card-header h4 {
        font-size: 1.1rem !important;
    }
    
    .summeryContent h4 {
        font-size: 1.2rem !important;
    }
    
    .summeryContent h6 {
        font-size: 0.8rem !important;
    }
}

/* Print / Export to PDF: preserve dashboard design */
@media print {
    .no-print { display: none !important; }
    /* Hide top bar, sidebar, footer */
    .header,
    .sidebar,
    #sidebar,
    .footer { display: none !important; }
    /* Main content full width and no top spacing when header is hidden */
    .main-content { padding-top: 0 !important; margin-top: 0 !important; }
    /* Hide all buttons */
    button,
    .btn { display: none !important; }
    /* Hide icons (font icons and SVG) */
    i[class*="fa-"],
    i[class*="la-"],
    .dashboard-compact svg,
    .dashboard-counter-card .icon { display: none !important; }
    body * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .page-content { padding: 0 !important; }
    /* Keep Student, Parent, Total Collection, Due Amount in one row */
    .dashboard-compact .row.g-2 > .col-xl-3 {
        width: 25% !important;
        max-width: 25% !important;
        flex: 0 0 25% !important;
        min-width: 0;
    }
    .dashboard-compact .row.g-2 { display: flex !important; flex-wrap: wrap !important; }
    .ot_crm_summeryBox2,
    .dashboard-card,
    .ot-card { break-inside: avoid; box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important; }
    .dashboard-print-fees { break-inside: avoid; }
    .table thead th { background: linear-gradient(135deg, #3d5d94 0%, #392C7D 100%) !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    a[href] { text-decoration: none; color: inherit; }
}
</style>
