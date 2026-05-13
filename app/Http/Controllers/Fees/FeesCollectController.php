<?php

namespace App\Http\Controllers\Fees;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fees\Collect\FeesCollectStoreRequest;
use App\Http\Requests\Fees\Collect\FeesCollectUpdateRequest;
use App\Interfaces\Fees\FeesCollectInterface;
use App\Models\Fees\FeesCollect;
use App\Repositories\Academic\ClassesRepository;
use App\Repositories\Academic\ClassSetupRepository;
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
    private $classSetupRepo;

    function __construct(
        FeesCollectInterface   $repo,
        ClassesRepository      $classRepo,
        ClassSetupRepository   $classSetupRepo,
        SectionRepository      $sectionRepo,
        StudentRepository      $studentRepo,
        FeesMasterRepository   $feesMasterRepo,
        )
    {
        $this->repo              = $repo;
        $this->classRepo         = $classRepo;
        $this->classSetupRepo    = $classSetupRepo;
        $this->sectionRepo       = $sectionRepo;
        $this->studentRepo       = $studentRepo;
        $this->feesMasterRepo    = $feesMasterRepo;
    }

    /**
     * SPA and dashboard URLs use fees_collects.id; edit/update repositories expect fees_assign_childrens.id.
     * If $id matches a fees_collect row, map to its fees_assign_children_id; otherwise treat $id as assign-child id.
     */
    protected function resolveFeesAssignChildrenId(int|string $id): int
    {
        $id = (int) $id;
        $collect = FeesCollect::query()->find($id);
        if ($collect !== null && $collect->fees_assign_children_id) {
            return (int) $collect->fees_assign_children_id;
        }

        return $id;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['title']         = ___('fees.fees_collect');
        $data['fees_collects'] = $this->repo->getPaginateAll();

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['fees_collects'],
                'meta' => [
                    'title' => $data['title'],
                ],
            ]);
        }

        $data['classes']  = $this->classRepo->assignedAll();
        $data['sections'] = $this->sectionRepo->all();
        $data['students'] = $this->repo->getFeesAssignStudentsAll();

        return view('backend.fees.collect.index', compact('data'));
    }

    /**
     * SPA: same data as legacy fees collect index (assignment lines to collect), with filters and pagination.
     */
    public function collectWorkbench(Request $request): JsonResponse
    {
        $useFilters = $request->filled('class')
            || $request->filled('section')
            || $request->filled('student')
            || $request->filled('name');

        $paginator = $useFilters
            ? $this->repo->getFeesAssignStudents($request)
            : $this->repo->getFeesAssignStudentsAll();

        $rows = $paginator->getCollection()->map(function ($row) {
            $sid = (int) ($row->student_id ?? 0);
            $assignId = (int) ($row->assignId ?? $row->id ?? 0);

            return [
                'assign_id' => $assignId,
                'student_id' => $sid,
                'student_name' => trim((string) (($row->first_name ?? '').' '.($row->last_name ?? ''))),
                'fees_name' => $row->fees_name ?? null,
                'class_name' => $row->class_name ?? null,
                'fees_amount' => $row->fees_amount ?? null,
                'paid_amount' => $row->paid_amount ?? null,
                'remained_amount' => $row->remained_amount ?? null,
            ];
        })->values();

        $paginator->setCollection($rows);

        $classId = $request->get('class');
        $meta = [
            'title' => ___('fees.fees_collect'),
            'class_options' => $this->classRepo->assignedAll()->map(function ($item) {
                $c = $item->class;

                return [
                    'value' => $c ? (string) $c->id : '',
                    'label' => $c ? (string) $c->name : (string) $item->id,
                ];
            })->filter(fn ($o) => $o['value'] !== '')->values(),
            'section_options' => $classId
                ? collect($this->classSetupRepo->getSections($classId))
                    ->map(function ($row) {
                        $sec = $row->section ?? null;
                        if ($sec === null) {
                            return null;
                        }

                        return [
                            'value' => (string) $sec->id,
                            'label' => (string) $sec->name,
                        ];
                    })
                    ->filter()
                    ->values()
                : [],
            'links' => [
                'update_fees' => spa_url('students/update-fees'),
                'cancelled_collect' => url('/fees-collect/cancelled-collect-list'),
                'print_receipt_base' => url('/fees-collect/printReceipt'),
            ],
        ];

        return response()->json([
            'data' => $paginator,
            'meta' => $meta,
        ]);
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
        $data['title']         = ___('fees.fees_collect');
        $data['student']       = $this->studentRepo->show($id);
        $data['fees_assigned']  = $this->repo->feesAssigned($id);

        if ($data['student'] === null) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json(['message' => ___('alert.no_data_found')], 404);
            }

            return redirect()->to(spa_url('collections'))->with('danger', ___('alert.no_data_found'));
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'data' => [
                    'student' => $data['student'],
                    'fees_assigned' => $data['fees_assigned'],
                ],
                'meta' => [
                    'title' => $data['title'],
                    'accounts' => DB::table('bank_accounts')
                        ->select('id', 'account_number', 'bank_name')
                        ->orderBy('bank_name')
                        ->get(),
                    'payment_methods' => collect(config('site.payment_methods', []))
                        ->map(fn ($label, $value) => [
                            'value' => (string) $value,
                            'label' => ___($label),
                        ])
                        ->values(),
                ],
            ]);
        }

        // Browser: always land in the React SPA; embed-only Blade is on collectEmbed().
        return redirect()->to(spa_url('collections/collect/'.$id));
    }

    /**
     * Legacy collect form (partial layout) for embedding in the SPA collect page (iframe).
     */
    public function collectEmbed($id): View|RedirectResponse
    {
        $data['title']         = ___('fees.fees_collect');
        $data['student']       = $this->studentRepo->show($id);
        $data['fees_assigned'] = $this->repo->feesAssigned($id);

        if ($data['student'] === null) {
            return redirect()->to(spa_url('collections'))->with('danger', ___('alert.no_data_found'));
        }

        return view('backend.fees.collect.collect-partial', compact('data'));
    }

    public function collect_list(Request $request): JsonResponse|\Illuminate\View\View
    {
        $data['title']         = ___('fees.fees_collect');
        $data['fees_assigned'] = $this->repo->feesAssignedDetails($request);
        if ($request->expectsJson()) {
            /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection|array $assigned */
            $assigned = $data['fees_assigned'];
            $meta = ['title' => $data['title']];
            if ($assigned instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
                $meta['pagination'] = [
                    'current_page' => $assigned->currentPage(),
                    'last_page' => $assigned->lastPage(),
                    'per_page' => $assigned->perPage(),
                    'total' => $assigned->total(),
                ];
                $meta['class_options'] = $this->classRepo->assignedAll()->map(function ($item) {
                    $c = $item->class;

                    return [
                        'value' => $c ? (string) $c->id : '',
                        'label' => $c ? (string) $c->name : (string) $item->id,
                    ];
                })->filter(fn ($o) => $o['value'] !== '')->values();

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
        $data['fees_assigned']  = $this->repo->feesAssignedDetailsForPushTransactions($request);
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

    public function collect_amendment(Request $request): JsonResponse|\Illuminate\View\View
    {
        $data['title'] = 'Amendments';
        $data['fees_assigned'] = $this->repo->feesAssignedUnpaidDetails($request);
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
        $idInt = (int) $id;
        $data['title'] = ___('fees.fees_collect');

        $collect = FeesCollect::query()
            ->with([
                'bankAccount',
                'session',
                'collectedBy',
                'student',
                'feesAssignChild' => function ($q) {
                    $q->with(['student', 'feesMaster']);
                },
            ])
            ->find($idInt);

        $assignRow = null;
        if ($collect !== null) {
            if ($collect->fees_assign_children_id) {
                $assignRow = $this->repo->showFeesAssignPerChildren((int) $collect->fees_assign_children_id);
            }
        } else {
            $assignRow = $this->repo->showFeesAssignPerChildren($idInt);
        }

        $data['fees_collect'] = $assignRow;

        if ($request->expectsJson()) {
            if ($assignRow === null && $collect === null) {
                return response()->json(['message' => 'Record not found.'], 404);
            }

            $assignForJson = $assignRow;
            if ($assignRow === null && $collect !== null) {
                $assignForJson = $this->syntheticAssignPayloadFromCollect($collect);
            }

            $meta = ['title' => $data['title']];

            $metaCollect = null;
            if ($collect !== null) {
                $metaCollect = $collect;
            } elseif ($assignRow !== null) {
                $metaCollect = FeesCollect::query()
                    ->with(['bankAccount', 'session', 'collectedBy'])
                    ->where('fees_assign_children_id', $assignRow->id)
                    ->orderByDesc('id')
                    ->first();
            }

            if ($metaCollect !== null) {
                $meta['fees_collect'] = $this->buildFeesCollectSpaMeta($metaCollect);
                if ($collect !== null && $assignRow === null) {
                    $meta['assign_missing'] = true;
                }
            }

            return response()->json([
                'data' => $assignForJson,
                'meta' => $meta,
            ]);
        }

        if ($data['fees_collect'] === null) {
            if ($collect !== null) {
                return redirect()->route('fees-collect.index')
                    ->with('danger', 'Fee assignment line is not available for this payment.');
            }

            return redirect()->route('fees-collect.index')->with('danger', 'Record not found.');
        }

        return redirect()->to(spa_url('collections/'.$id.'/edit'));
    }

    /**
     * Flat map for SPA collection view: core fees_collect columns plus joined bank, user, session.
     *
     * @return array<string, mixed>
     */
    protected function buildFeesCollectSpaMeta(FeesCollect $collect): array
    {
        $collect->loadMissing(['bankAccount', 'session', 'collectedBy']);

        return array_filter([
            'id' => $collect->id,
            'amount' => $collect->amount,
            'date' => $collect->date,
            'student_id' => $collect->student_id,
            'session_id' => $collect->session_id ?? null,
            'fees_assign_children_id' => $collect->fees_assign_children_id ?? null,
            'fees_collect_by' => $collect->fees_collect_by ?? null,
            'payment_method' => $collect->payment_method ?? null,
            'payment_gateway' => $collect->payment_gateway ?? null,
            'fine_amount' => $collect->fine_amount ?? null,
            'account_id' => $collect->account_id ?? null,
            'transaction_id' => $collect->transaction_id ?? null,
            'printed' => $collect->printed ?? null,
            'comments' => $collect->comments ?? null,
            'created_at' => $collect->created_at?->toIso8601String(),
            'updated_at' => $collect->updated_at?->toIso8601String(),
            'bank_name' => $collect->bankAccount?->bank_name,
            'bank_account_number' => $collect->bankAccount?->account_number,
            'bank_account_holder' => $collect->bankAccount?->account_name,
            'collected_by_name' => $collect->collectedBy?->name,
            'collected_by_email' => $collect->collectedBy?->email,
            'payment_session_name' => $collect->session?->name,
        ], static fn ($v) => $v !== null && $v !== '');
    }

    /**
     * @return array<string, mixed>
     */
    protected function syntheticAssignPayloadFromCollect(FeesCollect $collect): array
    {
        $student = $collect->relationLoaded('student') ? $collect->student : $collect->student()->first();

        return [
            'id' => $collect->fees_assign_children_id,
            'student_id' => $collect->student_id,
            'fees_amount' => null,
            'paid_amount' => null,
            'remained_amount' => null,
            'comment' => $collect->comments,
            'student' => $student ? $student->only(['id', 'first_name', 'last_name']) : null,
            'fees_master' => null,
        ];
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
        $assignChildrenId = $this->resolveFeesAssignChildrenId($id);
        $result = $this->repo->updateFeesAssignChildren($request, $assignChildrenId);
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

        if ($request->header('X-SPA-List') === '1') {
            $rows = $data['cancelled']->getCollection()->map(function ($row) {
                return [
                    'assign_id' => (int) ($row->assignId ?? 0),
                    'student_name' => trim((string) (($row->first_name ?? '').' '.($row->last_name ?? ''))),
                    'fees_name' => $row->fees_name ?? '—',
                    'class_name' => $row->class_name ?? '—',
                    'fees_amount' => $row->fees_amount,
                    'paid_amount' => $row->paid_amount,
                    'remained_amount' => $row->remained_amount,
                    'cancelled_at' => $row->cancelled_at ?? null,
                ];
            })->values();
            $data['cancelled']->setCollection($rows);

            return response()->json([
                'data' => $data['cancelled'],
                'meta' => [
                    'title' => ___('fees.fees_collect').' - '.__('Cancelled Collect'),
                ],
            ]);
        }

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

        // Transactions for the student in the current session, exclude completed and Admission Fee
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
              AND fees_assigns.session_id = ?
              AND (fees_assign_childrens.comment IS NULL OR fees_assign_childrens.comment != ?)
              AND fees_groups.name != "Admission Fee"
            ORDER BY fees_collects.date ASC, fees_collects.created_at ASC
        ', [$id, setting('session'), setting('session'), 'completed']);

        $data['resultAmount']  = DB::select('select sum(paid_amount) as paid_amount,sum(remained_amount) as remained_amount from fees_assign_childrens 
        inner join fees_collects on fees_collects.fees_assign_children_id = fees_assign_childrens.id 
        inner join students on students.id = fees_assign_childrens.student_id 
        inner join session_class_students on session_class_students.student_id = students.id 
        INNER join classes on classes.id=session_class_students.classes_id 
         where students.id=? and session_class_students.session_id=? 
         and (fees_assign_childrens.comment IS NULL OR fees_assign_childrens.comment != ?)',[$id,setting('session'),'completed']);

        // Outstanding Balance for current academic session
        $data['outstandingBalance']  = DB::select('
            SELECT 
                SUM(fees_assign_childrens.fees_amount) as amount,
                SUM(fees_assign_childrens.paid_amount) as paid_amount,
                SUM(fees_assign_childrens.remained_amount) as remained_amount,
                SUM(fees_assign_childrens.outstandingbalance) as outstandingbalance,
                YEAR(CURDATE()) as year
            FROM fees_assign_childrens 
            INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
            INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
            WHERE fees_assign_childrens.student_id = ? 
              AND fees_groups.name = "Outstanding Balance"
              AND fees_assigns.session_id = ?
              AND (fees_assign_childrens.remained_amount != 0 OR fees_assign_childrens.outstandingbalance < 0)
              AND (fees_assign_childrens.comment IS NULL OR fees_assign_childrens.comment != ?)
        ', [$id, setting('session'), 'completed']);

        // Current session fees: Exclude Outstanding Balance and Admission Fee
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
              AND fees_assigns.session_id = ?
              AND fees_groups.name NOT IN ("Outstanding Balance")
        ', [$id, setting('session'), setting('session')]);


        // School Fees: fees_group_id = 2 (School Fees)
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
              AND fees_assigns.session_id = ?
              AND fees_groups.id = 2
        ', [$id, setting('session'), setting('session')]);

        // Transport: fees_group_id = 3 (Transport)
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
              AND fees_assigns.session_id = ?
              AND fees_groups.id = 3
        ', [$id, setting('session'), setting('session')]);

        // Lunch Fee: fees_group_id = 4 (Lunch Fee)
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
              AND fees_assigns.session_id = ?
              AND fees_groups.id = 4
        ', [$id, setting('session'), setting('session')]);

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
    $ids = $request->input('fees_assign_ids', []);
    if (! is_array($ids)) {
        $ids = [$ids];
    }
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
    if (count($ids) === 0) {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Select at least one receipt line.'], 422);
        }

        return redirect()->back()->with('danger', 'Select at least one receipt.');
    }

    $allData = [];

    foreach ($ids as $id) {
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
