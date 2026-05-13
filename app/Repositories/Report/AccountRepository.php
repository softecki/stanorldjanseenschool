<?php

namespace App\Repositories\Report;

use App\Enums\AccountHeadType;
use App\Models\Accounts\Income;
use App\Models\Fees\FeesCollect;
use App\Models\ExaminationResult;
use App\Traits\ReturnFormatTrait;
use App\Models\Fees\FeesAssignChildren;
use App\Interfaces\Report\AccountInterface;
use App\Interfaces\Report\MeritListInterface;
use App\Interfaces\Report\FeesCollectionInterface;
use App\Models\Accounts\Expense;
use Illuminate\Support\Facades\Log;

class AccountRepository implements AccountInterface
{
    use ReturnFormatTrait;

    public function search($request)
    {
        $startDateFormatted = null;
        $endDateFormatted = null;

        Log::info($request->dates);
      if ($request->type == AccountHeadType::INCOME) {
            $result = Income::where('session_id', setting('session'));

            if ($request->head != "") {
                $result = $result->where('income_head', $request->head);

                if ($request->head == "1") {
                    $result = $result->join('fees_assign_childrens', 'incomes.fees_collect_id', '=', 'fees_assign_childrens.id');
                }
            }

        } else {
            $result = Expense::where('session_id', setting('session'));

            if ($request->head != "") {
                $result = $result->where('expense_head', $request->head);
            }
        }
       
        if($request->dates != ""){
            if (!empty($request->dates) ) {
                [$startDate, $endDate] = explode(' - ', $request->dates);
                $startDateFormatted = date('Y-m-d', strtotime($startDate));
                $endDateFormatted   = date('Y-m-d', strtotime($endDate));
                $result = $result->whereBetween('date', [
                    $startDateFormatted ,
                    $endDateFormatted ,
                ]);
            }
        }


        $data['sum']    = (clone $result)->sum('amount');
        $data['cash'] = (clone $result)->where('account_number', '=', '5')->sum('amount');
        $data['bank'] = (clone $result)->where('account_number', '!=', '5')->sum('amount');
        $data['start_date'] =  $startDateFormatted;
        $data['end_date'] =  $endDateFormatted;
        $data['result'] = $result->with('head')->latest()->paginate(20);
        
        return $data;
    }

    public function searchPDF($request)
{
    $isIncome = $request->type == AccountHeadType::INCOME;
    $startDateFormatted = null;
    $endDateFormatted = null;

    // Base query
    $query = $isIncome ? Income::query() : Expense::query();
    $query->where('session_id', setting('session'));

    // Filter by head
    if (!empty($request->head)) {
        $headColumn = $isIncome ? 'income_head' : 'expense_head';
        $query->where($headColumn, $request->head);
    }

    // Filter by date range
    if (!empty($request->dates)) {
        [$startDate, $endDate] = explode(' - ', $request->dates);
        $startDateFormatted = date('Y-m-d', strtotime($startDate));
        $endDateFormatted   = date('Y-m-d', strtotime($endDate));
        $query->whereBetween('date', [
            date('Y-m-d', strtotime($startDate)),
            date('Y-m-d', strtotime($endDate)),
        ]);
    }

    // Clone the query before modifying for cash/bank breakdown
    $results = $query->with('head')->latest()->get();

    $data['result'] = $results;
    $data['report'] = $query = $isIncome ? 'INCOME ': 'EXPENSES '; 
    $data['start_date'] =  $startDateFormatted;
        $data['end_date'] =  $endDateFormatted;
    $data['sum']    = $results->sum('amount');
    $data['cash']   = $results->where('account_number', '5')->sum('amount');
    $data['bank']   = $results->where('account_number', '!=', '5')->sum('amount');
    return $data;
}

}
