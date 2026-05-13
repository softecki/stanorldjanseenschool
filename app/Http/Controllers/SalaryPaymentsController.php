<?php

namespace App\Http\Controllers;

use App\Models\SalaryPayments;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SalaryPaymentsController extends Controller
{
    /**
     * @return \Illuminate\Database\Query\Builder
     */
    private function salaryListQuery()
    {
        return DB::table('salary_payments as sp')
            ->join('staff', 'sp.employee_id', '=', 'staff.id')
            ->select([
                'sp.id',
                'sp.employee_id',
                'sp.amount',
                'sp.month',
                'sp.year',
                'sp.payment_type',
                'sp.payment_status',
                'sp.batchnumber',
                'sp.user_id',
                'sp.created_at',
                'sp.updated_at',
                'staff.first_name',
                'staff.last_name',
            ])
            ->orderByDesc('sp.id');
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['title'] = 'Salary Payments';
        $data['departments'] = $this->salaryListQuery()->paginate(10);

        if ($request->expectsJson()) {
            return response()->json(['data' => $data['departments'], 'meta' => ['title' => $data['title']]]);
        }

        return view('backend.staff.salary.index', compact('data'));
    }

    public function create(Request $request): JsonResponse|View
    {
        $data['title'] = 'Salary Payments';
        $data['batch_date'] = date('Ym');

        if ($request->expectsJson()) {
            return response()->json(['meta' => ['title' => $data['title'], 'batch_date' => $data['batch_date']]]);
        }

        return view('backend.staff.salary.create', compact('data'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:32',
        ]);

        try {
            $employees = DB::table('staff')->get();

            foreach ($employees as $employee) {
                if (empty($this->checkEmployee($employee->id, date('Y'), date('m')))) {
                    $salaryPayment = new SalaryPayments;
                    $salaryPayment->employee_id = $employee->id;
                    $salaryPayment->amount = $employee->basic_salary;
                    $salaryPayment->month = date('m');
                    $salaryPayment->year = date('Y');
                    $salaryPayment->payment_type = '0';
                    $salaryPayment->payment_status = '0';
                    $salaryPayment->user_id = $employee->user_id;
                    $salaryPayment->batchnumber = $request->name;
                    $salaryPayment->save();
                }
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Salaries successfully processed.']);
            }

            return redirect()->route('salary.index')->with('success', 'Salaries successfully processed.');
        } catch (\Throwable $th) {
            Log::error('Salary processing error: '.$th->getMessage(), ['exception' => $th]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'An error occurred while processing salaries.'], 500);
            }

            return back()->with('danger', 'An error occurred while processing salaries.');
        }
    }

    private function checkEmployee($user_id, $year, $month)
    {
        $class_setup = DB::select(
            'SELECT id from salary_payments where employee_id  = ? and year = ? and month =?',
            [$user_id, $year, $month]
        );
        if (! empty($class_setup)) {
            return $class_setup[0]->id;
        }

        return '';
    }

    public function show(SalaryPayments $salaryPayments)
    {
        //
    }

    public function edit(Request $request, $id): JsonResponse|View
    {
        $data['department'] = $this->salaryListQuery()->where('sp.id', $id)->first();
        $data['title'] = 'Salary Payments';

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['department'],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return view('backend.staff.salary.edit', compact('data'));
    }

    public function update(Request $request, $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'status' => 'required',
        ]);

        try {
            $row = SalaryPayments::findOrFail($id);
            $row->payment_status = $request->status;
            $row->payment_type = '1';
            $row->save();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Payment status updated.']);
            }

            return redirect()->route('salary.index')->with('success', 'Salaries successfully processed.');
        } catch (\Throwable $th) {
            Log::error('Salary update error: '.$th->getMessage(), ['exception' => $th]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'An error occurred while updating this payment.'], 500);
            }

            return back()->with('danger', 'An error occurred while processing salaries.');
        }
    }

    public function delete(Request $request, $id): JsonResponse
    {
        try {
            SalaryPayments::where('id', $id)->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => ___('alert.deleted_successfully'),
                ]);
            }

            $success[0] = ___('alert.deleted_successfully');
            $success[1] = 'success';
            $success[2] = ___('alert.deleted');
            $success[3] = ___('alert.OK');

            return response()->json($success);
        } catch (\Throwable $th) {
            Log::error('Salary delete error: '.$th->getMessage(), ['exception' => $th]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => ___('alert.something_went_wrong_please_try_again'),
                ], 422);
            }

            $success[0] = ___('alert.something_went_wrong_please_try_again');
            $success[1] = 'error';
            $success[2] = ___('alert.oops');
            $success[3] = ___('alert.OK');

            return response()->json($success);
        }
    }
}
