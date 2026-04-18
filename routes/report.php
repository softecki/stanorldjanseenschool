<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Report\AccountController;
use App\Http\Controllers\Report\DueFeesController;
use App\Http\Controllers\Report\MarksheetController;
use App\Http\Controllers\Report\MeritListController;
use App\Http\Controllers\Report\ExamRoutineController;
use App\Http\Controllers\Report\ClassRoutineController;
use App\Http\Controllers\Report\ProgressCardController;
use App\Http\Controllers\Report\ProgressListController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\Report\FeesCollectionController;
use App\Http\Controllers\Report\DuplicateStudentsController;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;


Route::middleware(saasMiddleware())->group(function () {
    Route::group(['middleware' => ['XssSanitizer']], function () {
        Route::group(['middleware' => ['lang', 'CheckSubscription', 'FeatureCheck:report']], function () {
            Route::group(['middleware' => ['auth.routes', 'AdminPanel']], function () {

                Route::controller(MarksheetController::class)->prefix('report-marksheet')->group(function () {
                    Route::get('/', 'index')->name('report-marksheet.index')->middleware('PermissionCheck:report_marksheet_read');
                    Route::post('/search', 'search')->name('marksheet.search')->middleware('PermissionCheck:report_marksheet_read');
                    Route::get('/get-students', 'getStudents');
                    Route::get('/pdf-generate/{id}/{type}/{class}/{section}', 'generatePDF')->name('report-marksheet.pdf-generate');
                });

                Route::controller(MeritListController::class)->prefix('report-merit-list')->group(function () {
                    Route::get('/', 'index')->name('report-merit-list.index')->middleware('PermissionCheck:report_merit_list_read');
                    Route::any('/search', 'search')->name('merit-list.search')->middleware('PermissionCheck:report_merit_list_read');
                    Route::get('/pdf-generate/{type}/{class}/{section}', 'generatePDF')->name('report-merit-list.pdf-generate');
                });

                Route::controller(ProgressCardController::class)->prefix('report-progress-card')->group(function () {
                    Route::get('/', 'index')->name('report-progress-card.index')->middleware('PermissionCheck:report_progress_card_read');
                    Route::post('/search', 'search')->name('report-progress-card.search');
                    Route::get('/get-students', 'getStudents');
                    Route::get('/pdf-generate/{class}/{section}/{student}', 'generatePDF')->name('report-progress-card.pdf-generate');
                });

                Route::controller(DueFeesController::class)->prefix('report-due-fees')->group(function () {
                    Route::get('/', 'index')->name('report-due-fees.index')->middleware('PermissionCheck:report_due_fees_read');
                    Route::any('/search', 'search')->name('due-fees.search')->middleware('PermissionCheck:report_due_fees_read');
                    Route::post('/pdf-generate', 'generatePDF')->name('report-due-fees.pdf-generate');
                });

                Route::controller(FeesCollectionController::class)->prefix('report-fees-collection')->group(function () {
                    Route::get('/', 'index')->name('report-fees-collection.index')->middleware('PermissionCheck:report_fees_collection_read');
                    Route::any('/search', 'search')->name('fees-collection.search')->middleware('PermissionCheck:report_fees_collection_read');
                    Route::get('/pdf-generate/{class}/{section}/{dates}', 'generatePDF')->name('report-fees-collection.pdf-generate');
                    Route::get('/excel-generate/{class}/{section}/{dates}', 'generateExcel')->name('report-fees-collection.excel-generate');
                });


                Route::controller(FeesCollectionController::class)->prefix('report-fees-summary')->group(function () {
                    Route::get('/', 'feeSummary')->name('report-fees-summary.index')->middleware('PermissionCheck:report_fees_collection_read');
                    Route::any('/search', 'searchFeeSummary')->name('report-fees-summary.search')->middleware('PermissionCheck:report_fees_collection_read');
                    Route::get('/pdf-generate/{class?}/{section?}/{dates?}', 'generatePDF')->name('report-fees-summary.pdf-generate');
                    Route::get('/excel-generate/{class?}/{section?}/{dates?}/{fee_group_id?}/{amount?}/{year?}', 'generateSummaryExcel')->name('report-fees-summary.excel-generate');
                });


                Route::controller(FeesCollectionController::class)->prefix('report-students')->group(function () {
                    Route::get('/', 'students')->name('report-students.index')->middleware('PermissionCheck:report_fees_collection_read');
                    Route::any('/search', 'searchStudents')->name('report-students.search')->middleware('PermissionCheck:report_fees_collection_read');
                    Route::get('/pdf-generate/{class}', 'generatePDF')->name('report-students.pdf-generate');
                    Route::get('/excel-generate/{class}', 'generateStudentsExcel')->name('report-students.excel-generate');
                });

                Route::controller(FeesCollectionController::class)->prefix('report-fees-by-year')->group(function () {
                    Route::get('/', 'feesByYear')->name('report-fees-by-year.index')->middleware('PermissionCheck:report_fees_collection_read');
                    Route::any('/search', 'searchFeesByYear')->name('report-fees-by-year.search')->middleware('PermissionCheck:report_fees_collection_read');
                    Route::get('/detail/{studentId}', 'feesByYearDetail')->name('report-fees-by-year.detail')->middleware('PermissionCheck:report_fees_collection_read');
                    Route::post('/recalculate/{studentId}/{year}', 'recalculateBalances')->name('report-fees-by-year.recalculate')->middleware('PermissionCheck:report_fees_collection_read');
                    Route::post('/bulk-recalculate', 'bulkRecalculateBalances')->name('report-fees-by-year.bulk-recalculate')->middleware('PermissionCheck:report_fees_collection_read');
                    Route::post('/generate-outstanding-balance-2026', 'generateOutstandingBalance2026')->name('report-fees-by-year.generate-outstanding-balance-2026')->middleware('PermissionCheck:report_fees_collection_read');
                    Route::post('/generate-outstanding-balance-2026/{studentId}', 'generateOutstandingBalance2026ForStudent')->name('report-fees-by-year.generate-outstanding-balance-2026-student')->middleware('PermissionCheck:report_fees_collection_read');
                });

                Route::controller(FeesCollectionController::class)->prefix('report-boarding-students')->group(function () {
                    Route::get('/', 'boardingStudents')->name('report-boarding-students.index')->middleware('PermissionCheck:report_fees_collection_read');
                    Route::any('/search', 'searchBoardingStudents')->name('report-boarding-students.search')->middleware('PermissionCheck:report_fees_collection_read');
                    Route::post('/update-fees-2026', 'updateBoardingSchoolFees2026')->name('report-boarding-students.update-fees-2026')->middleware('PermissionCheck:report_fees_collection_read');
                    Route::get('/find-missing-2026', 'findMissingBoardingStudents2026')->name('report-boarding-students.find-missing-2026')->middleware('PermissionCheck:report_fees_collection_read');
                    Route::post('/create-missing-fees-2026', 'createMissingBoardingSchoolFees2026')->name('report-boarding-students.create-missing-fees-2026')->middleware('PermissionCheck:report_fees_collection_read');
                });

                Route::controller(AccountController::class)->prefix('report-account')->group(function () {
                    Route::get('/', 'index')->name('report-account.index')->middleware('PermissionCheck:report_account_read');
                    Route::any('/search', 'search')->name('account.search')->middleware('PermissionCheck:report_account_read');
                    Route::get('/get-account-types', 'getAccountTypes');
                    Route::post('/pdf-generate', 'generatePDF')->name('report-account.pdf-generate');
                });

                Route::controller(AttendanceController::class)->prefix('report-attendance')->group(function () {
                    Route::get('/report', 'report')->name('report-attendance.report')->middleware('PermissionCheck:report_attendance_read');
                    Route::any('/report-search', 'reportSearch')->name('report-attendance.report-search')->middleware('PermissionCheck:report_attendance_read');
                    Route::post('/pdf-generate', 'generatePDF')->name('report-attendance.pdf-generate');
                });

                Route::controller(ClassRoutineController::class)->prefix('report-class-routine')->group(function () {
                    Route::get('/', 'index')->name('report-class-routine.index')->middleware('PermissionCheck:report_class_routine_read');
                    Route::post('/search', 'search')->name('report-class-routine.search')->middleware('PermissionCheck:report_class_routine_read');
                    Route::get('/pdf-generate/{class}/{section}', 'generatePDF')->name('report-class-routine.pdf-generate');
                });

                Route::controller(ExamRoutineController::class)->prefix('report-exam-routine')->group(function () {
                    Route::get('/', 'index')->name('report-exam-routine.index')->middleware('PermissionCheck:report_exam_routine_read');
                    Route::post('/search', 'search')->name('report-exam-routine.search')->middleware('PermissionCheck:report_exam_routine_read');
                    Route::get('/pdf-generate/{class}/{section}/{type}', 'generatePDF')->name('report-exam-routine.pdf-generate');
                });

                Route::controller(DuplicateStudentsController::class)->prefix('report-duplicate-students')->group(function () {
                    Route::get('/', 'index')->name('report-duplicate-students.index')->middleware('PermissionCheck:report_fees_collection_read');
                    Route::post('/search', 'search')->name('report-duplicate-students.search')->middleware('PermissionCheck:report_fees_collection_read');
                });

            });
        });
    });
});


