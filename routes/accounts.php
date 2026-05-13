<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Accounts\IncomeController;
use App\Http\Controllers\Accounts\ExpenseController;
use App\Http\Controllers\Accounts\AccountHeadController;
use App\Http\Controllers\Accounts\ChartOfAccountsController;
use App\Http\Controllers\Accounts\PaymentMethodController;
use App\Http\Controllers\Accounts\FinancialDashboardController;
use App\Http\Controllers\Accounts\BankReconciliationController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;


Route::middleware(saasMiddleware())->group(function () {
    Route::group(['middleware' => ['XssSanitizer']], function () {
        Route::group(['middleware' => ['lang', 'CheckSubscription', 'FeatureCheck:account']], function () {
            // auth routes
            Route::group(['middleware' => ['auth.routes', 'AdminPanel']], function () {
                // Accounting dashboard (chart of accounts, payment methods, reports)
                Route::controller(FinancialDashboardController::class)->prefix('accounting')->group(function () {
                    Route::get('/dashboard', 'dashboard')->name('accounting.dashboard')->middleware('PermissionCheck:income_read');
                    Route::get('/cashbook', 'cashbook')->name('accounting.cashbook')->middleware('PermissionCheck:income_read');
                    Route::get('/reports/income', 'incomeReport')->name('accounting.reports.income')->middleware('PermissionCheck:income_read');
                    Route::get('/reports/expense', 'expenseReport')->name('accounting.reports.expense')->middleware('PermissionCheck:expense_read');
                    Route::get('/reports/profit-loss', 'profitLossReport')->name('accounting.reports.profit-loss')->middleware('PermissionCheck:income_read');
                    Route::get('/audit-log', 'auditLog')->name('accounting.audit-log')->middleware('PermissionCheck:income_read');
                });

                Route::controller(BankReconciliationController::class)->prefix('accounting/bank-reconciliation')->group(function () {
                    Route::get('/', 'index')->name('accounting.bank-reconciliation.index')->middleware('PermissionCheck:income_read');
                    Route::post('/upload', 'upload')->name('accounting.bank-reconciliation.upload')->middleware('PermissionCheck:income_create', 'DemoCheck');
                    Route::get('/process', 'process')->name('accounting.bank-reconciliation.process')->middleware('PermissionCheck:income_read');
                    Route::get('/generate-pdf', 'generatePdf')->name('accounting.bank-reconciliation.pdf')->middleware('PermissionCheck:income_read');
                    Route::get('/generate-excel', 'generateExcel')->name('accounting.bank-reconciliation.excel')->middleware('PermissionCheck:income_read');
                    Route::get('/reset', 'reset')->name('accounting.bank-reconciliation.reset')->middleware('PermissionCheck:income_create', 'DemoCheck');
                });

                Route::controller(ChartOfAccountsController::class)->prefix('chart-of-accounts')->group(function () {
                    Route::get('/', 'index')->name('chart-of-accounts.index')->middleware('PermissionCheck:account_head_read');
                    Route::get('/show/{id}', 'show')->name('chart-of-accounts.show')->middleware('PermissionCheck:account_head_read');
                    Route::get('/create', 'create')->name('chart-of-accounts.create')->middleware('PermissionCheck:account_head_create');
                    Route::post('/store', 'store')->name('chart-of-accounts.store')->middleware('PermissionCheck:account_head_create', 'DemoCheck');
                    Route::get('/edit/{id}', 'edit')->name('chart-of-accounts.edit')->middleware('PermissionCheck:account_head_update');
                    Route::put('/update/{id}', 'update')->name('chart-of-accounts.update')->middleware('PermissionCheck:account_head_update', 'DemoCheck');
                    Route::delete('/delete/{id}', 'delete')->name('chart-of-accounts.delete')->middleware('PermissionCheck:account_head_delete', 'DemoCheck');
                });

                Route::controller(PaymentMethodController::class)->prefix('payment-methods')->group(function () {
                    Route::get('/', 'index')->name('payment-methods.index')->middleware('PermissionCheck:account_head_read');
                    Route::get('/show/{id}', 'show')->name('payment-methods.show')->middleware('PermissionCheck:account_head_read');
                    Route::get('/create', 'create')->name('payment-methods.create')->middleware('PermissionCheck:account_head_create');
                    Route::post('/store', 'store')->name('payment-methods.store')->middleware('PermissionCheck:account_head_create', 'DemoCheck');
                    Route::get('/edit/{id}', 'edit')->name('payment-methods.edit')->middleware('PermissionCheck:account_head_update');
                    Route::put('/update/{id}', 'update')->name('payment-methods.update')->middleware('PermissionCheck:account_head_update', 'DemoCheck');
                    Route::delete('/delete/{id}', 'delete')->name('payment-methods.delete')->middleware('PermissionCheck:account_head_delete', 'DemoCheck');
                });

                Route::controller(AccountHeadController::class)->prefix('account-head')->group(function () {
                    Route::get('/',                 'index')->name('account_head.index')->middleware('PermissionCheck:account_head_read');
                    Route::get('/show/{id}',        'show')->name('account_head.show')->middleware('PermissionCheck:account_head_read');
                    Route::get('/create',           'create')->name('account_head.create')->middleware('PermissionCheck:account_head_create');
                    Route::post('/store',           'store')->name('account_head.store')->middleware('PermissionCheck:account_head_create', 'DemoCheck');
                    Route::get('/edit/{id}',        'edit')->name('account_head.edit')->middleware('PermissionCheck:account_head_update');
                    Route::put('/update/{id}',      'update')->name('account_head.update')->middleware('PermissionCheck:account_head_update', 'DemoCheck');
                    Route::delete('/delete/{id}',   'delete')->name('account_head.delete')->middleware('PermissionCheck:account_head_delete', 'DemoCheck');
                });

                Route::controller(IncomeController::class)->prefix('income')->group(function () {
                    Route::get('/',                 'index')->name('income.index')->middleware('PermissionCheck:income_read');
                    Route::get('/create',           'create')->name('income.create')->middleware('PermissionCheck:income_create');
                    Route::post('/store',           'store')->name('income.store')->middleware('PermissionCheck:income_create', 'DemoCheck');
                    Route::get('/edit/{id}',        'edit')->name('income.edit')->middleware('PermissionCheck:income_update');
                    Route::put('/update/{id}',      'update')->name('income.update')->middleware('PermissionCheck:income_update', 'DemoCheck');
                    Route::delete('/delete/{id}',   'delete')->name('income.delete')->middleware('PermissionCheck:income_delete', 'DemoCheck');
                });

                Route::controller(ExpenseController::class)->prefix('expense')->group(function () {
                    Route::get('/',                 'index')->name('expense.index')->middleware('PermissionCheck:expense_read');
                    Route::get('/create',           'create')->name('expense.create')->middleware('PermissionCheck:expense_create');
                    Route::post('/store',           'store')->name('expense.store')->middleware('PermissionCheck:expense_create', 'DemoCheck');
                    Route::get('/edit/{id}',        'edit')->name('expense.edit')->middleware('PermissionCheck:expense_update');
                    Route::put('/update/{id}',      'update')->name('expense.update')->middleware('PermissionCheck:expense_update', 'DemoCheck');
                    Route::delete('/delete/{id}',   'delete')->name('expense.delete')->middleware('PermissionCheck:expense_delete', 'DemoCheck');
                });

                Route::controller(ExpenseController::class)->prefix('cash')->group(function () {
                    Route::get('/cash',                 'index_cash')->name('cash.index')->middleware('PermissionCheck:expense_read');
                    Route::get('/create',           'create')->name('cash.create')->middleware('PermissionCheck:expense_create');
                    Route::post('/store',           'store')->name('cash.store')->middleware('PermissionCheck:expense_create', 'DemoCheck');
                    Route::get('/edit/{id}',        'edit')->name('cash.edit')->middleware('PermissionCheck:expense_update');
                    Route::put('/update/{id}',      'update')->name('cash.update')->middleware('PermissionCheck:expense_update', 'DemoCheck');
                    Route::delete('/delete/{id}',   'delete')->name('cash.delete')->middleware('PermissionCheck:expense_delete', 'DemoCheck');
                });

                Route::controller(ExpenseController::class)->prefix('product')->group(function () {
                    Route::get('/cash',                 'index_product')->name('product.index')->middleware('PermissionCheck:expense_read');
                    Route::get('/create',           'create_product')->name('product.create')->middleware('PermissionCheck:expense_create');
                    Route::get('/sell',           'create_sell')->name('product.sell')->middleware('PermissionCheck:expense_create');
                    Route::post('/store',           'store_product')->name('product.store')->middleware('PermissionCheck:expense_create', 'DemoCheck');
                    Route::post('/sellout',           'store_sell')->name('product.sellout')->middleware('PermissionCheck:expense_create', 'DemoCheck');
                    Route::get('/edit/{id}',        'edit_product')->name('product.edit')->middleware('PermissionCheck:expense_update');
                    Route::put('/update/{id}',      'update_product')->name('product.update')->middleware('PermissionCheck:expense_update', 'DemoCheck');
                    Route::delete('/delete/{id}',   'delete_product')->name('product.delete')->middleware('PermissionCheck:expense_delete', 'DemoCheck');
                });

                   Route::controller(ExpenseController::class)->prefix('item')->group(function () {
                    Route::get('/cash',                 'index_item')->name('item.index')->middleware('PermissionCheck:expense_read');
                    Route::get('/create',           'create_item')->name('item.create')->middleware('PermissionCheck:expense_create');
                    Route::post('/store',           'store_item')->name('item.store')->middleware('PermissionCheck:expense_create', 'DemoCheck');
                    Route::get('/edit/{id}',        'edit_item')->name('item.edit')->middleware('PermissionCheck:expense_update');
                    Route::put('/update/{id}',      'update_item')->name('item.update')->middleware('PermissionCheck:expense_update', 'DemoCheck');
                    Route::delete('/delete/{id}',   'delete_item')->name('item.delete')->middleware('PermissionCheck:expense_delete', 'DemoCheck');
                });

                Route::controller(\App\Http\Controllers\DepositsController::class)->prefix('deposit')->group(function () {
                    Route::get('/',                 'index')->name('deposit.index')->middleware('PermissionCheck:expense_read');
                    Route::get('/create',           'create')->name('deposit.create')->middleware('PermissionCheck:expense_create');
                    Route::post('/store',           'store')->name('deposit.store')->middleware('PermissionCheck:expense_create', 'DemoCheck');
                    Route::get('/edit/{id}',        'edit')->name('deposit.edit')->middleware('PermissionCheck:expense_update');
                    Route::put('/update/{id}',      'update')->name('deposit.update')->middleware('PermissionCheck:expense_update', 'DemoCheck');
                    Route::delete('/delete/{id}',   'delete')->name('deposit.delete')->middleware('PermissionCheck:expense_delete', 'DemoCheck');
                });

                Route::controller(\App\Http\Controllers\PaymentsController::class)->prefix('payments')->group(function () {
                    Route::get('/',                 'index')->name('payments.index')->middleware('PermissionCheck:expense_read');
                    Route::get('/create',           'create')->name('payments.create')->middleware('PermissionCheck:expense_create');
                    Route::post('/store',           'store')->name('payments.store')->middleware('PermissionCheck:expense_create', 'DemoCheck');
                    Route::get('/edit/{id}',        'edit')->name('payments.edit')->middleware('PermissionCheck:expense_update');
                    Route::put('/update/{id}',      'update')->name('payments.update')->middleware('PermissionCheck:expense_update', 'DemoCheck');
                    Route::delete('/delete/{id}',   'delete')->name('payments.delete')->middleware('PermissionCheck:expense_delete', 'DemoCheck');
                });

                Route::controller(\App\Http\Controllers\TransactionsController::class)->prefix('transactions')->group(function () {
                    Route::get('/',                 'index')->name('transactions.index')->middleware('PermissionCheck:expense_read');
                    Route::get('/create',           'create')->name('transactions.create')->middleware('PermissionCheck:expense_create');
                    Route::post('/store',           'store')->name('transactions.store')->middleware('PermissionCheck:expense_create', 'DemoCheck');
                    Route::get('/edit/{id}',        'edit')->name('transactions.edit')->middleware('PermissionCheck:expense_update');
                    Route::put('/update/{id}',      'update')->name('transactions.update')->middleware('PermissionCheck:expense_update', 'DemoCheck');
                    Route::delete('/delete/{id}',   'delete')->name('transactions.delete')->middleware('PermissionCheck:expense_delete', 'DemoCheck');
                });

                Route::controller(\App\Http\Controllers\SuppliersController::class)->prefix('suppliers')->group(function () {
                    Route::get('/',                 'index')->name('suppliers.index')->middleware('PermissionCheck:expense_read');
                    Route::get('/create',           'create')->name('suppliers.create')->middleware('PermissionCheck:expense_create');
                    Route::post('/store',           'store')->name('suppliers.store')->middleware('PermissionCheck:expense_create', 'DemoCheck');
                    Route::get('/edit/{id}',        'edit')->name('suppliers.edit')->middleware('PermissionCheck:expense_update');
                    Route::put('/update/{id}',      'update')->name('suppliers.update')->middleware('PermissionCheck:expense_update', 'DemoCheck');
                    Route::delete('/delete/{id}',   'delete')->name('suppliers.delete')->middleware('PermissionCheck:expense_delete', 'DemoCheck');
                });

                Route::controller(\App\Http\Controllers\InvoicesController::class)->prefix('invoices')->group(function () {
                    Route::get('/',                 'index')->name('invoices.index')->middleware('PermissionCheck:expense_read');
                    Route::get('/create',           'create')->name('invoices.create')->middleware('PermissionCheck:expense_create');
                    Route::post('/store',           'store')->name('invoices.store')->middleware('PermissionCheck:expense_create', 'DemoCheck');
                    Route::get('/edit/{id}',        'edit')->name('invoices.edit')->middleware('PermissionCheck:expense_update');
                    Route::put('/update/{id}',      'update')->name('invoices.update')->middleware('PermissionCheck:expense_update', 'DemoCheck');
                    Route::delete('/delete/{id}',   'delete')->name('invoices.delete')->middleware('PermissionCheck:expense_delete', 'DemoCheck');
                });
            });
        });
    });
});

