<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalaryPaymentsRequest;
use App\Http\Requests\UpdateSalaryPaymentsRequest;
use App\Models\SalaryPayments;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class SalaryPaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse|\Illuminate\View\View
    {
        //
        $data['title']              = 'Salary Payments';
        $data['departments'] = DB::table('salary_payments')
            ->join('staff', 'salary_payments.employee_id', '=', 'staff.id')
            ->select('salary_payments.*', 'staff.*') // Adjust columns as needed
            ->paginate(10);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['departments'], 'meta' => ['title' => $data['title']]]);
        }

        return view('backend.staff.salary.index', compact('data'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['title']              = 'Salary Payments';
        $data['batch_date'] = date('Ym');
        return view('backend.staff.salary.create', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSalaryPaymentsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Retrieve all employees from the 'users' table
            $employees = DB::table('staff')->get(); // Variable name should ideally be plural (e.g., $employees)

            foreach ($employees as $employee) {
                // Ensure employee salary is not already logged for the current month and year
                if (empty($this->checkEmployee($employee->id, date('Y'), date('m')))) {
                    // Create a new SalaryPayments record
                    $salaryPayment = new SalaryPayments();
                    $salaryPayment->employee_id = $employee->id;
                    $salaryPayment->amount = $employee->basic_salary; // Ensure 'basic_salary' exists in 'users'
                    $salaryPayment->month = date('m');
                    $salaryPayment->year = date('Y');
                    $salaryPayment->payment_type = "0"; // Use constants or enums for such values
                    $salaryPayment->payment_status = "0"; // Consider using constants here as well
                    $salaryPayment->user_id = $employee->user_id; // This appears redundant; clarify its purpose
                    $salaryPayment->batchnumber = $request->name; // Ensure 'name' exists in the request and is validated
                    $salaryPayment->save();
                }
            }

            // Redirect with success message
            return redirect()->route('salary.index')->with('success', 'Salaries successfully processed.');

        } catch (\Throwable $th) {

            dd($th);
            // Log the error for debugging
            Log::error('Salary processing error: ' . $th->getMessage());

            // Redirect back with danger message
            return back()->with('danger', 'An error occurred while processing salaries.');
        }
    }
    private function checkEmployee( $user_id,  $year,$month)
    {
        $class_setup = DB::select('SELECT id from salary_payments where employee_id  = ? and year = ? and month =?'
            ,[$user_id,$year,$month]);
        if(!empty($class_setup)){
            return $class_setup[0]->id;
        }else{
            return "";
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SalaryPayments  $salaryPayments
     * @return \Illuminate\Http\Response
     */
    public function show(SalaryPayments $salaryPayments)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalaryPayments  $salaryPayments
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Fetch the salary payment record with the associated staff member
        $data['department'] = DB::table('salary_payments')
            ->join('staff', 'salary_payments.employee_id', '=', 'staff.id')
            ->where('salary_payments.id', $id)
            ->select('salary_payments.*', 'staff.*')
            ->first(); // Fetch only a single record, not a collection

        // Set the title for the view
        $data['title'] = 'Salary Payments';

        // Debugging output (optional, can be removed in production)
        // dd($data);

        // Return the view with the data
        return view('backend.staff.salary.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSalaryPaymentsRequest  $request
     * @param  \App\Models\SalaryPayments  $salaryPayments
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        try {
            $row                = SalaryPayments::findOrfail($id);
            $row->payment_status        = $request->status;
            $row->payment_type        = "1";
            $row->save();
            return redirect()->route('salary.index')->with('success', 'Salaries successfully processed.');
        } catch (\Throwable $th) {
            return back()->with('danger', 'An error occurred while processing salaries.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalaryPayments  $salaryPayments
     * @return \Illuminate\Http\Response
     */
    public function destroy(SalaryPayments $salaryPayments)
    {
        //
    }
}
