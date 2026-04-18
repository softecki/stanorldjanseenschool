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
            ->join('expenses_status', 'expenses_status.id', '=', 'expenses.status')
            ->where('session_id', setting('session'))
            ->select('expenses.*', 'expenses_status.status_name') // Corrected here
            ->latest() // Applies ORDER BY created_at DESC (by default)
            ->paginate(Settings::PAGINATE);
    }

        public function getAllProduct()
    {

        return Product::join('items', 'items.id', '=', 'products.name')
        ->paginate(Settings::PAGINATE);
      }

        public function getAllItem()
        {
        return Item::paginate(Settings::PAGINATE);  
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
            $expenseStore->date = Carbon::now()->format('Y-m-d');
            //  $expenseStore->date = $request->date;
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
            $expenseUpdate->session_id       = setting('session'); 
            $expenseUpdate->name             = $request->name;
            $expenseUpdate->expense_head     = $request->expense_head;
            // $expenseUpdate->date             = $request->date;
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
            $this->UploadImageDelete($expenseDestroy->upload_id);
            $expenseDestroy->delete();

            DB::commit();
            return $this->responseWithSuccess(___('alert.deleted_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }
}
