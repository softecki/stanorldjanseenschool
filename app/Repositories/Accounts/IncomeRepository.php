<?php

namespace App\Repositories\Accounts;

use App\Enums\Settings;
use App\Models\Accounts\Income;
use App\Traits\CommonHelperTrait;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;
use App\Interfaces\Accounts\IncomeInterface;
use App\Models\FloatBalance;
use App\Services\Accounts\BankAccountBalanceService;
use Illuminate\Support\Facades\Schema;

class IncomeRepository implements IncomeInterface
{
    use ReturnFormatTrait;
    use CommonHelperTrait;
    private $income;

    public function __construct(Income $income)
    {
        $this->income = $income;
    }

    public function all()
    {
        return $this->income->active()->where('session_id', setting('session'))->get();
    }

    public function getAll()
    {
        return $this->income->latest()->where('session_id', setting('session'))->paginate(Settings::PAGINATE);
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $incomeStore                   = new $this->income;
            $incomeStore->session_id       = setting('session'); 
            $incomeStore->name             = $request->name;
            $incomeStore->income_head      = $request->income_head;
            $incomeStore->date             = $request->date;
            $incomeStore->amount           = $request->amount;
            $incomeStore->bank_name             = $request->bank_name ?? null;
            $incomeStore->account_number        = $request->account_number ?? null;
            if (Schema::hasColumn('incomes', 'bank_account_id')) {
                $incomeStore->bank_account_id   = $request->bank_account_id ?: null;
            }
            $incomeStore->invoice_number   = $request->invoice_number;
            $incomeStore->upload_id        = $this->UploadImageCreate($request->document, 'backend/uploads/incomes');
            $incomeStore->description      = $request->description;
            $incomeStore->save();

            // Reflect income on bank account balance (money in)
            $bankAccountId = $request->bank_account_id ? (int) $request->bank_account_id : null;
            if ($bankAccountId && $request->amount > 0) {
                BankAccountBalanceService::credit($bankAccountId, (float) $request->amount);
            }

            // Legacy: also update FloatBalance if account_number is used
            if ($request->account_number && !$bankAccountId) {
                $existingBalance = FloatBalance::where('account', $request->account_number)->first();
                if ($existingBalance) {
                    $existingBalance->balance_amount += $request->amount;
                    $existingBalance->save();
                } else {
                    $FloatBalance = new FloatBalance();
                    $FloatBalance->balance_amount = $request->amount;
                    $FloatBalance->account = $request->account_number;
                    $FloatBalance->save();
                }
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
        return  DB::select('select * from incomes where id = ?',[$id])[0];
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $incomeUpdate                   = $this->income->findOrfail($id);
            $incomeUpdate->session_id       = setting('session'); 
            $incomeUpdate->name             = $request->name;
            $incomeUpdate->income_head      = $request->income_head;
            $incomeUpdate->date             = $request->date;
            $incomeUpdate->amount           = $request->amount;
            $incomeUpdate->bank_name             = $request->bank_name ?? null;
            $incomeUpdate->account_number        = $request->account_number ?? null;
            if (Schema::hasColumn('incomes', 'bank_account_id')) {
                $incomeUpdate->bank_account_id   = $request->bank_account_id ?: null;
            }
            $incomeUpdate->invoice_number   = $request->invoice_number;
            $incomeUpdate->upload_id        = $this->UploadImageUpdate($request->document, 'backend/uploads/incomes', $incomeUpdate->upload_id);
            $incomeUpdate->description      = $request->description;
            $incomeUpdate->save();

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
            $incomeDestroy = $this->income->find($id);
            $this->UploadImageDelete($incomeDestroy->upload_id);
            $incomeDestroy->delete();

            DB::commit();
            return $this->responseWithSuccess(___('alert.deleted_successfully'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong_please_try_again'), []);
        }
    }
}
