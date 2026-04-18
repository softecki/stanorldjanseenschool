<?php

namespace App\Http\Controllers;

use App\Http\Requests\Accounts\Expense\ExpenseStoreRequest;
use App\Http\Requests\Accounts\Expense\ExpenseUpdateRequest;
use App\Http\Requests\StoreTransactionsRequest;
use App\Http\Requests\UpdateTransactionsRequest;
use App\Models\Transactions;
use App\Repositories\Accounts\AccountHeadRepository;
use App\Repositories\Accounts\ExpenseRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class TransactionsController extends Controller
{
    private $expenseRepo, $accountHeadRepository;

    function __construct(ExpenseRepository $expenseRepo, AccountHeadRepository $accountHeadRepository)
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        }
        $this->expenseRepo                 = $expenseRepo;
        $this->accountHeadRepository       = $accountHeadRepository;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['expense'] = $this->expenseRepo->getAll();
        $data['title'] = 'Transactions';
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['expense'], 'meta' => ['title' => $data['title']]]);
        }

        return view('backend.accounts.transactions.index', compact('data'));
    }

    public function create(Request $request): JsonResponse|View
    {
        $data['title'] = ___('account.create_expense');
        $data['heads'] = $this->accountHeadRepository->getExpenseHeads();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return view('backend.accounts.transactions.create', compact('data'));
    }

    public function store(ExpenseStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->expenseRepo->store($request);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('transactions.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|View
    {
        $data['heads'] = $this->accountHeadRepository->getExpenseHeads();
        $data['expense'] = $this->expenseRepo->show($id);
        $data['title'] = ___('account.edit_expense');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['expense'], 'meta' => $data]);
        }

        return view('backend.accounts.transactions.edit', compact('data'));
    }

    public function update(ExpenseUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->expenseRepo->update($request, $id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('transactions.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function delete($id)
    {
        $result = $this->expenseRepo->destroy($id);
        if($result['status']):
            $success[0] = $result['message'];
            $success[1] = 'success';
            $success[2] = ___('alert.deleted');
            $success[3] = ___('alert.OK');
            return response()->json($success);
        else:
            $success[0] = $result['message'];
            $success[1] = 'error';
            $success[2] = ___('alert.oops');
            return response()->json($success);
        endif;
    }
}
