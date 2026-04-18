<?php

namespace App\Http\Controllers\Fees;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fees\Collect\FeesCollectStoreRequest;
use App\Http\Requests\Fees\Collect\FeesCollectUpdateRequest;
use App\Interfaces\Fees\FeesCollectInterface;
use App\Repositories\Academic\ClassesRepository;
use App\Repositories\Academic\SectionRepository;
use App\Repositories\Fees\FeesMasterRepository;
use App\Repositories\StudentInfo\StudentRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class FeesCollectController extends Controller
{
    private $repo;
    private $classRepo;
    private $sectionRepo;
    private $studentRepo;
    private $feesMasterRepo;

    function __construct(
        FeesCollectInterface   $repo,
        ClassesRepository      $classRepo, 
        SectionRepository      $sectionRepo,
        StudentRepository      $studentRepo,
        FeesMasterRepository   $feesMasterRepo,
        )
    {
        $this->repo              = $repo;  
        $this->classRepo         = $classRepo; 
        $this->sectionRepo       = $sectionRepo;
        $this->studentRepo       = $studentRepo;
        $this->feesMasterRepo    = $feesMasterRepo;
    }
    
    public function index(Request $request): JsonResponse|View
    {
        $data['title']              = ___('fees.fees_collect');
        $data['fees_collects']      = $this->repo->getPaginateAll();
        $data['classes']            = $this->classRepo->assignedAll();
        $data['sections']           = $this->sectionRepo->all();
        $data['students'] = $this->repo->getFeesAssignStudentsAll();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['fees_collects'],
                'meta' => [
                    'title' => $data['title'],
                    'classes' => $data['classes'],
                    'sections' => $data['sections'],
                    'students' => $data['students'],
                ],
            ]);
        }

        return view('backend.fees.collect.index', compact('data'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']        = ___('fees.fees_collect');
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(url('/app/fees/collections/create'));
        
    }

    public function collect(Request $request, $id): JsonResponse|RedirectResponse
    { // student id
        $data['title']          = ___('fees.fees_collect');
        $data['student']        = $this->studentRepo->show($id);
        $data['fees_assigned']  = $this->repo->feesAssigned($id);

        // When loaded via AJAX (e.g. Collect fees button in index panel), return only the form content
        // so it renders correctly inside the panel instead of a full nested layout.
        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'student' => $data['student'],
                    'fees_assigned' => $data['fees_assigned'],
                ],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return redirect()->to(url('/app/fees/collections/collect/'.$id));
    }

    public function collect_list(Request $request): JsonResponse|\Illuminate\View\View
    { // student id
        $data['title']          = ___('fees.fees_collect');
        $data['fees_assigned']  = $this->repo->feesAssignedDetails();
        Log::info($data['fees_assigned']);
        if ($request->expectsJson()) {
            $assigned = $data['fees_assigned'];
            $meta = ['title' => $data['title']];
            if ($assigned instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
                $meta['pagination'] = [
                    'current_page' => $assigned->currentPage(),
                    'last_page' => $assigned->lastPage(),
                    'per_page' => $assigned->perPage(),
                    'total' => $assigned->total(),
                ];

                return response()->json([
                    'data' => $assigned->items(),
                    'meta' => $meta,
                ]);
            }

            return response()->json([
                'data' => is_array($assigned) ? $assigned : collect($assigned)->values()->all(),
                'meta' => $meta,
            ]);
        }
        return view('backend.fees.collect.transactions', compact('data'));
    }

    public function collect_transactions(Request $request): JsonResponse|\Illuminate\View\View
    { // student id
        $data['title']          = ___('fees.fees_collect');
        $data['fees_assigned']  = $this->repo->feesAssignedDetailsForPushTransactions();
        Log::info($data['fees_assigned']);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['fees_assigned'], 'meta' => ['title' => $data['title']]]);
        }
        return view('backend.fees.collect.transactions_online', compact('data'));
    }

    public function getFeesTransactionsCollectStudents(Request $request)
    { // student id
        $data['title']          = ___('fees.fees_collect');
        $data['fees_assigned']  = $this->repo->feesAssignedDetailsSearch($request);
        Log::info($data['fees_assigned']);
        return view('backend.fees.collect.transactions', compact('data'));
    }

  public function collect_unpaid_list(){
      $data['title']          = ___('fees.fees_collect');
      $data['fees_assigned']  = $this->repo->feesAssignedUnpaidDetails();
      Log::info($data['fees_assigned']);
      return view('backend.fees.collect.unpaid', compact('data'));
  }

   public function collect_amendment(Request $request): JsonResponse|\Illuminate\View\View{
      $data['title']          = 'Amendments';
      $data['fees_assigned']  = $this->repo->feesAssignedUnpaidDetails();
      Log::info($data['fees_assigned']);
      if ($request->expectsJson()) {
          return response()->json(['data' => $data['fees_assigned'], 'meta' => ['title' => $data['title']]]);
      }
      return view('backend.fees.collect.amendment_list', compact('data'));
  }


    public function search_collect_unpaid_list(Request $request){
        $data['title']          = ___('fees.fees_collect');
        $data['fees_assigned']  = $this->repo->feesAssignedUnpaidDetailsSearch($request);
        Log::info($data['fees_assigned']);
        return view('backend.fees.collect.unpaid', compact('data'));
    }
    public function store(Request $request): JsonResponse|RedirectResponse
    {

        $result = $this->repo->store($request);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return back()->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        // $data['fees_collect']  = $this->repo->show($id);
        $data['fees_collect'] = $this->repo->showFeesAssignPerChildren($id);
        $data['title']         = ___('fees.fees_collect');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['fees_collect'], 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(url('/app/fees/collections/'.$id.'/edit'));
    }

     public function amendment($id)
    {
        // $data['fees_collect']  = $this->repo->show($id);
        $data['fees_collect'] = $this->repo->showFeesAssignPerChildren($id);
        $data['title']         = ___('fees.fees_collect');
        return view('backend.fees.collect.amendment', compact('data'));
    }

    public function update(Request $request, $id): JsonResponse|RedirectResponse
    {
        // $result = $this->repo->update($request, $id);
        $result = $this->repo->updateFeesAssignChildren($request, $id);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('fees-collect.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

     public function update_amendment(Request $request, $id)
    {
          $validator = Validator::make($request->all(), [
        'parent_name' => 'required|string|max:255',
        'phonenumber' => 'required|string|max:255',
        'date' => 'required|string|max:255',
        'description' => 'required|string|max:255',
    ], [
        'parent_name.required' => 'Missing Data.',
        'phonenumber.required' => 'Missing Data.',
        'date.required' => 'Missing Data.',
        'description.required' => 'Missing Data.',
    ]);

    if ($validator->fails()) {
         return back()->with('danger', 'Fill all required fields');
    }
        $result = $this->repo->updateAmendment($request, $id);
        if($result['status']){
            return redirect()->route('fees-collect.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message']);
    }

    public function delete($id)
    {
        
        $result = $this->repo->destroy($id);
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

    /**
     * Show push_transaction details: full page for normal GET, HTML partial for AJAX (modal).
     */
    public function pushTransactionDetails($id)
    {
        $push = DB::table('push_transactions')->where('id', $id)->first();
        if (!$push) {
            if (request()->ajax()) {
                return response('<div class="alert alert-warning">Transaction not found.</div>', 404);
            }
            return redirect()->route('fees-collect.collect-transactions')->with('danger', 'Transaction not found.');
        }
        if (request()->ajax()) {
            return response()->view('backend.fees.collect.push-transaction-details', ['push' => $push]);
        }
        return view('backend.fees.collect.push-transaction-details-page', ['push' => $push]);
    }

    /**
     * Cancel push transaction and reverse all affected data (fees_collects, fees_assign_childrens, incomes, bank, push_transactions).
     */
    public function cancelPushTransaction(Request $request, $id)
    {
        $result = $this->repo->cancelPushTransactionAndReverse((int) $id);
        if ($result['status']) {
            if ($request->wantsJson()) {
                return response()->json(['status' => true, 'message' => $result['message']]);
            }
            return redirect()->route('fees-collect.collect-transactions')->with('success', $result['message']);
        }
        $message = $result['message'] ?? ___('alert.something_went_wrong_please_try_again');
        if ($request->wantsJson()) {
            return response()->json(['status' => false, 'message' => $message], 422);
        }
        return redirect()->route('fees-collect.collect-transactions')->with('danger', $message);
    }

    public function deleteFees($id)
    {
        try {
            $ok = $this->repo->cancelFeesAssign($id);
            if ($ok) {
                $success[0] = ___('alert.updated_successfully');
                $success[1] = 'success';
                $success[2] = ___('alert.deleted');
                $success[3] = ___('alert.OK');
                return response()->json($success);
            }
            $success[0] = ___('alert.something_went_wrong_please_try_again');
            $success[1] = 'error';
            $success[2] = ___('alert.oops');
            return response()->json($success);
        } catch (\Throwable $th) {
            $success[0] = 'Failed';
            $success[1] = 'error';
            $success[2] = ___('alert.oops');
            return response()->json($success);
        }
    }

    /**
     * Sidebar: Cancelled Collect list (partial HTML for AJAX or full page).
     */
    public function cancelledCollectList(Request $request)
    {
        $data['cancelled'] = $this->repo->getCancelledCollects(20);
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('backend.fees.collect.partials.cancelled-collect-list', compact('data'))->render(),
            ]);
        }
        $data['title'] = ___('fees.fees_collect') . ' - ' . __('Cancelled Collect');
        return view('backend.fees.collect.cancelled', compact('data'));
    }

    public function getFeesCollectStudents(Request $request)
    {
        $data['students'] = $this->repo->getFeesAssignStudents($request);
        $data['title']    = ___('fees.fees_collect');
        $data['classes']  = $this->classRepo->assignedAll();
        return view('backend.fees.collect.index', compact('data'));
    }

     public function getFeesCollectStudentsResult(Request $request)
    {
        $data['students'] = $this->repo->getFeesAssignStudents($request);
        $data['title']    = ___('fees.fees_collect');
        $data['classes']  = $this->classRepo->assignedAll();
        // return view('backend.fees.collect.feesresult', compact('data'));
         $html = view('backend.fees.collect.feesresult', compact('data'))->render();

        return response()->json([
            'html' => $html,
            'students' => $data['students']->toArray(),
        ]);
    }

    public function feesShow(Request $request)
    {
        $data = $this->repo->feesShow($request);
        Log::info($data);
//        return $data;
        return view('backend.fees.collect.fees-show', compact('data'));
    }

    public function generatePDF($id)
    {


        $data['result']  = DB::select('select * from fees_assign_childrens 
        inner join students on students.id = fees_assign_childrens.student_id 
        inner join session_class_students on session_class_students.student_id = students.id 
        INNER join classes on classes.id=session_class_students.classes_id 
         where students.id=? and session_class_students.session_id=?',[$id,setting('session')]);

        // Get transactions for the student, filter by year 2026, exclude completed and Admission Fee
        $data['results']  = DB::select('
            SELECT fees_collects.*
            FROM fees_assign_childrens 
            INNER JOIN fees_collects ON fees_collects.fees_assign_children_id = fees_assign_childrens.id 
            INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
            INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
            INNER JOIN students ON students.id = fees_assign_childrens.student_id 
            INNER JOIN session_class_students ON session_class_students.student_id = students.id 
            INNER JOIN classes ON classes.id = session_class_students.classes_id 
            WHERE students.id = ? 
              AND session_class_students.session_id = ?
              AND fees_assigns.session_id = 9
              AND YEAR(fees_collects.created_at) = 2026
              AND YEAR(fees_assign_childrens.created_at) = 2026
              AND (fees_assign_childrens.comment IS NULL OR fees_assign_childrens.comment != ?)
              AND fees_groups.name != "Admission Fee"
            ORDER BY fees_collects.date ASC, fees_collects.created_at ASC
        ', [$id, setting('session'), 'completed']);

        $data['resultAmount']  = DB::select('select sum(paid_amount) as paid_amount,sum(remained_amount) as remained_amount from fees_assign_childrens 
        inner join fees_collects on fees_collects.fees_assign_children_id = fees_assign_childrens.id 
        inner join students on students.id = fees_assign_childrens.student_id 
        inner join session_class_students on session_class_students.student_id = students.id 
        INNER join classes on classes.id=session_class_students.classes_id 
         where students.id=? and session_class_students.session_id=? 
         and (fees_assign_childrens.comment IS NULL OR fees_assign_childrens.comment != ?)',[$id,setting('session'),'completed']);

        // Outstanding Balance: Only for 2026, include if remained_amount != 0 OR if outstandingbalance is negative (overpayment)
        // Filter by year 2026 and session_id = 9
        // Removed unnecessary joins to prevent duplicate rows
        $data['outstandingBalance']  = DB::select('
            SELECT 
                SUM(fees_assign_childrens.fees_amount) as amount,
                SUM(fees_assign_childrens.paid_amount) as paid_amount,
                SUM(fees_assign_childrens.remained_amount) as remained_amount,
                SUM(fees_assign_childrens.outstandingbalance) as outstandingbalance,
                2026 as year
            FROM fees_assign_childrens 
            INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
            INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
            WHERE fees_assign_childrens.student_id = ? 
              AND fees_groups.name = "Outstanding Balance"
              AND fees_assigns.session_id = 9
              AND YEAR(fees_assign_childrens.created_at) = 2026
              AND (fees_assign_childrens.remained_amount != 0 OR fees_assign_childrens.outstandingbalance < 0)
              AND (fees_assign_childrens.comment IS NULL OR fees_assign_childrens.comment != ?)
        ', [$id, 'completed']);

        // Current Year Fees: Exclude Outstanding Balance and Admission Fee, filter by year 2026 and session_id = 9
        $data['otherFee']  = DB::select('
            SELECT 
                SUM(fees_amount) as amount,
                SUM(paid_amount) as paid_amount, 
                SUM(remained_amount) as remained_amount  
            FROM fees_assign_childrens 
            INNER JOIN fees_masters ON fees_masters.id = fees_assign_childrens.fees_master_id
            INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
            INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
            INNER JOIN students ON students.id = fees_assign_childrens.student_id 
            INNER JOIN session_class_students ON session_class_students.student_id = students.id 
            INNER JOIN classes ON classes.id = session_class_students.classes_id 
            WHERE students.id = ? 
              AND session_class_students.session_id = ?
              AND fees_assigns.session_id = 9
              AND YEAR(fees_assign_childrens.created_at) = 2026
              AND fees_groups.name NOT IN ("Outstanding Balance")
        ', [$id, setting('session')]);


        // School Fees: Filter by year 2026, session_id = 9, and fees_group_id = 2 (School Fees)
        $data['schoolfees']  = DB::select('
            SELECT 
                SUM(fees_amount) as amount,
                SUM(paid_amount) as paid_amount,
                SUM(remained_amount) as remained_amount,
                SUM(outstandingbalance) as outstandingbalance,
                SUM(quater_one) as quater_one,
                SUM(quater_two) as quater_two,
                SUM(quater_three) as quater_three,
                SUM(quater_four) as quater_four 
            FROM fees_assign_childrens 
            INNER JOIN fees_masters ON fees_masters.id = fees_assign_childrens.fees_master_id
            INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
            INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
            INNER JOIN students ON students.id = fees_assign_childrens.student_id 
            INNER JOIN session_class_students ON session_class_students.student_id = students.id 
            INNER JOIN classes ON classes.id = session_class_students.classes_id 
            WHERE students.id = ? 
              AND session_class_students.session_id = ?
              AND fees_assigns.session_id = 9
              AND YEAR(fees_assign_childrens.created_at) = 2026
              AND fees_groups.id = 2
        ', [$id, setting('session')]);

        // Transport: Filter by year 2026, session_id = 9, and fees_group_id = 3 (Transport)
        $data['transport']  = DB::select('
            SELECT 
                SUM(fees_amount) as amount,
                SUM(paid_amount) as paid_amount,
                SUM(remained_amount) as remained_amount,
                SUM(outstandingbalance) as outstandingbalance,
                SUM(quater_one) as quater_one,
                SUM(quater_two) as quater_two,
                SUM(quater_three) as quater_three,
                SUM(quater_four) as quater_four 
            FROM fees_assign_childrens 
            INNER JOIN fees_masters ON fees_masters.id = fees_assign_childrens.fees_master_id
            INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
            INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
            INNER JOIN students ON students.id = fees_assign_childrens.student_id 
            INNER JOIN session_class_students ON session_class_students.student_id = students.id 
            INNER JOIN classes ON classes.id = session_class_students.classes_id 
            WHERE students.id = ? 
              AND session_class_students.session_id = ?
              AND fees_assigns.session_id = 9
              AND YEAR(fees_assign_childrens.created_at) = 2026
              AND fees_groups.id = 3
        ', [$id, setting('session')]);

        // Lunch Fee: Filter by year 2026, session_id = 9, and fees_group_id = 4 (Lunch Fee)
        $data['lunch']  = DB::select('
            SELECT 
                SUM(fees_amount) as amount,
                SUM(paid_amount) as paid_amount,
                SUM(remained_amount) as remained_amount,
                SUM(outstandingbalance) as outstandingbalance,
                SUM(quater_one) as quater_one,
                SUM(quater_two) as quater_two,
                SUM(quater_three) as quater_three,
                SUM(quater_four) as quater_four 
            FROM fees_assign_childrens 
            INNER JOIN fees_masters ON fees_masters.id = fees_assign_childrens.fees_master_id
            INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
            INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
            INNER JOIN students ON students.id = fees_assign_childrens.student_id 
            INNER JOIN session_class_students ON session_class_students.student_id = students.id 
            INNER JOIN classes ON classes.id = session_class_students.classes_id 
            WHERE students.id = ? 
              AND session_class_students.session_id = ?
              AND fees_assigns.session_id = 9
              AND YEAR(fees_assign_childrens.created_at) = 2026
              AND fees_groups.id = 4
        ', [$id, setting('session')]);

        $data['printedby'] = Auth::user()->name;
        
        // Calculate total amount for QR code
        // Outstanding Balance: Include if remained_amount != 0 OR if outstandingbalance is negative (overpayment)
        // Negative outstanding balance means overpayment and should be deducted from total
        $outstandingBalanceAmount = 0;
        if (!empty($data['outstandingBalance'][0])) {
            $outstandingBalance = $data['outstandingBalance'][0]->outstandingbalance ?? 0;
            // Include if remained_amount != 0 (positive balance) OR if outstandingbalance is negative (overpayment)
            if ($data['outstandingBalance'][0]->remained_amount != 0 || $outstandingBalance < 0) {
                $outstandingBalanceAmount = $outstandingBalance;
            }
        }
        
        $currentYearAmount = $data['otherFee'][0]->amount ?? 0;
        // If outstanding balance is negative (overpayment), it reduces the total amount
        $totalAmount = $currentYearAmount + $outstandingBalanceAmount;
        $totalPaid = 0;
        $transactionIds = [];
        if (!empty($data['results'])) {
            foreach ($data['results'] as $item) {
                $totalPaid += $item->amount;
                if (!empty($item->transaction_id)) {
                    $transactionIds[] = $item->transaction_id;
                }
            }
        }
        $remainedAmount = $totalAmount - $totalPaid;
        $studentId = !empty($data['result'][0]->student_id) ? $data['result'][0]->student_id : $id;
        
        // Generate QR code data (student_id:total_amount:transaction_id1,transaction_id2,...)
        $transactionIdsString = implode(',', $transactionIds);
        $qrData = $studentId . ':' . number_format($totalAmount, 2, '.', '') . ':' . $transactionIdsString;
        $data['qrCodeUrl'] = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qrData);
        $data['remainedAmount'] = $remainedAmount;
        $data['studentId'] = $studentId;
        
        Log::info($data['result']);
        if (request()->ajax()) {
            return view('backend.report.receipt-recordview-ajax', compact('data'))->render();
        }

    $pdf = PDF::loadView('backend.report.receipt-recordPDF', compact('data'));
    return $pdf->download('fees_record_' . date('d_m_Y') . '.pdf');

    }

    public function printManyReceipt(Request $request)
{
    $allData = [];

    foreach ($request->fees_assign_ids as $id) {
        $studentData = [];

        $studentData['result']       = DB::select('select *,fees_groups.name as fee_name from fees_assign_childrens 
            inner join fees_collects on fees_collects.fees_assign_children_id = fees_assign_childrens.id 
        inner join fees_assigns on fees_assigns.id=fees_assign_childrens.fees_assign_id
        inner join bank_accounts on bank_accounts.id=fees_collects.account_id
        inner join fees_groups on fees_groups.id=fees_assigns.fees_group_id
        inner join students on students.id = fees_assign_childrens.student_id 
        inner join session_class_students on session_class_students.student_id = students.id 
        INNER join classes on classes.id=session_class_students.classes_id 
         where fees_collects.id=? and session_class_students.session_id=?',[$id,setting('session')]);

        $studentData['results']       = DB::select('select * from fees_assign_childrens 
        inner join fees_collects on fees_collects.fees_assign_children_id = fees_assign_childrens.id 
        inner join students on students.id = fees_assign_childrens.student_id 
        inner join session_class_students on session_class_students.student_id = students.id 
        INNER join classes on classes.id=session_class_students.classes_id 
         where fees_collects.id=? and session_class_students.session_id=?',[$id,setting('session')]);

        $studentData['resultAmount']       = DB::select('select sum(paid_amount) as paid_amount,sum(remained_amount) as remained_amount from fees_assign_childrens 
        inner join fees_collects on fees_collects.fees_assign_children_id = fees_assign_childrens.id 
        inner join students on students.id = fees_assign_childrens.student_id 
        inner join session_class_students on session_class_students.student_id = students.id 
        INNER join classes on classes.id=session_class_students.classes_id 
         where fees_collects.id=? and session_class_students.session_id=?',[$id,setting('session')]);

        // Generate QR code data (id:amount:date)
        if (!empty($studentData['result'][0])) {
            $transactionId = $studentData['result'][0]->transaction_id ?? $id;
            $amount = $studentData['result'][0]->amount ?? 0;
            $date = $studentData['result'][0]->date ?? date('Y-m-d');
            $qrData = $transactionId . ':' . number_format($amount, 2, '.', '') . ':' . $date;
            $studentData['qrCodeUrl'] = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($qrData);
        }

         DB::update("UPDATE fees_collects SET printed = ? WHERE id = ?",["1",$id]);

        $studentData['printedby'] = Auth::user()->name;
        $allData[] = $studentData;
    }
    
    // Generate a single PDF for all receipts
    $pdf = PDF::loadView('backend.report.transactionslist', compact('allData'));
        return $pdf->download('fees_transaction_receipts_'.date('d_m_Y').'.pdf');
}


    public function generateSalaryPDF($id)
    {
        $data['result']       = [];
        $data['printedby'] = Auth::user()->name;
        $pdf = PDF::loadView('backend.report.salarySlip', compact('data'));
        return $pdf->download('salary_receipt'.'_'.date('d_m_Y').'.pdf');
    }

    public function generateTransactionPDF($id){
        $data['result']       = DB::select('select *,fees_groups.name as fee_name from fees_assign_childrens 
            inner join fees_collects on fees_collects.fees_assign_children_id = fees_assign_childrens.id 
        inner join fees_assigns on fees_assigns.id=fees_assign_childrens.fees_assign_id
        inner join bank_accounts on bank_accounts.id=fees_collects.account_id
        inner join fees_groups on fees_groups.id=fees_assigns.fees_group_id
        inner join students on students.id = fees_assign_childrens.student_id 
        inner join session_class_students on session_class_students.student_id = students.id 
        INNER join classes on classes.id=session_class_students.classes_id 
         where fees_collects.id=? and session_class_students.session_id=?',[$id,setting('session')]);

        $data['results']       = DB::select('select * from fees_assign_childrens 
        inner join fees_collects on fees_collects.fees_assign_children_id = fees_assign_childrens.id 
        inner join students on students.id = fees_assign_childrens.student_id 
        inner join session_class_students on session_class_students.student_id = students.id 
        INNER join classes on classes.id=session_class_students.classes_id 
         where fees_collects.id=? and session_class_students.session_id=?',[$id,setting('session')]);

        $data['resultAmount']       = DB::select('select sum(paid_amount) as paid_amount,sum(remained_amount) as remained_amount from fees_assign_childrens 
        inner join fees_collects on fees_collects.fees_assign_children_id = fees_assign_childrens.id 
        inner join students on students.id = fees_assign_childrens.student_id 
        inner join session_class_students on session_class_students.student_id = students.id 
        INNER join classes on classes.id=session_class_students.classes_id 
         where fees_collects.id=? and session_class_students.session_id=?',[$id,setting('session')]);

        // Generate QR code data (id:amount:date)
        if (!empty($data['result'][0])) {
            $transactionId = $data['result'][0]->transaction_id ?? $id;
            $amount = $data['result'][0]->amount ?? 0;
            $date = $data['result'][0]->date ?? date('Y-m-d');
            $qrData = $transactionId . ':' . number_format($amount, 2, '.', '') . ':' . $date;
            $data['qrCodeUrl'] = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($qrData);
        }

         DB::update("UPDATE fees_collects SET printed = ? WHERE id = ?",["1",$id]);
        $data['printedby'] = Auth::user()->name;
        $pdf = PDF::loadView('backend.report.transaction-receipt-recordPDF', compact('data'));
        return $pdf->download($data['result'][0]->first_name.'_'.$data['result'][0]->last_name.'_'.date('d_m_Y').'.pdf');
    }



    
}
