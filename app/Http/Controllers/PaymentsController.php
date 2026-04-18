<?php

namespace App\Http\Controllers;

use App\Http\Requests\Accounts\Expense\ExpenseStoreRequest;
use App\Http\Requests\Accounts\Expense\ExpenseUpdateRequest;
use App\Http\Requests\StorePaymentsRequest;
use App\Http\Requests\UpdatePaymentsRequest;
use App\Models\Payments;
use App\Repositories\Accounts\AccountHeadRepository;
use App\Repositories\Accounts\ExpenseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PaymentsController extends Controller
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

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['expense'] = $this->expenseRepo->getAll();
        $data['title'] = ___('account.expense');
        if ($request->expectsJson()) return response()->json(['data' => $data['expense'], 'meta' => ['title' => $data['title']]]);
        return redirect()->to(url('/app/accounts/payments'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']       = ___('account.create_expense');
        $data['heads']       = $this->accountHeadRepository->getExpenseHeads();
        if ($request->expectsJson()) return response()->json(['meta' => $data]);
        return redirect()->to(url('/app/accounts/payments/create'));
    }

    public function store(ExpenseStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->expenseRepo->store($request);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('payments.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['heads']       = $this->accountHeadRepository->getExpenseHeads();
        $data['expense']     = $this->expenseRepo->show($id);
        $data['title']       = ___('account.edit_expense');
        if ($request->expectsJson()) return response()->json(['data' => $data['expense'], 'meta' => $data]);
        return redirect()->to(url('/app/accounts/payments/'.$id.'/edit'));
    }

    public function update(ExpenseUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->expenseRepo->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('payments.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
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
