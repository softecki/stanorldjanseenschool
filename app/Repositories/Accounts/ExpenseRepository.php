<?php

namespace App\Repositories\Accounts;

use App\Enums\Settings;
use App\Models\Accounts\Expense;
use App\Traits\CommonHelperTrait;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;
use App\Interfaces\Accounts\ExpenseInterface;
use App\Models\FloatBalance;
use App\Models\Product;
use App\Models\Item;
use Carbon\Carbon;
use App\Services\Accounts\BankAccountBalanceService;
use Illuminate\Support\Facades\Schema;

class ExpenseRepository implements ExpenseInterface
{
    use ReturnFormatTrait;
    use CommonHelperTrait;
    private $expense;

    public function __construct(Expense $expense)
    {
        $this->expense = $expense;
    }

    public function all()
    {
        return $this->expense->join('expenses_status','expenses_status.id','=','expenses.status')->active()->where('session_id', setting('session'))->get();
    }

   public function getAll()
    {
        return $this->expense
            ->with(['head', 'bankAccount'])
            ->join('expenses_status', 'expenses_status.id', '=', 'expenses.status')
            ->where('expenses.session_id', setting('session'))
            ->select('expenses.*', 'expenses_status.status_name') // Corrected here
            ->orderByDesc('expenses.created_at')
            ->paginate(Settings::PAGINATE);
    }

        public function getAllProduct()
    {
        return Product::leftJoin('items', 'items.id', '=', 'products.name')
            ->select(
                'products.*',
                'products.name as item_id',
                'items.name as item_name',
                'items.description as item_description'
            )
            ->orderBy('items.name')
            ->paginate(Settings::PAGINATE);
      }

        public function getAllItem()
        {
        return Item::orderBy('name')->paginate(Settings::PAGINATE);
        }

         public function getAllBalance()
        {
        return FloatBalance::paginate(Settings::PAGINATE);  
        }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $expenseStore                   = new $this->expense;
            $expenseStore->session_id       = setting('session'); 
            $expenseStore->name             = $request->name;
            $expenseStore->expense_head     = $request->expense_head;
            $expenseStore->date             = $request->date ?: Carbon::now()->format('Y-m-d');
            $expenseStore->amount           = $request->amount;
            // $expenseStore->bank_name             = $request->bank_name;
            $expenseStore->account_number           = $request->account_number ?? null;
            if (Schema::hasColumn('expenses', 'bank_account_id')) {
                $bankAccountId = $request->bank_account_id ?: (is_numeric($request->account_number) ? (int) $request->account_number : null);
                $expenseStore->bank_account_id     = $bankAccountId;
            }
            $expenseStore->invoice_number   = $request->invoice_number;
            $expenseStore->upload_id        = $this->UploadImageCreate($request->document, 'backend/uploads/expenses');
            if($request->expense_head=="3"){
            $expenseStore->description      = $request->driver . " " .$request->description;
            }else{
            $expenseStore->description      = $request->description;
            }
           
            $expenseStore->save();

            // Deduct expense from selected bank account (money out)
            $bankAccountId = $request->bank_account_id ? (int) $request->bank_account_id : (is_numeric($request->account_number) ? (int) $request->account_number : null);
            if ($bankAccountId && $request->amount > 0) {
                BankAccountBalanceService::debit($bankAccountId, (float) $request->amount);
            }

            DB::commit();
            return $this->responseWithSuccess(___('alert.created_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function show($id)
    {
        return $this->expense->find($id);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $expenseUpdate                   = $this->expense->findOrfail($id);
            $previousBankAccountId = Schema::hasColumn('expenses', 'bank_account_id') && $expenseUpdate->bank_account_id
                ? (int) $expenseUpdate->bank_account_id
                : (is_numeric($expenseUpdate->account_number) ? (int) $expenseUpdate->account_number : null);
            $previousAmount = (float) $expenseUpdate->amount;
            $expenseUpdate->session_id       = setting('session'); 
            $expenseUpdate->name             = $request->name;
            $expenseUpdate->expense_head     = $request->expense_head;
            $expenseUpdate->date             = $request->date ?: ($expenseUpdate->date ?: Carbon::now()->format('Y-m-d'));
            $expenseUpdate->amount           = $request->amount;
            // $expenseUpdate->bank_name             = $request->bank_name??null;
            $expenseUpdate->status             = $request->status??null;
            $expenseUpdate->account_number           = $request->account_number ?? null;
            if (Schema::hasColumn('expenses', 'bank_account_id')) {
                $expenseUpdate->bank_account_id     = $request->bank_account_id ?: (is_numeric($request->account_number) ? (int) $request->account_number : null);
            }
            $expenseUpdate->invoice_number   = $request->invoice_number;
            $expenseUpdate->upload_id        = $this->UploadImageUpdate($request->document, 'backend/uploads/expenses', $expenseUpdate->upload_id);
            $expenseUpdate->description      = $request->description;
            $expenseUpdate->save();

            if ($previousBankAccountId && $previousAmount > 0) {
                BankAccountBalanceService::reverseDebit($previousBankAccountId, $previousAmount);
            }

            $bankAccountId = Schema::hasColumn('expenses', 'bank_account_id') && $expenseUpdate->bank_account_id
                ? (int) $expenseUpdate->bank_account_id
                : (is_numeric($expenseUpdate->account_number) ? (int) $expenseUpdate->account_number : null);
            if ($bankAccountId && $expenseUpdate->amount > 0) {
                BankAccountBalanceService::debit($bankAccountId, (float) $expenseUpdate->amount);
            }

            DB::commit();
            return $this->responseWithSuccess(___('alert.updated_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $expenseDestroy = $this->expense->find($id);
            if (!$expenseDestroy) {
                DB::rollBack();
                return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
            }
            $bankAccountId = Schema::hasColumn('expenses', 'bank_account_id') && $expenseDestroy->bank_account_id
                ? (int) $expenseDestroy->bank_account_id
                : (is_numeric($expenseDestroy->account_number) ? (int) $expenseDestroy->account_number : null);
            $amount = (float) $expenseDestroy->amount;
            $this->UploadImageDelete($expenseDestroy->upload_id);
            $expenseDestroy->delete();

            if ($bankAccountId && $amount > 0) {
                BankAccountBalanceService::reverseDebit($bankAccountId, $amount);
            }

            DB::commit();
            return $this->responseWithSuccess(___('alert.deleted_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }
}
