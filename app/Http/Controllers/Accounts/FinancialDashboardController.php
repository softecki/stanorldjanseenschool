<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Accounts\Income;
use App\Models\Accounts\Expense;
use App\Models\Accounts\AccountAuditLog;
use App\Models\BankAccounts;
use App\Models\Fees\FeesCollect;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class FinancialDashboardController extends Controller
{
    public function dashboard(Request $request): JsonResponse|View
    {
        $sessionId = setting('session');
        $today = Carbon::today()->toDateString();

        $totalIncome = (float) Income::where('session_id', $sessionId)->sum('amount');
        $totalExpense = (float) Expense::where('session_id', $sessionId)->sum('amount');
        $feesCollected = (float) FeesCollect::where('session_id', $sessionId)->sum('amount');
        $todayIncome = (float) Income::where('session_id', $sessionId)->whereDate('date', $today)->sum('amount');
        $todayExpense = (float) Expense::where('session_id', $sessionId)->whereDate('date', $today)->sum('amount');

        $bankAccounts = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('bank_accounts')) {
            $bankAccounts = BankAccounts::orderBy('bank_name')->get();
        }

        $recentIncomes = Income::where('session_id', $sessionId)->with('head')->latest('date')->take(5)->get();
        $recentExpenses = Expense::where('session_id', $sessionId)->with('head')->latest('date')->take(5)->get();

        $data = [
            'title' => __('Financial Dashboard'),
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'fees_collected' => $feesCollected,
            'today_income' => $todayIncome,
            'today_expense' => $todayExpense,
            'balance' => $totalIncome - $totalExpense,
            'bank_accounts' => $bankAccounts,
            'recent_incomes' => $recentIncomes,
            'recent_expenses' => $recentExpenses,
        ];
        if ($request->expectsJson()) {
            $bankAccountsPayload = collect($bankAccounts)->map(function ($b) {
                return [
                    'id' => $b->id,
                    'bank_name' => $b->bank_name,
                    'account_name' => $b->account_name ?? null,
                    'account_number' => $b->account_number,
                    'balance' => $b->balance,
                ];
            })->values()->all();

            $recentIncomesPayload = $recentIncomes->map(function ($row) {
                return [
                    'id' => $row->id,
                    'date' => $row->date,
                    'name' => $row->name,
                    'amount' => $row->amount,
                    'head_name' => optional($row->head)->name,
                ];
            })->values()->all();

            $recentExpensesPayload = $recentExpenses->map(function ($row) {
                return [
                    'id' => $row->id,
                    'date' => $row->date,
                    'name' => $row->name,
                    'amount' => $row->amount,
                    'head_name' => optional($row->head)->name,
                ];
            })->values()->all();

            return response()->json([
                'meta' => [
                    'title' => $data['title'],
                    'total_income' => $data['total_income'],
                    'total_expense' => $data['total_expense'],
                    'fees_collected' => $data['fees_collected'],
                    'today_income' => $data['today_income'],
                    'today_expense' => $data['today_expense'],
                    'balance' => $data['balance'],
                ],
                'data' => [
                    'bank_accounts' => $bankAccountsPayload,
                    'recent_incomes' => $recentIncomesPayload,
                    'recent_expenses' => $recentExpensesPayload,
                ],
            ]);
        }
        return view('backend.accounts.dashboard.index', compact('data'));
    }

    public function cashbook(Request $request): JsonResponse|RedirectResponse
    {
        $sessionId = setting('session');
        $date = $request->get('date', Carbon::today()->toDateString());

        $incomes = Income::where('session_id', $sessionId)->whereDate('date', $date)->orderBy('date')->get();
        $expenses = Expense::where('session_id', $sessionId)->whereDate('date', $date)->orderBy('date')->get();
        $feePayments = FeesCollect::where('session_id', $sessionId)->whereDate('date', $date)->orderBy('date')->get();

        $totalIn = $incomes->sum('amount') + $feePayments->sum('amount');
        $totalOut = $expenses->sum('amount');

        $data = [
            'title' => __('Daily Cashbook'),
            'date' => $date,
            'incomes' => $incomes,
            'expenses' => $expenses,
            'fee_payments' => $feePayments,
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'balance' => $totalIn - $totalOut,
        ];
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return redirect()->to(url('/app/reports/accounting/cashbook'));
    }

    public function incomeReport(Request $request): JsonResponse|RedirectResponse
    {
        $sessionId = setting('session');
        $from = $request->get('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->get('to', Carbon::now()->toDateString());

        $query = Income::where('session_id', $sessionId)->whereBetween('date', [$from, $to]);
        $items = $query->with('head')->orderBy('date')->get();
        $byHead = $items->groupBy('income_head')->map(fn ($g) => $g->sum('amount'));
        $feesInPeriod = FeesCollect::where('session_id', $sessionId)->whereBetween('date', [$from, $to])->sum('amount');

        $data = [
            'title' => __('Income Report'),
            'from' => $from,
            'to' => $to,
            'items' => $items,
            'by_head' => $byHead,
            'total' => $items->sum('amount') + $feesInPeriod,
            'fees_in_period' => $feesInPeriod,
        ];
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['items'], 'meta' => $data]);
        }
        return redirect()->to(url('/app/reports/accounting/income'));
    }

    public function expenseReport(Request $request): JsonResponse|RedirectResponse
    {
        $sessionId = setting('session');
        $from = $request->get('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->get('to', Carbon::now()->toDateString());

        $items = Expense::where('session_id', $sessionId)->whereBetween('date', [$from, $to])->with('head')->orderBy('date')->get();
        $byHead = $items->groupBy('expense_head')->map(fn ($g) => $g->sum('amount'));

        $data = [
            'title' => __('Expense Report'),
            'from' => $from,
            'to' => $to,
            'items' => $items,
            'by_head' => $byHead,
            'total' => $items->sum('amount'),
        ];
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['items'], 'meta' => $data]);
        }
        return redirect()->to(url('/app/reports/accounting/expense'));
    }

    public function profitLossReport(Request $request): JsonResponse|RedirectResponse
    {
        $sessionId = setting('session');
        $from = $request->get('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->get('to', Carbon::now()->toDateString());

        $incomeTotal = Income::where('session_id', $sessionId)->whereBetween('date', [$from, $to])->sum('amount');
        $feesTotal = FeesCollect::where('session_id', $sessionId)->whereBetween('date', [$from, $to])->sum('amount');
        $expenseTotal = Expense::where('session_id', $sessionId)->whereBetween('date', [$from, $to])->sum('amount');

        $data = [
            'title' => __('Profit & Loss'),
            'from' => $from,
            'to' => $to,
            'total_income' => $incomeTotal + $feesTotal,
            'total_expense' => $expenseTotal,
            'profit_loss' => ($incomeTotal + $feesTotal) - $expenseTotal,
        ];
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return redirect()->to(url('/app/reports/accounting/profit-loss'));
    }

    public function auditLog(Request $request): JsonResponse|RedirectResponse
    {
        $logs = Schema::hasTable('accounting_audit_logs')
            ? AccountAuditLog::with('user')->orderByDesc('created_at')->paginate(50)
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50);
        $data = ['title' => __('Audit Log'), 'logs' => $logs];
        if ($request->expectsJson()) {
            return response()->json(['data' => $logs, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(url('/app/reports/accounting/audit-log'));
    }
}
