<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\Income\IncomeStoreRequest;
use App\Http\Requests\Accounts\Income\IncomeUpdateRequest;
use App\Models\BankAccounts;
use App\Repositories\Accounts\AccountHeadRepository;
use App\Repositories\Accounts\IncomeRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class IncomeController extends Controller
{
    private $incomeRepo, $accountHeadRepository;

    function __construct(IncomeRepository $incomeRepo, AccountHeadRepository $accountHeadRepository)
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        } 
        $this->incomeRepo                  = $incomeRepo; 
        $this->accountHeadRepository       = $accountHeadRepository; 
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['income'] = $this->incomeRepo->getAll();
        $data['title'] = ___('account.income');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['income'], 'meta' => ['title' => $data['title']]]);
        }
        return view('backend.accounts.income.index', compact('data'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']       = ___('account.create_income');
        $data['heads']       = $this->accountHeadRepository->getIncomeHeads();
        $data['bank_accounts'] = BankAccounts::orderBy('bank_name')->get();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return redirect()->to(url('/app/accounts/income/create'));
    }

    public function store(IncomeStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->incomeRepo->store($request);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('income.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['heads']       = $this->accountHeadRepository->getIncomeHeads();
        $data['income']      = $this->incomeRepo->show($id);
        $data['title']       = ___('account.edit_income');
        $data['bank_accounts'] = BankAccounts::orderBy('bank_name')->get();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['income'], 'meta' => $data]);
        }
        return redirect()->to(url('/app/accounts/income/'.$id.'/edit'));
    }

    public function update(IncomeUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->incomeRepo->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('income.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function delete($id)
    {
        $result = $this->incomeRepo->destroy($id);
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
