<aside class="sidebar" id="sidebar">

    <x-sidebar-header />

    <div class="sidebar-menu srollbar">
        <div class="sidebar-menu-section">
            <!-- parent menu list start  -->
            <ul class="sidebar-dropdown-menu">
                <li class="sidebar-menu-item">
                    <a href="{{ route('dashboard') }}" class="parent-item-content">
                        {{-- <img src="{{ asset('backend') }}/assets/images/icons/notification-status.svg" alt="Dashboard" /> --}}
                        <i class="las la-tachometer-alt"></i>
                        <span class="on-half-expanded">{{ ___('common.dashboard') }}</span>
                    </a>
                </li>


               


                <!-- Subscription of schools start -->
                @if(env('APP_SAAS'))
                <li class="sidebar-menu-item {{ set_menu(['subscription*']) }}">
                    <a href="{{ route('subscription') }}" class="parent-item-content">
                        <i class="las la-user"></i>
                        <span class="on-half-expanded">{{ ___('settings.Subscriptions') }}</span>
                    </a>
                </li>
                @endif
                <!-- Subscription of schools end-->


                {{-- Student info --}}
                @if ((hasPermission('student_read') ||
                hasPermission('student_category_read') ||
                hasPermission('promote_students_read') ||
                hasPermission('disabled_students_read') ||
                hasPermission('admission_read') ||
                hasPermission('parent_read')) && hasFeature('student_info'))
                <li class="sidebar-menu-item {{ set_menu(['students*','student*', 'student/category*']) }}">
                    <a class="parent-item-content has-arrow">
                        {{-- <img src="{{ asset('backend') }}/assets/images/icons/clipboard.svg" alt="Dashboard" /> --}}
                        <i class="las la-users"></i>
                        <span class="on-half-expanded">{{ ___('settings.student_info') }}</span>
                    </a>

                    <!-- second layer child menu list start  -->

                    <ul class="child-menu-list">
                        @if (hasPermission('student_read'))
                        <li class="sidebar-menu-item {{ set_menu(['student','student/create','student/edit']) }}">
                            <a href="{{ route('student.index') }}">Students</a>
                        </li>
                        @endif
{{--                        @if (hasPermission('student_read'))--}}
{{--                        <li class="sidebar-menu-item {{ set_menu(['student','student/create','student/edit','student/formtwo']) }}">--}}
{{--                            <a href="{{ route('student.formtwo') }}">Form Two</a>--}}
{{--                        </li>--}}
{{--                        @endif--}}
                        @if (hasPermission('student_category_read'))
                        <li class="sidebar-menu-item {{ set_menu(['student/category*']) }}">
                            <a href="{{ route('student_category.index') }}">{{ ___('student_info.student_category') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('promote_students_read'))
                        <li class="sidebar-menu-item {{ set_menu(['promote/students*']) }}">
                            <a href="{{ route('promote_students.index') }}">{{ ___('student_info.promote_students') }}</a>
                        </li>
                        @endif
                        {{-- @if (hasPermission('disabled_students_read'))
                        <li class="sidebar-menu-item {{ set_menu(['disabled/students*']) }}">
                            <a href="{{ route('disabled_students.index') }}">{{ ___('student_info.disabled_students') }}</a>
                        </li>
                        @endif--}}
                        @if (hasPermission('parent_read'))
                        <li class="sidebar-menu-item {{ set_menu(['parent*']) }}">
                            <a href="{{ route('parent.index') }}">{{ ___('student_info.guardian') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('student_read'))
                        <li class="sidebar-menu-item {{ set_menu(['student/qr-code*']) }}">
                            <a href="{{ route('student.index') }}">
                                <i class="las la-qrcode"></i>
                                {{ ___('common.qr_code') }}
                            </a>
                        </li>
                        @endif
                        @if (hasPermission('student_read'))
                        <li class="sidebar-menu-item {{ set_menu(['student-deleted-history*']) }}">
                            <a href="{{ route('student_deleted_history.index') }}">
                                <i class="las la-history"></i>
                                {{ ___('common.deleted_student_history') ?? 'Deleted student history' }}
                            </a>
                        </li>
                        @endif

                    </ul>
                </li>
                @endif

                {{-- @if (hasPermission('id_card_read') || hasPermission('id_card_generate_read'))
                <li class="sidebar-menu-item {{ set_menu(['idcard*']) }}">
                    <a class="parent-item-content has-arrow">
                        <i class="las la-swatchbook"></i>
                        <span class="on-half-expanded">{{ ___('common.id_cards') }}</span>
                    </a>
                    <ul class="child-menu-list">
                        @if (hasPermission('id_card_read'))
                        <li class="sidebar-menu-item {{ set_menu(['idcard', 'idcard/create', 'idcard/edit*']) }}">
                            <a href="{{ route('idcard.index') }}">{{ ___('common.id_cards') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('id_card_generate_read'))
                        <li class="sidebar-menu-item {{ set_menu(['idcard/generate*']) }}">
                            <a href="{{ route('idcard.generate') }}">{{ ___('common.generate_id_cards') }}</a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif --}}

                {{-- @if (hasPermission('certificate_read') || hasPermission('certificate_generate_read'))
                <li class="sidebar-menu-item {{ set_menu(['certificate*']) }}">
                    <a class="parent-item-content has-arrow">
                        <i class="las la-swatchbook"></i>
                        <span class="on-half-expanded">{{ ___('common.certificates') }}</span>
                    </a>
                    <ul class="child-menu-list">
                        @if (hasPermission('certificate_read'))
                        <li class="sidebar-menu-item {{ set_menu(['certificate', 'certificate/create', 'certificate/edit*']) }}">
                            <a href="{{ route('certificate.index') }}">{{ ___('common.certificates') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('certificate_generate_read'))
                        <li class="sidebar-menu-item {{ set_menu(['certificate/generate*']) }}">
                            <a href="{{ route('certificate.generate') }}">{{ ___('common.generate_certificates') }}</a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif --}}


                {{-- Start Academic --}}
                @if ((hasPermission('classes_read') ||
                hasPermission('section_read') ||
                hasPermission('shift_read') ||
                hasPermission('class_setup_read') ||
                hasPermission('subject_read') ||
                hasPermission('subject_assign_read') ||
                hasPermission('time_schedule_read') ||
                hasPermission('class_room_read')) && hasFeature('academic'))
                <li class="sidebar-menu-item {{ set_menu(['academic*']) }}">
                    <a class="parent-item-content has-arrow">
                        <i class="las la-graduation-cap"></i>
                        <span class="on-half-expanded">{{ ___('settings.academic') }}</span>
                    </a>
                    <ul class="child-menu-list">
                        @if (hasPermission('classes_read'))
                        <li class="sidebar-menu-item {{ set_menu(['classes*']) }}">
                            <a href="{{ route('classes.index') }}">{{ ___('settings.class') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('section_read'))
                        <li class="sidebar-menu-item {{ set_menu(['section*']) }}">
                            <a href="{{ route('section.index') }}">{{ ___('settings.section') }}</a>
                        </li>
                        @endif
{{--                        @if (hasPermission('shift_read'))--}}
{{--                        <li class="sidebar-menu-item {{ set_menu(['shift*']) }}">--}}
{{--                            <a href="{{ route('shift.index') }}">{{ ___('settings.shift') }}</a>--}}
{{--                        </li>--}}
{{--                        @endif--}}
                        @if (hasPermission('class_setup_read'))
                        <li class="sidebar-menu-item {{ set_menu(['class-setup*']) }}">
                            <a href="{{ route('class-setup.index') }}">{{ ___('settings.class_setup') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('subject_read'))
                        <li class="sidebar-menu-item {{ set_menu(['subject*']) }}">
                            <a href="{{ route('subject.index') }}">{{ ___('settings.subject') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('subject_assign_read'))
                        <li class="sidebar-menu-item {{ set_menu(['assign-subject*']) }}">
                            <a href="{{ route('assign-subject.index') }}">{{ ___('settings.subject_assign') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('time_schedule_read'))
                        <li class="sidebar-menu-item {{ set_menu(['time/schedule*']) }}">
                            <a href="{{ route('time_schedule.index') }}">{{ ___('academic.time_schedule') }}</a>
                        </li>
                        @endif

                        {{-- @if (hasPermission('class_room_read'))
                        <li class="sidebar-menu-item {{ set_menu(['class-room*']) }}">
                            <a href="{{ route('class-room.index') }}">{{ ___('settings.class_room') }}</a>
                        </li>
                        @endif --}}
                    </ul>
                </li>
                @endif
                {{-- End Academic --}}



                {{-- Start Fees --}}
                @if ((hasPermission('fees_group_read') ||
                hasPermission('fees_type_read') ||
                hasPermission('fees_master_read') ||
                hasPermission('fees_assign_read') ||
                hasPermission('fees_collect_read')) && hasFeature('fees'))
                    <li class="sidebar-menu-item {{ set_menu(['fees*']) }}">
                        <a class="parent-item-content has-arrow">
                            <i class="las la-hand-holding-usd"></i>
                            <span class="on-half-expanded">{{ ___('settings.fees') }}</span>
                        </a>
                        <ul class="child-menu-list">
                            @if (hasPermission('fees_group_read'))
                                <li class="sidebar-menu-item {{ set_menu(['fees-group*']) }}">
                                    <a href="{{ route('fees-group.index') }}">{{ ___('settings.group') }}</a>
                                </li>
                            @endif
                            @if (hasPermission('fees_type_read'))
                                <li class="sidebar-menu-item {{ set_menu(['fees-type*']) }}">
                                    <a href="{{ route('fees-type.index') }}">{{ ___('settings.type') }}</a>
                                </li>
                            @endif
                            @if (hasPermission('fees_master_read'))
                                <li class="sidebar-menu-item {{ set_menu(['fees-master*']) }}">
                                    <a href="{{ route('fees-master.index') }}">{{ ___('settings.master') }}</a>
                                </li>
                            @endif
                            @if (hasPermission('fees_assign_read'))
                                <li class="sidebar-menu-item {{ set_menu(['fees-assign*']) }}">
                                    <a href="{{ route('fees-assign.index') }}">{{ ___('settings.assign') }}</a>
                                </li>
                            @endif
                            @if (hasPermission('fees_collect_read'))
                                <li class="sidebar-menu-item {{ set_menu(['fees-collect*']) }}">
                                    <a href="{{ route('fees-collect.index') }}">{{ ___('settings.collect') }}</a>
                                </li>
                            @endif

                            @if (hasPermission('fees_collect_read'))
                                <li class="sidebar-menu-item {{ set_menu(['fees-collect*']) }}">
                                    <a href="{{ route('fees-collect.collect-list') }}">{{ 'Transactions' }}</a>
                                </li>
                            @endif

                               @if (hasPermission('fees_collect_read'))
                                <li class="sidebar-menu-item {{ set_menu(['fees-collect*']) }}">
                                    <a href="{{ route('fees-collect.collect-transactions') }}">{{ 'Online Transactions' }}</a>
                                </li>
                            @endif

                             @if (hasPermission('fees_collect_read'))
                                <li class="sidebar-menu-item {{ set_menu(['fees-collect*']) }}">
                                    <a href="{{ route('fees-collect.collect-amendment') }}">{{ 'Amendments' }}</a>
                                </li>
                            @endif 
                        </ul>
                    </li>
                @endif
                {{-- End Fees --}}

                {{-- Start Accounts --}}
                @if ((hasPermission('account_head_read') ||
               hasPermission('income_read') ||
               hasPermission('expense_read')) && hasFeature('account'))
                    <li class="sidebar-menu-item {{ set_menu([
                    'account_head.index','chart-of-accounts*','payment-methods*',
                    'accounting.dashboard','accounting.cashbook','accounting.reports*','accounting.audit-log','accounting.bank-reconciliation*',
                    'account_head.create','account_head.edit','product*','item*','balance*'
                ]) }}">
                        <a class="parent-item-content has-arrow">
                            <i class="las la-dolly"></i>
                            <span class="on-half-expanded">{{ ___('account.Accounts') }}</span>
                        </a>
                        <ul class="child-menu-list">
                            {{--                            @if (hasPermission('expense_read'))--}}
                            {{--                                <li class="sidebar-menu-item {{ set_menu(['deposit*']) }}">--}}
                            {{--                                    <a href="{{ route('deposit.index') }}">{{ 'Deposits & Funds'}}</a>--}}
                            {{--                                </li>--}}
                            {{--                            @endif--}}

                            {{--                            @if (hasPermission('expense_read'))--}}
                            {{--                                <li class="sidebar-menu-item {{ set_menu(['payments*']) }}">--}}
                            {{--                                    <a href="{{ route('payments.index') }}">{{ 'Payments'}}</a>--}}
                            {{--                                </li>--}}
                            {{--                            @endif--}}

                            {{--                            @if (hasPermission('expense_read'))--}}
                            {{--                                <li class="sidebar-menu-item {{ set_menu(['transactions*']) }}">--}}
                            {{--                                    <a href="{{ route('transactions.index') }}">{{ 'Transactions'}}</a>--}}
                            {{--                                </li>--}}
                            {{--                            @endif--}}

                            @if (hasPermission('income_read') && Route::has('accounting.dashboard'))
                                <li class="sidebar-menu-item {{ set_menu(['accounting.dashboard','accounting.cashbook','accounting.reports*','accounting.audit-log']) }}">
                                    <a href="{{ route('accounting.dashboard') }}">{{ __('Financial Dashboard') }}</a>
                                </li>
                            @endif
                            @if (hasPermission('account_head_read'))
                                <li class="sidebar-menu-item {{ set_menu(['chart-of-accounts*']) }}">
                                    <a href="{{ route('chart-of-accounts.index') }}">{{ __('Chart of Accounts') }}</a>
                                </li>
                            @endif
                            @if (hasPermission('account_head_read'))
                                <li class="sidebar-menu-item {{ set_menu(['payment-methods*']) }}">
                                    <a href="{{ route('payment-methods.index') }}">{{ __('Payment Methods') }}</a>
                                </li>
                            @endif
                            @if (hasPermission('account_head_read'))
                                <li class="sidebar-menu-item {{ set_menu([
                            'account_head.index',
                            'account_head.create',
                            'account_head.edit',
                        ]) }}">
                                    <a href="{{ route('account_head.index') }}">{{ ___('account.account_head') }}</a>
                                </li>
                            @endif
                            @if (hasPermission('income_read'))
                                <li class="sidebar-menu-item {{ set_menu(['income*']) }}">
                                    <a href="{{ route('income.index') }}">{{ ___('account.income') }}</a>
                                </li>
                            @endif
                            @if (hasPermission('expense_read'))
                                <li class="sidebar-menu-item {{ set_menu(['expense*']) }}">
                                    <a href="{{ route('expense.index') }}">{{ ___('account.expense') }}</a>
                                </li>
                            @endif

                            {{-- @if (hasPermission('expense_read'))
                            <li class="sidebar-menu-item {{ set_menu(['cash*']) }}">
                                <a href="{{ route('cash.index') }}">{{ 'Cash Deposit' }}</a>
                            </li>
                            @endif
--}}
                            @if (hasPermission('expense_read'))
                            <li class="sidebar-menu-item {{ set_menu(['product*']) }}">
                                <a href="{{ route('product.index') }}">{{ 'Products' }}</a>
                            </li>
                            @endif 

                             @if (hasPermission('expense_read'))
                            <li class="sidebar-menu-item {{ set_menu(['item*']) }}">
                                <a href="{{ route('item.index') }}">{{ 'Items' }}</a>
                            </li>
                            @endif 

                                @if (hasPermission('expense_read'))
                            <li class="sidebar-menu-item {{ set_menu(['item*']) }}">
                                <a href="{{ route('balance.index') }}">{{ 'Balance' }}</a>
                            </li>
                            @endif

                            @if (hasPermission('income_read'))
                            <li class="sidebar-menu-item {{ set_menu(['accounting.bank-reconciliation*']) }}">
                                <a href="{{ route('accounting.bank-reconciliation.index') }}">{{ __('Bank Reconciliation') }}</a>
                            </li>
                            @endif

                            {{--                            @if (hasPermission('expense_read'))--}}
                            {{--                                <li class="sidebar-menu-item {{ set_menu(['suppliers*']) }}">--}}
                            {{--                                    <a href="{{ route('suppliers.index') }}">{{ 'Suppliers' }}</a>--}}
                            {{--                                </li>--}}
                            {{--                            @endif--}}

                            {{--                            @if (hasPermission('expense_read'))--}}
                            {{--                                <li class="sidebar-menu-item {{ set_menu(['invoices*']) }}">--}}
                            {{--                                    <a href="{{ route('invoices.index') }}">{{ 'Invoices' }}</a>--}}
                            {{--                                </li>--}}
                            {{--                            @endif--}}
                        </ul>
                    </li>
                @endif
                {{-- End Transactions --}}

                 {{-- Report start --}}
                @if ((hasPermission('report_marksheet_read') ||
                hasPermission('report_merit_list_read') ||
                hasPermission('report_progress_card_read') ||
                hasPermission('report_due_fees_read') ||
                hasPermission('report_fees_collection_read') ||
                hasPermission('report_account_read') ||
                hasPermission('class_routine_read') ||
                hasPermission('exam_routine_read') ||
                hasPermission('report_attendance_read')) && hasFeature('report'))
                <li class="sidebar-menu-item {{ set_menu(['report-*']) }}">
                    <a class="parent-item-content has-arrow">
                        <i class="las la-file-invoice"></i>
                        <span class="on-half-expanded">{{ ___('settings.Report') }}</span>
                    </a>
                    <ul class="child-menu-list">
                        @if (hasPermission('report_fees_collection_read'))
                            <li class="sidebar-menu-item {{ set_menu(['report-fees-collection*']) }}">
                                <a href="{{ route('report-fees-collection.index') }}">{{ ___('settings.fees_collection') }}</a>
                            </li>
                        @endif
                        @if (hasPermission('report_fees_collection_read'))
                            <li class="sidebar-menu-item {{ set_menu(['report-fees-summary*']) }}">
                                <a href="{{ route('report-fees-summary.index') }}">{{ 'Collection Summary' }}</a>
                            </li>
                        @endif
                        @if (hasPermission('report_fees_collection_read'))
                            <li class="sidebar-menu-item {{ set_menu(['report-students*']) }}">
                                <a href="{{ route('report-students.index') }}">{{ 'Student List' }}</a>
                            </li>
                        @endif
                        @if (hasPermission('report_fees_collection_read'))
                            <li class="sidebar-menu-item {{ set_menu(['report-fees-by-year*']) }}">
                                <a href="{{ route('report-fees-by-year.index') }}">{{ 'Fees Assignment By Year' }}</a>
                            </li>
                        @endif
                        @if (hasPermission('report_fees_collection_read'))
                            <li class="sidebar-menu-item {{ set_menu(['report-boarding-students*']) }}">
                                <a href="{{ route('report-boarding-students.index') }}">{{ 'Boarding Students Report' }}</a>
                            </li>
                        @endif
                        @if (hasPermission('report_fees_collection_read'))
                            <li class="sidebar-menu-item {{ set_menu(['report-duplicate-students*']) }}">
                                <a href="{{ route('report-duplicate-students.index') }}">{{ 'Duplicate Students' }}</a>
                            </li>
                        @endif
                        @if (hasPermission('report_marksheet_read'))
                        <li class="sidebar-menu-item {{ set_menu(['report-marksheet*']) }}">
                            <a href="{{ route('report-marksheet.index') }}">{{ ___('settings.marksheet') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('report_merit_list_read'))
                        <li class="sidebar-menu-item {{ set_menu(['report-merit-list*']) }}">
                            <a href="{{ route('report-merit-list.index') }}">{{ ___('settings.merit_list') }}</a>
                        </li>
                        @endif
                        {{-- @if (hasPermission('report_progress_card_read'))
                        <li class="sidebar-menu-item {{ set_menu(['report-progress-card*']) }}">
                            <a href="{{ route('report-progress-card.index') }}">{{ ___('settings.progress_card') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('report_due_fees_read'))
                        <li class="sidebar-menu-item {{ set_menu(['report-due-fees*']) }}">
                            <a href="{{ route('report-due-fees.index') }}">{{ ___('settings.due_fees') }}</a>
                        </li>
                        @endif --}}

                        @if (hasPermission('report_account_read'))
                        <li class="sidebar-menu-item {{ set_menu(['report-account*']) }}">
                            <a href="{{ route('report-account.index') }}">{{ ___('settings.Accounts') }}</a>
                        </li>
                        @endif
                        {{-- @if (hasPermission('class_routine_read'))
                        <li class="sidebar-menu-item {{ set_menu(['report-class-routine*']) }}">
                            <a href="{{ route('report-class-routine.index') }}">{{ ___('settings.class_routine') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('exam_routine_read'))
                        <li class="sidebar-menu-item {{ set_menu(['report-exam-routine*']) }}">
                            <a href="{{ route('report-exam-routine.index') }}">{{ ___('settings.exam_routine') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('report_attendance_read'))
                        <li class="sidebar-menu-item {{ set_menu(['report-attendance/report*']) }}">
                            <a href="{{ route('report-attendance.report') }}">{{ ___('settings.Attendance') }}</a>
                        </li>
                        @endif --}}
                    </ul>
                </li>
                @endif
                {{-- Report end --}}



                {{-- start routines --}}
                {{-- @if ((hasPermission('class_routine_read') || hasPermission('exam_routine_read')) && hasFeature('routine'))
                <li class="sidebar-menu-item {{ set_menu(['class-routine*', 'exam-routine*']) }}">
                    <a class="parent-item-content has-arrow">
                        <i class="las la-calendar-alt"></i>
                        <span class="on-half-expanded">{{ ___('settings.Routines') }}</span>
                    </a>
                    <ul class="child-menu-list">

                        @if (hasPermission('class_routine_read'))
                        <li class="sidebar-menu-item {{ set_menu(['class-routine*']) }}">
                            <a href="{{ route('class-routine.index') }}">{{ ___('settings.class_routine') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('exam_routine_read'))
                        <li class="sidebar-menu-item {{ set_menu(['exam-routine*']) }}">
                            <a href="{{ route('exam-routine.index') }}">{{ ___('settings.exam_routine') }}</a>
                        </li>
                        @endif

                    </ul>
                </li>
                @endif --}}
                {{-- End routines --}}

                {{-- Start Attendance --}}
                {{-- @if ((hasPermission('attendance_read') ||
                hasPermission('report_attendance_read')) && hasFeature('attendance'))
                <li class="sidebar-menu-item {{ set_menu(['attendance*']) }}">
                    <a class="parent-item-content has-arrow">
                        <i class="las la-pen-nib"></i>
                        <span class="on-half-expanded">{{ ___('settings.Attendance') }}</span>
                    </a>
                    <ul class="child-menu-list">
                        @if (hasPermission('attendance_read'))
                        <li class="sidebar-menu-item {{ set_menu(['attendance.index', 'attendance.search']) }}">
                            <a href="{{ route('attendance.index') }}">{{ ___('settings.Attendance') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('report_attendance_read'))
                        <li class="sidebar-menu-item {{ set_menu(['attendance/report*']) }}">
                            <a href="{{ route('attendance.report') }}">{{ ___('settings.attendance_report') }}</a>
                        </li>
                        @endif

                        @if (hasPermission('report_attendance_read'))
                        <li class="sidebar-menu-item {{ set_menu(['attendance.notification']) }}">
                            <a href="{{ route('attendance.notification') }}">{{ ___('settings.notification_setup') }}</a>
                        </li>
                        @endif

                    </ul>
                </li>
                @endif --}}
                {{-- End Attendance --}}



                {{-- Start exam --}}
                {{-- @if ((hasPermission('exam_type_read') ||
                hasPermission('marks_grade_read') ||
                hasPermission('exam_assign_read') ||
                hasPermission('marks_register_read') ||
                hasPermission('exam_setting_read')) && hasFeature('examination'))
                <li class="sidebar-menu-item {{ set_menu(['exam-type*', 'marks-grade*', 'exam-assign*', 'marks-register*', 'examination-settings*']) }}">
                    <a class="parent-item-content has-arrow">
                        <i class="las la-book-reader"></i>
                        <span class="on-half-expanded">{{ ___('settings.examination') }}</span>
                    </a>
                    <ul class="child-menu-list">
                        @if (hasPermission('exam_type_read'))
                        <li class="sidebar-menu-item {{ set_menu(['exam-type*']) }}">
                            <a href="{{ route('exam-type.index') }}">{{ ___('settings.type') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('marks_grade_read'))
                        <li class="sidebar-menu-item {{ set_menu(['marks-grade*']) }}">
                            <a href="{{ route('marks-grade.index') }}">{{ ___('examination.marks_grade') }}</a>
                        </li>
                        @endif

                        @if (hasPermission('exam_assign_read'))
                        <li class="sidebar-menu-item {{ set_menu(['exam-assign*']) }}">
                            <a href="{{ route('exam-assign.index') }}">{{ ___('examination.exam_assign') }}</a>
                        </li>
                        @endif

                        @if (hasPermission('marks_register_read'))
                        <li class="sidebar-menu-item {{ set_menu(['marks-register*']) }}">
                            <a href="{{ route('marks-register.index') }}">{{ ___('examination.marks_register') }}</a>
                        </li>
                        @endif

                        @if (hasPermission('exam_setting_read'))
                        <li class="sidebar-menu-item {{ set_menu(['examination-settings*']) }}">
                            <a href="{{ route('examination-settings.index') }}">{{ ___('settings.Settings') }}</a>
                        </li>
                        @endif

                    </ul>
                </li>
                @endif --}}
                {{-- End exam --}}

                <!-- Online Examination start -->
{{--                 @if ((hasPermission('question_group_read') ||--}}
{{--                hasPermission('question_bank_read') ||--}}
{{--                hasPermission('online_exam_read') ||--}}
{{--                hasPermission('online_exam_type_read')) && hasFeature('online_examination'))--}}
{{--                <li class="sidebar-menu-item {{ set_menu(['question-group*', 'question-bank*', 'online-exam*', 'online-exam-type*']) }}">--}}
{{--                    <a class="parent-item-content has-arrow">--}}
{{--                        <i class="las la-swatchbook"></i>--}}
{{--                        <span class="on-half-expanded">{{ ___('online-examination.online_examination') }}</span>--}}
{{--                    </a>--}}

{{--                    <!-- second layer child menu list start  -->--}}

{{--                    <ul class="child-menu-list">--}}
{{--                        <li class="sidebar-menu-item {{ set_menu(['online-exam-type*']) }}">--}}
{{--                            <a href="{{ route('online-exam-type.index') }}">{{ ___('settings.type') }}</a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-menu-item {{ set_menu(['question-group*']) }}">--}}
{{--                            <a href="{{ route('question-group.index') }}">{{ ___('online-examination.question_group') }}</a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-menu-item {{ set_menu(['question-bank*']) }}">--}}
{{--                            <a href="{{ route('question-bank.index') }}">{{ ___('online-examination.question_bank') }}</a>--}}
{{--                        </li>--}}
{{--                        <li class="sidebar-menu-item {{ set_menu(['online-exam','online-exam/create','online-exam/edit*']) }}">--}}
{{--                            <a href="{{ route('online-exam.index') }}">{{ ___('online-examination.online_exam') }}</a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}
{{--                </li>--}}
{{--                @endif--}}


                <!-- Online Examination end -->


                @if (hasPermission('notice_board_read') ||
                hasPermission('sms_mail_template_read') ||
                hasPermission('sms_mail_read'))

                <li class="sidebar-menu-item {{ set_menu(['communication/*']) }}">
                    <a class="parent-item-content has-arrow">
                        <i class="las la-envelope"></i>
                        <span class="on-half-expanded">{{ ___('common.communication') }}</span>
                    </a>
                    <ul class="child-menu-list">
                        @if (hasPermission('notice_board_read'))
                        <li class="sidebar-menu-item {{ set_menu(['communication/notice*']) }}">
                            <a href="{{ route('notice-board.index') }}">{{ ___('common.notice_board') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('sms_mail_read'))
                        <li class="sidebar-menu-item {{ set_menu(['communication/smsmail*']) }}">
                            <a href="{{ route('smsmail.index') }}">{{ ___('common.SMS/Mail') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('sms_mail_read'))
                        @php
                            $campaignRouteExists = false;
                            try {
                                route('smsmail.campaign');
                                $campaignRouteExists = true;
                            } catch (\Exception $e) {
                                $campaignRouteExists = false;
                            }
                        @endphp
                        @if($campaignRouteExists)
                        <li class="sidebar-menu-item {{ set_menu(['communication/smsmail/campaign*']) }}">
                            <a href="{{ route('smsmail.campaign') }}">SMS Campaign</a>
                        </li>
                        @endif
                        @endif
                        @if (hasPermission('sms_mail_template_read'))
                        <li class="sidebar-menu-item {{ set_menu(['communication/template*']) }}">
                            <a href="{{ route('template.index') }}">{{ ___('common.SMS/Mail_template') }}</a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif


                {{-- @if (hasPermission('gmeet_read'))

                <li class="sidebar-menu-item {{ set_menu(['liveclass/gmeet*']) }}">
                    <a class="parent-item-content has-arrow">
                        <i class="las la-swatchbook"></i>
                        <span class="on-half-expanded">{{ ___('common.live_class') }}</span>
                    </a>
                    <ul class="child-menu-list">
                        @if (hasPermission('gmeet_read'))
                        <li class="sidebar-menu-item {{ set_menu(['liveclass/gmeet*']) }}">
                            <a href="{{ route('gmeet.index') }}">{{ ___('common.gmeet') }}</a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif --}}

                <!-- Library start -->
                 {{-- @if ((hasPermission('book_category_read') ||
                hasPermission('book_read') ||
                hasPermission('member_read') ||
                hasPermission('issue_book_read')) && hasFeature('library'))
                <li class="sidebar-menu-item {{ set_menu(['book-category*']) }}">
                    <a class="parent-item-content has-arrow">
                        <i class="las la-swatchbook"></i>
                        <span class="on-half-expanded">{{ ___('settings.Library') }}</span>
                    </a>

                    <!-- second layer child menu list start  -->

                    <ul class="child-menu-list">
                        <li class="sidebar-menu-item {{ set_menu(['book-category*']) }}">
                            <a href="{{ route('book-category.index') }}">{{ ___('settings.book_category') }}</a>
                        </li>
                        <li class="sidebar-menu-item {{ set_menu(['book','book/create','book/edit*']) }}">
                            <a href="{{ route('book.index') }}">{{ ___('settings.Book') }}</a>
                        </li>
                        <li class="sidebar-menu-item {{ set_menu(['member-category*']) }}">
                            <a href="{{ route('member-category.index') }}">{{ ___('settings.member_category') }}</a>
                        </li>
                        <li class="sidebar-menu-item {{ set_menu(['member','member/create','member/edit*']) }}">
                            <a href="{{ route('member.index') }}">{{ ___('settings.Member') }}</a>
                        </li>
                        <li class="sidebar-menu-item {{ set_menu(['issue-book*']) }}">
                            <a href="{{ route('issue-book.index') }}">{{ ___('settings.issue_book') }}</a>
                        </li>
                    </ul>
                </li>
                @endif --}}
                <!-- Library end -->




               
 <!-- Admission start -->
                {{-- @if (hasPermission('admission_read') && hasFeature('online_admission'))
                <li class="sidebar-menu-item {{ set_menu(['online-admissions*']) }}">
                    <a class="parent-item-content has-arrow">
                        <i class="las la-users"></i>
                        <span class="on-half-expanded">{{ ___('settings.online_admission') }}</span>
                    </a>
                    <ul class="child-menu-list">
                        @if (hasPermission('certificate_read'))
                        <li class="sidebar-menu-item {{ set_menu(['online-admissions']) }}">
                            <a href="{{ route('online-admissions.index') }}">{{ ___('settings.online_admission') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('certificate_generate_read'))
                        <li class="sidebar-menu-item {{ set_menu(['online-admissions.setting.fees']) }}">
                            <a href="{{ route('online-admissions.setting.fees') }}">{{ 'Admission Fees' }}</a>
                        </li>
                        @endif
                        {{-- @if (hasPermission('certificate_generate_read'))
                        <li class="sidebar-menu-item {{ set_menu(['online-admissions.setting.index']) }}">
                            <a href="{{ route('online-admissions.setting.index') }}">{{ ___('common.Setting') }}</a>
                        </li>
                        @endif -- }}
                    </ul>
                </li>
                @endif --}}
                <!-- Admission end -->

                
                      <!-- Storekeeper start -->
               {{-- @if ((hasPermission('question_group_read') ||
               hasPermission('question_bank_read') ||
               hasPermission('online_exam_read') ||
               hasPermission('online_exam_type_read')) && hasFeature('online_examination'))
               <li class="sidebar-menu-item {{ set_menu(['storekeeper*', 'order*', 'online-exam*', 'online-exam-type*']) }}">
                   <a class="parent-item-content has-arrow">
                       <i class="las la-swatchbook"></i>
                       <span class="on-half-expanded">{{ "Place Order" }}</span>
                   </a>

                   <!-- second layer child menu list start  -->

                   <ul class="child-menu-list">
                       <li class="sidebar-menu-item {{ set_menu(['online-exam-type*']) }}">
                           <a href="{{ route('storekeeper.index') }}">{{ "Order Now" }}</a>
                       </li>
                       <li class="sidebar-menu-item {{ set_menu(['question-group*']) }}">
                           <a href="{{ route('order.index') }}">{{ "Order" }}</a>
                       </li>
                       <li class="sidebar-menu-item {{ set_menu(['question-bank*']) }}">
                           <a href="{{ route('storekeeper.index') }}">{{ "Report" }}</a>
                       </li>
                     
                   </ul>
               </li>
               @endif --}}


               <!-- Storekeeperend -->

                      <!-- Transportation start -->
               {{-- @if ((hasPermission('question_group_read') ||
               hasPermission('question_bank_read') ||
               hasPermission('online_exam_read') ||
               hasPermission('online_exam_type_read')) && hasFeature('online_examination'))
               <li class="sidebar-menu-item {{ set_menu(['tranporstation*', 'order*', 'online-exam*', 'vehicles*']) }}">
                   <a class="parent-item-content has-arrow">
                       <i class="las la-car"></i>
                       <span class="on-half-expanded">{{ "Transportation" }}</span>
                   </a>

                   <!-- second layer child menu list start  -->

                   <ul class="child-menu-list">
                       <li class="sidebar-menu-item {{ set_menu(['online-exam-type*']) }}">
                           <a href="{{ route('vehicles.index') }}">{{ "Vehicles" }}</a>
                       </li>
                       <li class="sidebar-menu-item {{ set_menu(['question-group*']) }}">
                           <a href="{{ route('order.index') }}">{{ "Invoices" }}</a>
                       </li>
                       <li class="sidebar-menu-item {{ set_menu(['question-bank*']) }}">
                           <a href="{{ route('storekeeper.index') }}">{{ "Report" }}</a>
                       </li>
                     
                   </ul>
               </li>
               @endif --}} 


               <!-- Storekeeperend -->


                {{-- @if (hasPermission('homework_read'))

               <li class="sidebar-menu-item {{ set_menu(['homework*']) }}">
                   <a class="parent-item-content has-arrow">
                       <i class="las la-swatchbook"></i>
                       <span class="on-half-expanded">{{ ___('common.homeworks') }}</span>
                   </a>
                   <ul class="child-menu-list">
                       @if (hasPermission('homework_read'))
                       <li class="sidebar-menu-item {{ set_menu(['homework*']) }}">
                           <a href="{{ route('homework.index') }}">{{ ___('common.Homeworks') }}</a>
                       </li>
                       @endif
                   </ul>
               </li>
               @endif --}}


                <!-- Language layout start -->
                {{-- @if ((hasPermission('language_read')) && hasFeature('language'))
                <li class="sidebar-menu-item {{ set_menu(['languages*']) }}">
                    <a href="{{ route('languages.index') }}" class="parent-item-content">
                        <i class="las la-language"></i>
                        <span class="on-half-expanded">{{ ___('common.language') }}</span>
                    </a>
                </li>
                @endif --}}
                <!-- Language layout end -->

                <!-- Homework layout end -->

                @if ((hasPermission('role_read') ||
                hasPermission('user_read') ||
                hasPermission('department_read') ||
                hasPermission('designation_read')) && hasFeature('staff_manage'))
                <li class="sidebar-menu-item {{ set_menu(['users*', 'roles*']) }}">
                    <a class="parent-item-content has-arrow">
                        <i class="las la-user-friends"></i>
                        <span class="on-half-expanded">{{ ___('settings.staff_manage') }}</span>
                    </a>

                    <!-- second layer child menu list start  -->

                    <ul class="child-menu-list">
                        @if (hasPermission('role_read'))
                        <li class="sidebar-menu-item {{ set_menu(['roles*']) }}">
                            <a href="{{ route('roles.index') }}">{{ ___('users_roles.roles') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('user_read'))
                        <li class="sidebar-menu-item {{ set_menu(['users*']) }}">
                            <a href="{{ route('users.index') }}">{{ ___('settings.staff') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('department_read'))
                        <li class="sidebar-menu-item {{ set_menu(['department', 'department/create', 'department/edit*']) }}">
                            <a href="{{ route('department.index') }}">{{ ___('staff.department') }}</a>
                        </li>
                        @endif
                            @if (hasPermission('department_read'))
                                <li class="sidebar-menu-item {{ set_menu(['salary', 'salary/create', 'salary/edit*']) }}">
                                    <a href="{{ route('salary.index') }}">{{ 'Batch Processing' }}</a>
                                </li>
                            @endif
                        @if (hasPermission('designation_read'))
                        <li class="sidebar-menu-item {{ set_menu(['designation*']) }}">
                            <a href="{{ route('designation.index') }}">{{ ___('staff.designation') }}</a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif
                <!-- Subscription layout start -->
                {{-- @if (hasPermission('subscribe_read'))
                <li class="sidebar-menu-item {{ set_menu(['subscribe*']) }}">
                    <a href="{{ route('subscribe.index') }}" class="parent-item-content">
                        <i class="las la-tags"></i>
                        <span class="on-half-expanded">{{ ___('settings.subscribers') }}</span>
                    </a>
                </li>
                @endif --}}
                <!-- Subscription layout end -->

                <!-- Subscription layout start -->

                {{-- @if (hasPermission('contact_message_read'))
                <li class="sidebar-menu-item {{ set_menu(['contact-message*']) }}">
                    <a href="{{ route('contact-message.index') }}" class="parent-item-content">
                        <i class="las la-sms"></i>
                        <span class="on-half-expanded">{{ ___('settings.contact_message') }}</span>
                    </a>
                </li>
                @endif --}}
                <!-- Subscription layout end -->


                <!-- Website setup start -->
                {{-- @if ((hasPermission('page_sections_read') ||
                hasPermission('slider_read') ||
                hasPermission('about_read') ||
                hasPermission('counter_read') ||
                hasPermission('contact_info_read') ||
                hasPermission('dep_contact_read') ||
                hasPermission('news_read') ||
                hasPermission('event_read')) && hasFeature('website_setup'))
                <li class="sidebar-menu-item {{ set_menu(['page-sections*','slider*','about*','counter*','contact-info*','department-contact*','admin-news*','event*']) }}">
                    <a class="parent-item-content has-arrow">
                        <i class="las la-shapes"></i>
                        <span class="on-half-expanded">{{ ___('settings.Website_setup') }}</span>
                    </a>

                    <!-- second layer child menu list start  -->

                    <ul class="child-menu-list">
                        @if (hasPermission('page_sections_read'))
                        <li class="sidebar-menu-item {{ set_menu(['page*']) }}">
                            <a href="{{ route('page.index') }}">{{ ___('settings.pages') }}</a>
                        </li>
                        @endif

                        @if (hasPermission('page_sections_read'))
                        <li class="sidebar-menu-item {{ set_menu(['page-sections*']) }}">
                            <a href="{{ route('sections.index') }}">{{ ___('settings.sections') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('slider_read'))
                        <li class="sidebar-menu-item {{ set_menu(['slider*']) }}">
                            <a href="{{ route('slider.index') }}">{{ ___('settings.Slider') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('about_read'))
                        <li class="sidebar-menu-item {{ set_menu(['about*']) }}">
                            <a href="{{ route('about.index') }}">{{ ___('settings.about') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('counter_read'))
                        <li class="sidebar-menu-item {{ set_menu(['counter*']) }}">
                            <a href="{{ route('counter.index') }}">{{ ___('settings.Counter') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('contact_info_read'))
                        <li class="sidebar-menu-item {{ set_menu(['contact-info*']) }}">
                            <a href="{{ route('contact-info.index') }}">{{ ___('settings.contact_information') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('dep_contact_read'))
                        <li class="sidebar-menu-item {{ set_menu(['department-contact*']) }}">
                            <a href="{{ route('department-contact.index') }}">{{ ___('settings.department_contact') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('news_read'))
                        <li class="sidebar-menu-item {{ set_menu(['admin-news*']) }}">
                            <a href="{{ route('news.index') }}">{{ ___('settings.News') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('event_read'))
                        <li class="sidebar-menu-item {{ set_menu(['event*']) }}">
                            <a href="{{ route('event.index') }}">{{ ___('settings.event') }}</a>
                        </li>
                        @endif

                    </ul>
                </li>
                @endif --}}
                <!-- Website setup end -->


                <!-- Gallery start -->
                {{-- @if ((hasPermission('gallery_category_read') ||
                hasPermission('gallery_read')) && hasFeature('gallery'))
                <li class="sidebar-menu-item {{ set_menu(['gallery-category*', 'gallery/*']) }}">
                    <a class="parent-item-content has-arrow">
                        <i class="las la-images"></i>
                        <span class="on-half-expanded">{{ ___('settings.Gallery') }}</span>
                    </a>

                    <!-- second layer child menu list start  -->

                    <ul class="child-menu-list">
                        @if (hasPermission('gallery_category_read'))
                        <li class="sidebar-menu-item {{ set_menu(['gallery-category*']) }}">
                            <a href="{{ route('gallery-category.index') }}">{{ ___('settings.Gallery_category') }}</a>
                        </li>
                        @endif
                        @if (hasPermission('gallery_read'))
                        <li class="sidebar-menu-item {{ set_menu(['gallery', 'gallery/*']) }}">
                            <a href="{{ route('gallery.index') }}">{{ ___('settings.Images') }}</a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif --}}
                <!-- Gallery end -->



                <!-- Settings layout start -->
                 @if ((hasPermission('general_settings_read') ||
                hasPermission('storage_settings_read') ||
                hasPermission('task_schedules_read') ||
                hasPermission('software_update_read') ||
                hasPermission('recaptcha_settings_read') ||
                hasPermission('email_settings_read') ||
                hasPermission('sms_settings_read') ||
                hasPermission('gender_read') ||
                hasPermission('religion_read') ||
                hasPermission('blood_group_read') ||
                hasPermission('session_read')) && hasFeature('setting'))

                <li class="sidebar-menu-item {{ set_menu(['setting*', 'genders*', 'religions*']) }}">
                    <a href="#" class="parent-item-content has-arrow">
                        <i class="las la-cog"></i>
                        <span class="on-half-expanded">{{ ___('common.settings') }}</span>
                    </a>

                    <!-- second layer child menu list start  -->
                    <ul class="child-menu-list">
                        @if (hasPermission('general_settings_read'))
                        <li class="sidebar-menu-item {{ set_menu(['settings.general-settings']) }}">
                            <a href="{{ route('settings.general-settings') }}">{{ ___('settings.general_settings') }}</a>
                        </li>
                        @endif

                        @if (!hasPermission('notification-settings_read'))
                        <li class="sidebar-menu-item {{ set_menu(['settings.notification-settings']) }}">
                            <a href="{{ route('settings.notification-settings') }}">{{ ___('settings.notification_setting') }}</a>
                        </li>
                        @endif

                        @if (hasPermission('storage_settings_read'))
                        <li class="sidebar-menu-item {{ set_menu(['settings.storagesetting']) }}">
                            <a href="{{ route('settings.storagesetting') }}">{{ ___('settings.storage_settings') }}</a>
                        </li>
                        @endif

                        @if (hasPermission('task_schedules_read'))
                        <li class="sidebar-menu-item {{ set_menu(['settings.task-schedulers']) }}">
                            <a href="{{ route('settings.task-schedulers') }}">{{ ___('settings.task_schedules') }}</a>
                        </li>
                        @endif

                        @if (hasPermission('software_update_read'))
                        <li class="sidebar-menu-item {{ set_menu(['settings.software-update']) }}">
                            <a href="{{ route('settings.software-update') }}">{{ ___('settings.software_update') }}</a>
                        </li>
                        @endif

                        @if (hasPermission('recaptcha_settings_read'))
                        <li class="sidebar-menu-item {{ set_menu(['settings.recaptcha-setting']) }}">
                            <a href="{{ route('settings.recaptcha-setting') }}">{{ ___('settings.recaptcha_settings') }}</a>
                        </li>
                        @endif

                        @if (hasPermission('sms_settings_read'))
                        <li class="sidebar-menu-item {{ set_menu(['settings.recaptcha-setting']) }}">
                            <a href="{{ route('settings.sms-setting') }}">{{ ___('settings.sms_settings') }}</a>
                        </li>
                        @endif

                        @if (hasPermission('payment_gateway_settings_read'))
                        <li class="sidebar-menu-item {{ set_menu(['settings.payment-gateway-setting']) }}">
                            <a href="{{ route('settings.payment-gateway-setting') }}">{{ ___('settings.payment_gateway_settings') }}</a>
                        </li>
                        @endif

                        @if (hasPermission('email_settings_read'))
                        <li class="sidebar-menu-item {{ set_menu(['settings.mail-setting']) }}">
                            <a href="{{ route('settings.mail-setting') }}">{{ ___('settings.email_settings') }}</a>
                        </li>
                        @endif

                        @if (hasPermission('email_settings_read'))
                        <li class="sidebar-menu-item {{ set_menu(['settings.notification-settings']) }}">
                            <a href="{{ route('settings.notification-settings') }}">{{ ___('settings.notification_setting') }}</a>
                        </li>
                        @endif

                        <!-- gender layout start -->
                        @if (hasPermission('gender_read'))
                        <li class="sidebar-menu-item {{ set_menu(['genders*']) }}">
                            <a href="{{ route('genders.index') }}">{{ ___('settings.genders') }}</a>
                        </li>
                        @endif
                        <!-- gender layout end -->


                            <!-- bank account layout start -->
                            @if (hasPermission('gender_read'))
                                <li class="sidebar-menu-item {{ set_menu(['banksAccounts*']) }}">
                                    <a href="{{ route('banksAccounts.index') }}">{{ 'Bank Accounts' }}</a>
                                </li>
                            @endif
                            <!-- bank account layout end -->

                        <!-- religion layout start -->
                        @if (hasPermission('religion_read'))
                        <li class="sidebar-menu-item {{ set_menu(['religions*']) }}">
                            <a href="{{ route('religions.index') }}">{{ ___('settings.religions') }}</a>
                        </li>
                        @endif
                        <!-- religion layout end -->

                        <!-- blood_group layout start -->
                        @if (hasPermission('blood_group_read'))
                        <li class="sidebar-menu-item {{ set_menu(['blood-groups*']) }}">
                            <a href="{{ route('blood-groups.index') }}">{{ ___('settings.blood_groups') }}</a>
                        </li>
                        @endif
                        <!-- blood_group layout end -->

                        <!-- session layout start -->
                        @if (hasPermission('session_read'))
                        <li class="sidebar-menu-item {{ set_menu(['sessions*']) }}">
                            <a href="{{ route('sessions.index') }}">{{ ___('settings.sessions') }}</a>
                        </li>
                        @endif
                        <!-- session layout end -->
                    </ul>
                    <!-- second layer child menu list end  -->
                </li>
                @endif
                <!-- Settings layout end -->



                <!-- Components Layout End -->
                {{-- <li class="sidebar-menu-item {{ set_menu(['sessions*']) }}">
                <a href="{{ route('manual') }}" target="_blank" rel="noopener noreferrer">{{ 'User Manual' }}</a>
                </li> --}}

                {{-- <li class="sidebar-menu-item {{ set_menu(['sessions*']) }}">
                <a href="/chat"  rel="noopener noreferrer">{{ 'Ask Here' }}</a>
                </li> --}}
            </ul>
            <!-- parent menu list end  -->


        </div>


    </div>
</aside>
