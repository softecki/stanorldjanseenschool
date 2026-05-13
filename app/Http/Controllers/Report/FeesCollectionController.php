<?php
namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\FeesCollectionRequest;
use App\Repositories\Academic\ClassesRepository;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\Examination\ExamAssignRepository;
use App\Repositories\Report\FeesCollectionRepository;
use App\Repositories\StudentInfo\StudentRepository;
use App\Models\Fees\FeesAssign;
use App\Models\Fees\FeesAssignChildren;
use App\Models\Fees\FeesMaster;
use App\Models\Fees\FeesGroup;
use App\Models\Fees\FeesType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class FeesCollectionController extends Controller
{
    private $repo;
    private $examAssignRepo;
    private $classRepo;
    private $classSetupRepo;
    private $studentRepo;

    public function __construct(
        FeesCollectionRepository $repo,
        ExamAssignRepository $examAssignRepo,
        ClassesRepository $classRepo,
        ClassSetupRepository $classSetupRepo,
        StudentRepository $studentRepo
    ) {
        $this->repo           = $repo;
        $this->examAssignRepo = $examAssignRepo;
        $this->classRepo      = $classRepo;
        $this->classSetupRepo = $classSetupRepo;
        $this->studentRepo    = $studentRepo;
    }

    /**
     * @param  mixed  $result  LengthAwarePaginator|iterable
     */
    protected function feesCollectionPaginatedJsonResponse($result, array $meta): JsonResponse
    {
        if ($result instanceof LengthAwarePaginator) {
            $meta['pagination'] = [
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
            ];

            return response()->json(['data' => $result->items(), 'meta' => $meta]);
        }

        return response()->json([
            'data' => is_array($result) ? $result : collect($result)->values()->all(),
            'meta' => $meta,
        ]);
    }

    protected function appendFeesCollectionExportMeta(Request $request, array &$meta): void
    {
        try {
            $dates = $request->input('dates');
            if (! $dates && $request->filled('date_from') && $request->filled('date_to')) {
                $dates = $request->date_from . ' - ' . $request->date_to;
            }
            $encrypted = Crypt::encryptString($dates ?: '__all__');
            $routeParams = [
                'class' => $request->input('class', '0') ?: '0',
                'section' => $request->input('section', '0') ?: '0',
                'dates' => $encrypted,
            ];
            $query = array_filter([
                'balance_status' => $request->input('balance_status'),
                'fee_group_id' => $request->input('fee_group_id'),
                'payment_percentage' => $request->input('payment_percentage'),
            ], fn ($value) => $value !== null && $value !== '');
            $queryString = $query ? '?' . http_build_query($query) : '';

            $meta['pdf_download_url'] = route('report-fees-collection.pdf-generate', $routeParams) . $queryString;
            $meta['excel_download_url'] = route('report-fees-collection.excel-generate', $routeParams) . $queryString;
        } catch (\Throwable $e) {
            Log::warning('report-fees-collection: could not build export URLs', ['message' => $e->getMessage()]);
        }
    }

    protected function feesCollectionReportMeta(Request $request): array
    {
        $classId = $request->input('class', '0');

        $meta = [
            'title' => ___('settings.fees_collection'),
            'classes' => $this->classRepo->assignedAll(),
            'sections' => $classId && $classId !== '0' ? $this->classSetupRepo->getSections($classId) : [],
            'fee_groups' => FeesGroup::where('status', 1)->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'class' => $request->input('class', '0'),
                'section' => $request->input('section', '0'),
                'balance_status' => $request->input('balance_status', '0'),
                'dates' => $request->input('dates', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
                'fee_group_id' => $request->input('fee_group_id', '0'),
                'payment_percentage' => $request->input('payment_percentage', ''),
            ],
            'totals' => $this->repo->collectionReportTotals($request),
        ];

        $this->appendFeesCollectionExportMeta($request, $meta);

        return $meta;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['classes']  = $this->classRepo->assignedAll();
        $data['sections'] = [];
        $data['result']   = $request->expectsJson() ? $this->repo->collectionReport($request) : $this->repo->getAll();
        // dd($data['result']);
        if ($request->expectsJson()) {
            return $this->feesCollectionPaginatedJsonResponse($data['result'], $this->feesCollectionReportMeta($request));
        }
        return view('backend.report.fees-collection', compact('data'));
    }

    public function sections($class): JsonResponse
    {
        return response()->json([
            'data' => $class && $class !== '0' ? $this->classSetupRepo->getSections($class) : [],
        ]);
    }

    public function students(Request $request): JsonResponse|View
    {
        $data['classes']  = $this->classRepo->assignedAll();
        $data['sections'] = [];
        $data['result']   = $request->expectsJson() ? $this->studentsReportRows($request) : $this->repo->getAllStudents();
        if ($request->expectsJson()) {
            return $this->studentsReportJsonResponse($data['result'], $this->studentsReportMeta($request, $data));
        }
        return view('backend.report.studentsList', compact('data'));
    }

    private function normalizeStudentsReportRequest(Request $request): Request
    {
        if (! $request->filled('dates') && $request->filled('date_from') && $request->filled('date_to')) {
            $request->merge([
                'dates' => $request->date_from . ' - ' . $request->date_to,
            ]);
        }

        return $request;
    }

    private function studentsReportQuery(Request $request)
    {
        $request = $this->normalizeStudentsReportRequest($request);
        $class = trim((string) $request->input('class', '0'));
        $section = trim((string) $request->input('section', '0'));
        $q = trim((string) $request->input('q', ''));

        $query = DB::table('students')
            ->join('session_class_students', function ($join) {
                $join->on('session_class_students.student_id', '=', 'students.id')
                    ->where('session_class_students.session_id', setting('session'));
            })
            ->join('classes', 'classes.id', '=', 'session_class_students.classes_id')
            ->join('sections', 'sections.id', '=', 'session_class_students.section_id')
            ->leftJoin('parent_guardians', 'parent_guardians.id', '=', 'students.parent_guardian_id')
            ->leftJoin('student_address_history', 'student_address_history.student_id', '=', 'students.id')
            ->where('session_class_students.classes_id', '!=', 11)
            ->select(
                'students.id',
                'students.first_name',
                'students.last_name',
                'students.admission_no',
                'students.mobile',
                'students.admission_date',
                'students.active',
                'students.residance_address',
                'student_address_history.student_address',
                'classes.name as class_name',
                'classes.id as class_id',
                'sections.name as section_name',
                'sections.id as section_id',
                'parent_guardians.guardian_email',
                'parent_guardians.guardian_mobile'
            );

        if ($class !== '' && $class !== '0') {
            if ($class === 'N') {
                if ($request->filled('dates')) {
                    $dates = explode(' - ', $request->dates);
                    if (count($dates) === 2) {
                        $query->whereBetween('students.admission_date', [
                            date('Y-m-d', strtotime($dates[0])),
                            date('Y-m-d', strtotime($dates[1])),
                        ]);
                    }
                }
            } elseif ($class === 'SHIFTED') {
                $query->where('students.active', '2');
            } else {
                $query->where('classes.id', $class);
            }
        }

        if ($section !== '' && $section !== '0') {
            $query->where('sections.id', $section);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('students.first_name', 'like', "%{$q}%")
                    ->orWhere('students.last_name', 'like', "%{$q}%")
                    ->orWhereRaw("CONCAT(students.first_name, ' ', students.last_name) LIKE ?", ["%{$q}%"])
                    ->orWhere('students.admission_no', 'like', "%{$q}%")
                    ->orWhere('students.mobile', 'like', "%{$q}%")
                    ->orWhere('parent_guardians.guardian_mobile', 'like', "%{$q}%");
            });
        }

        return $query->orderBy('classes.name')->orderBy('students.first_name')->orderBy('students.last_name');
    }

    private function studentsReportRows(Request $request, bool $paginate = true)
    {
        $query = $this->studentsReportQuery($request);

        return $paginate ? $query->paginate(50) : $query->get();
    }

    private function studentsReportMeta(Request $request, array $data): array
    {
        $request = $this->normalizeStudentsReportRequest($request);
        $class = $request->input('class', '0');
        $query = http_build_query(array_filter([
            'class' => $class,
            'section' => $request->input('section', '0'),
            'q' => $request->input('q', ''),
            'dates' => $request->input('dates', ''),
            'date_from' => $request->input('date_from', ''),
            'date_to' => $request->input('date_to', ''),
        ], fn ($value) => $value !== null && $value !== ''));

        return [
            'title' => 'Students Report',
            'classes' => $data['classes'] ?? $this->classRepo->assignedAll(),
            'sections' => $class && ! in_array($class, ['0', 'N', 'SHIFTED'], true) ? $this->classSetupRepo->getSections($class) : [],
            'filters' => [
                'class' => $class,
                'section' => $request->input('section', '0'),
                'q' => $request->input('q', ''),
                'dates' => $request->input('dates', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
            ],
            'totals' => [
                'students_count' => $data['result'] instanceof LengthAwarePaginator ? $data['result']->total() : collect($data['result'])->count(),
            ],
            'pdf_download_url' => route('report-students.pdf-generate') . ($query ? '?' . $query : ''),
            'excel_download_url' => route('report-students.excel-generate') . ($query ? '?' . $query : ''),
        ];
    }

    private function studentsReportJsonResponse($result, array $meta): JsonResponse
    {
        if ($result instanceof LengthAwarePaginator) {
            $meta['pagination'] = [
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
                'per_page' => $result->perPage(),
                'total' => $result->total(),
            ];

            return response()->json(['data' => $result->items(), 'meta' => $meta]);
        }

        return response()->json(['data' => collect($result)->values()->all(), 'meta' => $meta]);
    }

    public function outstandingBreakdown(Request $request): JsonResponse|View
    {
        $classId = trim((string) $request->query('class_id', ''));
        $selectedFeeGroups = collect((array) $request->query('fee_groups', []))
            ->flatMap(fn ($value) => is_string($value) ? explode(',', $value) : [$value])
            ->map(fn ($value) => (int) $value)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $classes = $this->classRepo->assignedAll();
        $feeGroups = DB::table('fees_groups')
            ->join('fees_assigns', function ($join) {
                $join->on('fees_assigns.fees_group_id', '=', 'fees_groups.id')
                    ->where('fees_assigns.session_id', setting('session'));
            })
            ->join('fees_assign_childrens', 'fees_assign_childrens.fees_assign_id', '=', 'fees_assigns.id')
            ->select('fees_groups.id', 'fees_groups.name')
            ->distinct()
            ->orderBy('fees_groups.name')
            ->get();

        if (empty($selectedFeeGroups)) {
            $selectedFeeGroups = $feeGroups->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        $selectedFeeTotals = DB::table('fees_assign_childrens as fac')
            ->join('fees_assigns as fa', function ($join) {
                $join->on('fa.id', '=', 'fac.fees_assign_id')
                    ->where('fa.session_id', setting('session'));
            })
            ->when(! empty($selectedFeeGroups), function ($query) use ($selectedFeeGroups) {
                $query->whereIn('fa.fees_group_id', $selectedFeeGroups);
            })
            ->select(
                'fac.student_id',
                DB::raw('COALESCE(SUM(fac.fees_amount), 0) as total_fees_amount'),
                DB::raw('COALESCE(SUM(fac.paid_amount), 0) as total_paid_amount'),
                DB::raw('COALESCE(SUM(fac.remained_amount), 0) as total_remained_amount')
            )
            ->groupBy('fac.student_id');

        $studentsQuery = DB::table('students as s')
            ->join('session_class_students as scs', function ($join) {
                $join->on('scs.student_id', '=', 's.id')
                    ->where('scs.session_id', '=', setting('session'));
            })
            ->leftJoin('classes as c', 'c.id', '=', 'scs.classes_id')
            ->leftJoin('sections as sec', 'sec.id', '=', 'scs.section_id')
            ->leftJoinSub($selectedFeeTotals, 'selected_fee_totals', function ($join) {
                $join->on('selected_fee_totals.student_id', '=', 's.id');
            })
            ->where('scs.classes_id', '!=', 11)
            ->when($classId !== '', function ($query) use ($classId) {
                $query->where('scs.classes_id', $classId);
            })
            ->select(
                's.id',
                's.first_name',
                's.last_name',
                's.mobile',
                'c.name as class_name',
                'sec.name as section_name',
                DB::raw('COALESCE(selected_fee_totals.total_fees_amount, 0) as total_fees_amount'),
                DB::raw('COALESCE(selected_fee_totals.total_paid_amount, 0) as total_paid_amount'),
                DB::raw('COALESCE(selected_fee_totals.total_remained_amount, 0) as total_remained_amount')
            )
            ->orderBy('s.first_name')
            ->orderBy('s.last_name');

        $rows = $studentsQuery->paginate(100);
        $studentIds = collect($rows->items())->pluck('id')->all();
        $breakdowns = empty($studentIds)
            ? collect()
            : DB::table('fees_assign_childrens as fac')
                ->join('fees_assigns as fa', function ($join) {
                    $join->on('fa.id', '=', 'fac.fees_assign_id')
                        ->where('fa.session_id', setting('session'));
                })
                ->join('fees_groups as fg', 'fg.id', '=', 'fa.fees_group_id')
                ->whereIn('fac.student_id', $studentIds)
                ->whereIn('fa.fees_group_id', $selectedFeeGroups)
                ->select(
                    'fac.student_id',
                    'fg.id as fee_group_id',
                    'fg.name as fee_group_name',
                    DB::raw('COALESCE(SUM(fac.fees_amount), 0) as fees_amount'),
                    DB::raw('COALESCE(SUM(fac.paid_amount), 0) as paid_amount'),
                    DB::raw('COALESCE(SUM(fac.remained_amount), 0) as remained_amount')
                )
                ->groupBy('fac.student_id', 'fg.id', 'fg.name')
                ->get()
                ->groupBy('student_id');

        $items = collect($rows->items())->map(function ($row) use ($breakdowns) {
            $row->fee_breakdowns = ($breakdowns[$row->id] ?? collect())->values()->all();
            return $row;
        })->all();

        $totalsQuery = DB::query()->fromSub(clone $studentsQuery, 'breakdown_rows');
        $totals = $totalsQuery
            ->selectRaw('
                COUNT(*) as students_count,
                COALESCE(SUM(total_fees_amount), 0) as total_fees_amount,
                COALESCE(SUM(total_paid_amount), 0) as total_paid_amount,
                COALESCE(SUM(total_remained_amount), 0) as total_remained_amount
            ')
            ->first();

        $meta = [
            'title' => 'Break Down Report',
            'classes' => $classes,
            'fee_groups' => $feeGroups,
            'selected_fee_groups' => $selectedFeeGroups,
            'filters' => ['class_id' => $classId, 'fee_groups' => $selectedFeeGroups],
            'totals' => [
                'students_count' => (int) ($totals->students_count ?? 0),
                'total_fees_amount' => (float) ($totals->total_fees_amount ?? 0),
                'total_paid_amount' => (float) ($totals->total_paid_amount ?? 0),
                'total_remained_amount' => (float) ($totals->total_remained_amount ?? 0),
            ],
            'pagination' => [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
            ],
        ];

        $exportQuery = http_build_query([
            'class_id' => $classId,
            'fee_groups' => implode(',', $selectedFeeGroups),
        ]);
        $meta['pdf_download_url'] = route('report-outstanding-breakdown.pdf-generate') . '?' . $exportQuery;
        $meta['excel_download_url'] = route('report-outstanding-breakdown.excel-generate') . '?' . $exportQuery;

        if ($request->expectsJson()) {
            return response()->json(['data' => $items, 'meta' => $meta]);
        }

        $data = ['result' => $rows, 'meta' => $meta];
        return view('backend.report.studentsList', compact('data'));
    }

    private function outstandingBreakdownExportData(Request $request): array
    {
        $classId = trim((string) $request->query('class_id', ''));
        $selectedFeeGroups = collect((array) $request->query('fee_groups', []))
            ->flatMap(fn ($value) => is_string($value) ? explode(',', $value) : [$value])
            ->map(fn ($value) => (int) $value)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $feeGroups = DB::table('fees_groups')
            ->join('fees_assigns', function ($join) {
                $join->on('fees_assigns.fees_group_id', '=', 'fees_groups.id')
                    ->where('fees_assigns.session_id', setting('session'));
            })
            ->join('fees_assign_childrens', 'fees_assign_childrens.fees_assign_id', '=', 'fees_assigns.id')
            ->select('fees_groups.id', 'fees_groups.name')
            ->distinct()
            ->orderBy('fees_groups.name')
            ->get();

        if (empty($selectedFeeGroups)) {
            $selectedFeeGroups = $feeGroups->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        $selectedGroups = $feeGroups->filter(fn ($group) => in_array((int) $group->id, $selectedFeeGroups, true))->values();

        $selectedFeeTotals = DB::table('fees_assign_childrens as fac')
            ->join('fees_assigns as fa', function ($join) {
                $join->on('fa.id', '=', 'fac.fees_assign_id')
                    ->where('fa.session_id', setting('session'));
            })
            ->whereIn('fa.fees_group_id', $selectedFeeGroups)
            ->select(
                'fac.student_id',
                DB::raw('COALESCE(SUM(fac.fees_amount), 0) as total_fees_amount'),
                DB::raw('COALESCE(SUM(fac.paid_amount), 0) as total_paid_amount'),
                DB::raw('COALESCE(SUM(fac.remained_amount), 0) as total_remained_amount')
            )
            ->groupBy('fac.student_id');

        $rows = DB::table('students as s')
            ->join('session_class_students as scs', function ($join) {
                $join->on('scs.student_id', '=', 's.id')
                    ->where('scs.session_id', '=', setting('session'));
            })
            ->leftJoin('classes as c', 'c.id', '=', 'scs.classes_id')
            ->leftJoin('sections as sec', 'sec.id', '=', 'scs.section_id')
            ->leftJoinSub($selectedFeeTotals, 'selected_fee_totals', function ($join) {
                $join->on('selected_fee_totals.student_id', '=', 's.id');
            })
            ->where('scs.classes_id', '!=', 11)
            ->when($classId !== '', function ($query) use ($classId) {
                $query->where('scs.classes_id', $classId);
            })
            ->select(
                's.id',
                's.first_name',
                's.last_name',
                's.mobile',
                'c.name as class_name',
                'sec.name as section_name',
                DB::raw('COALESCE(selected_fee_totals.total_fees_amount, 0) as total_fees_amount'),
                DB::raw('COALESCE(selected_fee_totals.total_paid_amount, 0) as total_paid_amount'),
                DB::raw('COALESCE(selected_fee_totals.total_remained_amount, 0) as total_remained_amount')
            )
            ->orderBy('s.first_name')
            ->orderBy('s.last_name')
            ->get();

        $studentIds = $rows->pluck('id')->all();
        $breakdowns = empty($studentIds)
            ? collect()
            : DB::table('fees_assign_childrens as fac')
                ->join('fees_assigns as fa', function ($join) {
                    $join->on('fa.id', '=', 'fac.fees_assign_id')
                        ->where('fa.session_id', setting('session'));
                })
                ->join('fees_groups as fg', 'fg.id', '=', 'fa.fees_group_id')
                ->whereIn('fac.student_id', $studentIds)
                ->whereIn('fa.fees_group_id', $selectedFeeGroups)
                ->select(
                    'fac.student_id',
                    'fg.id as fee_group_id',
                    'fg.name as fee_group_name',
                    DB::raw('COALESCE(SUM(fac.fees_amount), 0) as fees_amount'),
                    DB::raw('COALESCE(SUM(fac.paid_amount), 0) as paid_amount'),
                    DB::raw('COALESCE(SUM(fac.remained_amount), 0) as remained_amount')
                )
                ->groupBy('fac.student_id', 'fg.id', 'fg.name')
                ->get()
                ->groupBy('student_id');

        $items = $rows->map(function ($row) use ($breakdowns) {
            $row->fee_breakdowns = ($breakdowns[$row->id] ?? collect())->values()->all();
            return $row;
        })->values();

        return [
            'rows' => $items,
            'fee_groups' => $selectedGroups,
            'totals' => [
                'students_count' => $items->count(),
                'total_fees_amount' => (float) $items->sum('total_fees_amount'),
                'total_paid_amount' => (float) $items->sum('total_paid_amount'),
                'total_remained_amount' => (float) $items->sum('total_remained_amount'),
            ],
        ];
    }

    public function outstandingBreakdownPdf(Request $request)
    {
        $data = $this->outstandingBreakdownExportData($request);
        $pdf = PDF::loadView('backend.report.outstanding-breakdownPDF', compact('data'));

        return $pdf->download('break_down_report_' . date('d_m_Y') . '.pdf');
    }

    public function outstandingBreakdownExcel(Request $request)
    {
        $data = $this->outstandingBreakdownExportData($request);
        $rows = [];

        foreach ($data['rows'] as $index => $row) {
            $line = [
                $index + 1,
                trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')),
                $row->mobile ?? '',
                $row->class_name ?? '',
            ];

            foreach ($data['fee_groups'] as $group) {
                $breakdown = collect($row->fee_breakdowns)->firstWhere('fee_group_id', $group->id);
                $line[] = (float) ($breakdown->fees_amount ?? 0);
                $line[] = (float) ($breakdown->paid_amount ?? 0);
                $line[] = (float) ($breakdown->remained_amount ?? 0);
            }

            $line[] = (float) ($row->total_fees_amount ?? 0);
            $line[] = (float) ($row->total_paid_amount ?? 0);
            $line[] = (float) ($row->total_remained_amount ?? 0);
            $rows[] = $line;
        }

        $headings = ['No.', 'Student Name', 'Phone Number', 'Class'];
        foreach ($data['fee_groups'] as $group) {
            $headings[] = $group->name . ' Fees';
            $headings[] = $group->name . ' Paid';
            $headings[] = $group->name . ' Remained';
        }
        $headings[] = 'Total Fees';
        $headings[] = 'Total Paid';
        $headings[] = 'Total Remained';

        $export = new class($rows, $headings) implements FromArray, WithHeadings, WithEvents {
            private array $rows;
            private array $headings;

            public function __construct(array $rows, array $headings)
            {
                $this->rows = $rows;
                $this->headings = $headings;
            }

            public function array(): array
            {
                return $this->rows;
            }

            public function headings(): array
            {
                return $this->headings;
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headings));
                        $event->sheet->getDelegate()->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);
                    },
                ];
            }
        };

        return Excel::download($export, 'Break_Down_Report.xlsx');
    }

    public function getTotalPaid($id){
        $total = DB::select("SELECT SUM(amount) from fees_collects WHERE student_id = ?",[$id])[0]->amount ?? 0;
        return $total;
    }

    public function searchStudents(Request $request): JsonResponse|View
    {
        $request = $this->normalizeStudentsReportRequest($request);
        $data['classes']  = $this->classRepo->assignedAll();
        $data['sections'] = $request->class && ! in_array($request->class, ['0', 'N', 'SHIFTED'], true) ? $this->classSetupRepo->getSections($request->class) : [];
        $data['result']   = $request->expectsJson() ? $this->studentsReportRows($request) : $this->repo->getAllStudentsSearch($request->class, $request->dates);
        $data['request']  = $request;
        if ($request->expectsJson()) {
            return $this->studentsReportJsonResponse($data['result'], $this->studentsReportMeta($request, $data));
        }
        return view('backend.report.studentsList', compact('data'));
    }

    /**
     * Display year-based fees assignment report
     *
     * @return \Illuminate\View\View
     */
    public function feesByYear(Request $request): JsonResponse|View
    {
        $currentYear = date('Y');
        
        $data['result'] = DB::select("
            SELECT students.id as student_id,
                   students.first_name, 
                   students.last_name,
                   MAX(classes.name) as class_name,
                   MAX(classes.id) as class_id,
                   SUM(CASE 
                       WHEN fees_groups.name = 'Outstanding Balance' AND fees_assign_childrens.remained_amount != 0 
                       THEN fees_assign_childrens.outstandingbalance 
                       WHEN fees_groups.name != 'Outstanding Balance'
                       THEN fees_assign_childrens.outstandingbalance
                       ELSE 0 
                   END) as total_outstandingbalance
            FROM fees_assign_childrens
            INNER JOIN students ON students.id = fees_assign_childrens.student_id
            INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
                AND fees_assigns.session_id = ?
            INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
            LEFT JOIN session_class_students ON session_class_students.student_id = students.id 
                AND session_class_students.session_id = ?
            LEFT JOIN classes ON classes.id = session_class_students.classes_id
            WHERE YEAR(fees_assign_childrens.created_at) = ?
            GROUP BY students.id, students.first_name, students.last_name
            ORDER BY students.first_name ASC, students.last_name ASC
        ", [setting('session'), setting('session'), $currentYear]);

        $data['years'] = DB::select("
            SELECT DISTINCT YEAR(created_at) as year 
            FROM fees_assign_childrens 
            ORDER BY year DESC
        ");

        // Get distinct classes for the selected year from session_class_students
        $data['classes'] = DB::select("
            SELECT DISTINCT classes.id, classes.name
            FROM fees_assign_childrens
            INNER JOIN students ON students.id = fees_assign_childrens.student_id
            INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
                AND fees_assigns.session_id = ?
            INNER JOIN session_class_students ON session_class_students.student_id = students.id 
                AND session_class_students.session_id = ?
            INNER JOIN classes ON classes.id = session_class_students.classes_id
            WHERE YEAR(fees_assign_childrens.created_at) = ?
            ORDER BY classes.name ASC
        ", [setting('session'), setting('session'), $currentYear]);

        $data['selected_year'] = $currentYear;
        $data['selected_class'] = '';
        
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['result'], 'meta' => $data]);
        }
        return view('backend.report.fees-by-year', compact('data'));
    }

    /**
     * Search fees assignment by year
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function searchFeesByYear(Request $request): JsonResponse|View
    {
        $year = $request->year ?? date('Y');
        $class = $request->class ?? '';
        
        // Build query with optional class filter - Group by student and sum outstandingbalance
        // For Outstanding Balance group: only sum where remained_amount != 0
        // For other groups: sum all outstandingbalance
        $query = "
            SELECT students.id as student_id,
                   students.first_name, 
                   students.last_name,
                   MAX(classes.name) as class_name,
                   MAX(classes.id) as class_id,
                   SUM(CASE 
                       WHEN fees_groups.name = 'Outstanding Balance' AND fees_assign_childrens.remained_amount != 0 
                       THEN fees_assign_childrens.outstandingbalance 
                       WHEN fees_groups.name != 'Outstanding Balance'
                       THEN fees_assign_childrens.outstandingbalance
                       ELSE 0 
                   END) as total_outstandingbalance
            FROM fees_assign_childrens
            INNER JOIN students ON students.id = fees_assign_childrens.student_id
            INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
                AND fees_assigns.session_id = ?
            INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
            LEFT JOIN session_class_students ON session_class_students.student_id = students.id 
                AND session_class_students.session_id = ?
            LEFT JOIN classes ON classes.id = session_class_students.classes_id
            WHERE YEAR(fees_assign_childrens.created_at) = ?
        ";
        $session = setting('session');
        $params = [$session, $session, $year];
        
        // Add class filter if provided
        if (!empty($class)) {
            $query .= " AND session_class_students.classes_id = ?";
            $params[] = $class;
        }
        
        // Group by student and order alphabetically
        $query .= " GROUP BY students.id, students.first_name, students.last_name";
        $query .= " ORDER BY students.first_name ASC, students.last_name ASC";
        
        $data['result'] = DB::select($query, $params);

        $data['years'] = DB::select("
            SELECT DISTINCT YEAR(created_at) as year 
            FROM fees_assign_childrens 
            ORDER BY year DESC
        ");

        // Get distinct classes for the selected year from session_class_students
        $data['classes'] = DB::select("
            SELECT DISTINCT classes.id, classes.name
            FROM fees_assign_childrens
            INNER JOIN students ON students.id = fees_assign_childrens.student_id
            INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
                AND fees_assigns.session_id = ?
            INNER JOIN session_class_students ON session_class_students.student_id = students.id 
                AND session_class_students.session_id = ?
            INNER JOIN classes ON classes.id = session_class_students.classes_id
            WHERE YEAR(fees_assign_childrens.created_at) = ?
            ORDER BY classes.name ASC
        ", [$session, $session, $year]);

        $data['selected_year'] = $year;
        $data['selected_class'] = $class;
        $data['request'] = $request;
        
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['result'], 'meta' => $data]);
        }
        return view('backend.report.fees-by-year', compact('data'));
    }

    /**
     * Show detailed view for a student - fees groups breakdown and transactions
     *
     * @param int $studentId
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function feesByYearDetail($studentId, Request $request): JsonResponse|View
    {
        $year = $request->year ?? date('Y');
        
        // Get student information with current class/section based on the year
        $student = DB::select("
            SELECT students.*,
                   (SELECT classes.name 
                    FROM session_class_students 
                    INNER JOIN classes ON classes.id = session_class_students.classes_id
                    INNER JOIN fees_assigns ON fees_assigns.session_id = session_class_students.session_id
                    INNER JOIN fees_assign_childrens ON fees_assign_childrens.fees_assign_id = fees_assigns.id
                    WHERE session_class_students.student_id = students.id
                      AND YEAR(fees_assign_childrens.created_at) = ?
                    LIMIT 1) as class_name,
                   (SELECT sections.name 
                    FROM session_class_students 
                    INNER JOIN sections ON sections.id = session_class_students.section_id
                    INNER JOIN fees_assigns ON fees_assigns.session_id = session_class_students.session_id
                    INNER JOIN fees_assign_childrens ON fees_assign_childrens.fees_assign_id = fees_assigns.id
                    WHERE session_class_students.student_id = students.id
                      AND YEAR(fees_assign_childrens.created_at) = ?
                    LIMIT 1) as section_name
            FROM students
            WHERE students.id = ?
        ", [$year, $year, $studentId]);
        
        if (empty($student)) {
            abort(404, 'Student not found');
        }
        
        // Get fees groups breakdown for the student
        $data['feesGroups'] = DB::select("
            SELECT fees_groups.id as fees_group_id,
                   fees_groups.name as fees_group_name,
                   SUM(fees_assign_childrens.outstandingbalance) as total_outstandingbalance,
                   SUM(fees_assign_childrens.fees_amount) as total_fees_amount,
                   SUM(fees_assign_childrens.paid_amount) as total_paid_amount,
                   SUM(fees_assign_childrens.remained_amount) as total_remained_amount
            FROM fees_assign_childrens
            INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
            INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
            WHERE fees_assign_childrens.student_id = ?
              AND YEAR(fees_assign_childrens.created_at) = ?
            GROUP BY fees_groups.id, fees_groups.name
            ORDER BY fees_groups.name ASC
        ", [$studentId, $year]);
        
        // Get ALL transactions from fees_collects for the student in the selected year
        $data['transactions'] = DB::select("
            SELECT fees_collects.*,
                   fees_groups.name as fees_group_name,
                   fees_types.name as fees_type_name,
                   bank_accounts.account_name,
                   bank_accounts.account_number
            FROM fees_collects
            INNER JOIN fees_assign_childrens ON fees_assign_childrens.id = fees_collects.fees_assign_children_id
            INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
            INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
            INNER JOIN fees_masters ON fees_masters.id = fees_assign_childrens.fees_master_id
            INNER JOIN fees_types ON fees_types.id = fees_masters.fees_type_id
            LEFT JOIN bank_accounts ON bank_accounts.id = fees_collects.account_id
            WHERE fees_assign_childrens.student_id = ?
              AND YEAR(fees_collects.created_at) = ?
            ORDER BY fees_collects.date ASC, fees_collects.created_at ASC
        ", [$studentId, $year]);
        
        $data['student'] = $student[0];
        $data['selected_year'] = $year;
        
        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'student' => $data['student'],
                    'feesGroups' => $data['feesGroups'],
                    'transactions' => $data['transactions'],
                ],
                'meta' => ['selected_year' => $data['selected_year']],
            ]);
        }
        return view('backend.report.fees-by-year-detail', compact('data', 'student', 'studentId'));
    }

    /**
     * Recalculate and fix balances for a student based on transactions
     * This fixes the deduction order: Outstanding Balance -> Q1 School -> Q1 Transport -> Q2 School -> Q2 Transport -> etc.
     * 
     * This function works for any year (2025, 2026, etc.) by filtering:
     * - fees_assign_childrens by YEAR(created_at) = $year
     * - fees_collects transactions by YEAR(created_at) = $year
     *
     * @param int $studentId The student ID to recalculate balances for
     * @param int $year The year to recalculate (e.g., 2025, 2026)
     * @return \Illuminate\Http\JsonResponse
     */
    public function recalculateBalances($studentId, $year)
    {
        try {
            DB::beginTransaction();

            // Get all fees_assign_childrens for the student in the specified year
            $feesAssignments = DB::select("
                SELECT fees_assign_childrens.*,
                       fees_assigns.fees_group_id,
                       fees_groups.name as fees_group_name
                FROM fees_assign_childrens
                INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
                INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
                WHERE fees_assign_childrens.student_id = ?
                  AND YEAR(fees_assign_childrens.created_at) = ?
                ORDER BY 
                    CASE 
                        WHEN fees_groups.name = 'Outstanding Balance' THEN 1
                        WHEN fees_groups.name = 'School Fees' THEN 2
                        WHEN fees_groups.name = 'Transportation' THEN 3
                        WHEN fees_groups.name = 'Lunch Fee' THEN 4
                        ELSE 5
                    END,
                    fees_assign_childrens.id
            ", [$studentId, $year]);

            // Get all transactions for the student in the specified year, ordered by date
            // EXCLUDE transactions linked to "Admission Fee" group (these are handled separately)
            $transactions = DB::select("
                SELECT fees_collects.*
                FROM fees_collects
                INNER JOIN fees_assign_childrens ON fees_assign_childrens.id = fees_collects.fees_assign_children_id
                INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
                INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
                WHERE fees_assign_childrens.student_id = ?
                  AND YEAR(fees_collects.created_at) = ?
                  AND fees_groups.name != 'Admission Fee'
                ORDER BY fees_collects.date ASC, fees_collects.created_at ASC
            ", [$studentId, $year]);

            // Get Admission Fee transactions separately and calculate total
            $admissionFeeTransactions = DB::select("
                SELECT SUM(fees_collects.amount) as total_amount
                FROM fees_collects
                INNER JOIN fees_assign_childrens ON fees_assign_childrens.id = fees_collects.fees_assign_children_id
                INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
                INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
                WHERE fees_assign_childrens.student_id = ?
                  AND YEAR(fees_collects.created_at) = ?
                  AND fees_groups.name = 'Admission Fee'
            ", [$studentId, $year]);
            
            $admissionFeeTotal = $admissionFeeTransactions[0]->total_amount ?? 0;

            // Check for excess payment from 2024 (negative outstanding balance) BEFORE resetting
            $excessFrom2024 = 0;
            $originalOutstandingBalance = 0;
            foreach ($feesAssignments as $assignment) {
                if ($assignment->fees_group_name == 'Outstanding Balance') {
                    $originalOutstandingBalance = $assignment->outstandingbalance;
                    // If outstanding balance is negative, it means overpaid in 2024
                    if ($assignment->outstandingbalance < 0) {
                        $excessFrom2024 = abs($assignment->outstandingbalance);
                    }
                    break;
                }
            }

            // Calculate total payment amount from transactions in the specified year
            $totalPaymentAmount = 0;
            foreach ($transactions as $transaction) {
                $totalPaymentAmount += $transaction->amount;
            }

            // Total available payment = excess from previous year + transactions from current year
            $remainingPayment = $excessFrom2024 + $totalPaymentAmount;

            // Reset balances - DO NOT reset outstandingbalance (it's used for tracking)
            // Only reset remained_amount, paid_amount, and quarters
            foreach ($feesAssignments as $assignment) {
                $isOutstanding = $assignment->fees_group_name == 'Outstanding Balance';
                $isAdmissionFee = $assignment->fees_group_name == 'Admission Fee';
                
                if ($isOutstanding) {
                    // For Outstanding Balance group:
                    // If outstandingbalance is positive, set remained_amount = outstandingbalance, paid_amount = 0
                    // If outstandingbalance is negative (excess), set remained_amount = 0, paid_amount = excess
                    $currentOutstanding = $assignment->outstandingbalance;
                    
                    if ($currentOutstanding > 0) {
                        // Positive: set remained_amount to outstandingbalance, paid_amount = fees_amount - remained_amount
                        $resetRemained = $currentOutstanding;
                        $resetPaid = $assignment->fees_amount - $resetRemained;
                    } else {
                        // Negative (excess): set remained_amount to 0, paid_amount to excess amount
                        $resetRemained = 0;
                        $resetPaid = abs($currentOutstanding);
                    }
                    
                    DB::update("
                        UPDATE fees_assign_childrens 
                        SET paid_amount = ?,
                            remained_amount = ?,
                            quater_one = 0,
                            quater_two = 0,
                            quater_three = 0,
                            quater_four = 0
                        WHERE id = ?
                    ", [$resetPaid, $resetRemained, $assignment->id]);
                } elseif ($isAdmissionFee) {
                    // For Admission Fee group: update based on transactions found
                    // If there are Admission Fee transactions, set paid_amount = total, remained_amount = fees_amount - paid_amount
                    // If no transactions, reset to 0
                    if ($admissionFeeTotal > 0) {
                        $newPaid = $admissionFeeTotal;
                        $newRemained = max(0, $assignment->fees_amount - $newPaid);
                    } else {
                        $newPaid = 0;
                        $newRemained = $assignment->fees_amount;
                    }
                    
                    DB::update("
                        UPDATE fees_assign_childrens 
                        SET paid_amount = ?,
                            remained_amount = ?,
                            quater_one = 0,
                            quater_two = 0,
                            quater_three = 0,
                            quater_four = 0
                        WHERE id = ?
                    ", [$newPaid, $newRemained, $assignment->id]);
                } else {
                    // For School Fees, Transport, and Lunch Fee groups:
                    // Reset remained_amount to fees_amount and divide quarters
                    $quarterAmount = $assignment->fees_amount / 4;
                    
                    DB::update("
                        UPDATE fees_assign_childrens 
                        SET paid_amount = 0,
                            remained_amount = fees_amount,
                            quater_one = ?,
                            quater_two = ?,
                            quater_three = ?,
                            quater_four = ?
                        WHERE id = ?
                    ", [
                        $quarterAmount,
                        $quarterAmount,
                        $quarterAmount,
                        $quarterAmount,
                        $assignment->id
                    ]);
                }
            }

            // Step 1: Apply to Outstanding Balance first
            // Only update if outstandingbalance is positive
            foreach ($feesAssignments as $assignment) {
                if ($remainingPayment <= 0) break;
                if ($assignment->fees_group_name == 'Outstanding Balance') {
                    $currentOutstanding = DB::table('fees_assign_childrens')
                        ->where('id', $assignment->id)
                        ->value('outstandingbalance');
                    
                    // Only apply payments if outstanding balance is positive
                    if ($currentOutstanding > 0) {
                        // Get current remained_amount (which was set to outstandingbalance in reset)
                        $currentRemained = DB::table('fees_assign_childrens')
                            ->where('id', $assignment->id)
                            ->value('remained_amount');
                        
                        // Deduct from remained_amount
                        $deduct = min($remainingPayment, $currentRemained);
                        $newRemained = $currentRemained - $deduct;
                        $currentPaid = DB::table('fees_assign_childrens')
                            ->where('id', $assignment->id)
                            ->value('paid_amount');
                        $newPaid = $currentPaid + $deduct;
                        
                        // DO NOT update outstandingbalance - only update paid_amount and remained_amount
                        DB::update("
                            UPDATE fees_assign_childrens 
                            SET paid_amount = ?,
                                remained_amount = ?
                            WHERE id = ?
                        ", [$newPaid, $newRemained, $assignment->id]);
                        
                        $remainingPayment -= $deduct;
                    }
                    // Note: If outstanding balance was negative (excess from 2024), 
                    // remained_amount is already set to 0, so we skip and move to quarters
                }
            }

            // Step 2: Apply to quarters in order: Q1 School, Q1 Transport, Q1 Lunch Fee, Q2 School, Q2 Transport, Q2 Lunch Fee, etc.
            $quarters = ['quater_one', 'quater_two', 'quater_three', 'quater_four'];
            $groups = ['School Fees', 'Transportation', 'Lunch Fee'];
            
            foreach ($quarters as $quarter) {
                foreach ($groups as $groupName) {
                    if ($remainingPayment <= 0) break 2;
                    
                    foreach ($feesAssignments as $assignment) {
                        if ($remainingPayment <= 0) break;
                        if ($assignment->fees_group_name == $groupName) {
                            $currentQuarter = DB::table('fees_assign_childrens')
                                ->where('id', $assignment->id)
                                ->value($quarter);
                            
                            if ($currentQuarter > 0) {
                                $deduct = min($remainingPayment, $currentQuarter);
                                $newQuarter = $currentQuarter - $deduct;
                                $currentPaid = DB::table('fees_assign_childrens')
                                    ->where('id', $assignment->id)
                                    ->value('paid_amount');
                                $newPaid = $currentPaid + $deduct;
                                $feesAmount = $assignment->fees_amount;
                                $newRemained = $feesAmount - $newPaid;
                                
                                DB::update("
                                    UPDATE fees_assign_childrens 
                                    SET {$quarter} = ?,
                                        paid_amount = ?,
                                        remained_amount = ?
                                    WHERE id = ?
                                ", [$newQuarter, $newPaid, $newRemained, $assignment->id]);
                                
                                $remainingPayment -= $deduct;
                            }
                        }
                    }
                }
            }

            // Step 3: Handle excess payment (if remainingPayment > 0 after all quarters are paid)
            // Only apply excess to Admission Fee if Admission Fee transactions exist
            // If no Admission Fee transactions, Admission Fee remains unpaid and excess goes to School Fees
            if ($remainingPayment > 0) {
                // Only apply excess to Admission Fee if there were actual Admission Fee transactions
                if ($admissionFeeTotal > 0) {
                    // Find Admission Fee assignment
                    $admissionFeeAssignment = null;
                    foreach ($feesAssignments as $assignment) {
                        if ($assignment->fees_group_name == 'Admission Fee') {
                            $admissionFeeAssignment = $assignment;
                            break;
                        }
                    }
                    
                    // Apply excess to Admission Fee (if it exists and is unpaid)
                    if ($admissionFeeAssignment) {
                        $currentRemained = DB::table('fees_assign_childrens')
                            ->where('id', $admissionFeeAssignment->id)
                            ->value('remained_amount');
                        
                        if ($currentRemained > 0) {
                            // Apply excess to Admission Fee (up to the remained amount)
                            $deduct = min($remainingPayment, $currentRemained);
                            $newRemained = $currentRemained - $deduct;
                            $currentPaid = DB::table('fees_assign_childrens')
                                ->where('id', $admissionFeeAssignment->id)
                                ->value('paid_amount');
                            $newPaid = $currentPaid + $deduct;
                            
                            DB::update("
                                UPDATE fees_assign_childrens 
                                SET paid_amount = ?,
                                    remained_amount = ?
                                WHERE id = ?
                            ", [$newPaid, $newRemained, $admissionFeeAssignment->id]);
                            
                            $remainingPayment -= $deduct;
                        }
                    }
                }
                
                // If there's still excess (or if no Admission Fee transactions), apply to School Fees
                if ($remainingPayment > 0) {
                    // Find School Fees group assignment
                    $schoolFeesAssignment = null;
                    foreach ($feesAssignments as $assignment) {
                        if ($assignment->fees_group_name == 'School Fees') {
                            $schoolFeesAssignment = $assignment;
                            break;
                        }
                    }
                    
                    if ($schoolFeesAssignment) {
                        // Apply excess to School Fees
                        $currentPaid = DB::table('fees_assign_childrens')
                            ->where('id', $schoolFeesAssignment->id)
                            ->value('paid_amount');
                        
                        $newPaid = $currentPaid + $remainingPayment;
                        
                        // Set remained_amount = 0 and outstandingbalance = -excess for School Fees
                        DB::update("
                            UPDATE fees_assign_childrens 
                            SET paid_amount = ?,
                                remained_amount = 0,
                                outstandingbalance = ?
                            WHERE id = ?
                        ", [$newPaid, -$remainingPayment, $schoolFeesAssignment->id]);
                        
                        // Note: DO NOT update Outstanding Balance group - it's used for tracking and should remain unchanged
                    }
                }
            }

            // Step 4: After recalculation, update outstandingbalance for different groups
            // Handle Admission Fee separately first
            foreach ($feesAssignments as $assignment) {
                if ($assignment->fees_group_name == 'Admission Fee') {
                    $currentRemained = DB::table('fees_assign_childrens')
                        ->where('id', $assignment->id)
                        ->value('remained_amount');
                    
                    // Update Admission Fee outstandingbalance to match remained_amount
                    DB::update("
                        UPDATE fees_assign_childrens 
                        SET outstandingbalance = ?
                        WHERE id = ?
                    ", [$currentRemained, $assignment->id]);
                    break; // Only one Admission Fee per student
                }
            }
            
            // Step 4b: Update outstandingbalance for Transport and Lunch Fee groups
            // For School Fees, if there was excess, outstandingbalance is already set to -excess in Step 3
            // Set outstandingbalance = remained_amount for Transport and Lunch Fee groups
            // For School Fees, only update if outstandingbalance is not negative (no excess was applied)
            foreach ($feesAssignments as $assignment) {
                if ($assignment->fees_group_name == 'Outstanding Balance' || $assignment->fees_group_name == 'Admission Fee') {
                    // Skip Outstanding Balance and Admission Fee groups (handled separately)
                    continue;
                }
                
                $currentOutstanding = DB::table('fees_assign_childrens')
                    ->where('id', $assignment->id)
                    ->value('outstandingbalance');
                $currentRemained = DB::table('fees_assign_childrens')
                    ->where('id', $assignment->id)
                    ->value('remained_amount');
                
                // For School Fees: only update if outstandingbalance is not negative (excess already handled in Step 3)
                // For Transport and Lunch Fee: always update
                if ($assignment->fees_group_name == 'School Fees' && $currentOutstanding < 0) {
                    // School Fees has excess (negative outstandingbalance), don't override it
                    continue;
                }
                
                // Update outstandingbalance to match remained_amount
                DB::update("
                    UPDATE fees_assign_childrens 
                    SET outstandingbalance = ?
                    WHERE id = ?
                ", [$currentRemained, $assignment->id]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Balances recalculated successfully',
                'year' => $year,
                'total_transactions' => count($transactions),
                'total_amount' => $totalPaymentAmount,
                'excess_from_previous_year' => $excessFrom2024,
                'total_available_payment' => $excessFrom2024 + $totalPaymentAmount,
                'remaining_payment' => $remainingPayment
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Balance recalculation error: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error recalculating balances: ' . $th->getMessage()
            ], 500);
        }
    }

    public function feeSummary(Request $request): JsonResponse|View
    {
        $data['classes']      = $this->classRepo->assignedAll();
        $data['sections']     = [];
        $data['result']       = $request->expectsJson() ? $this->repo->getAllSumary($request) : $this->repo->getAll();
        $data['total_amount'] = DB::table('fees_assign_childrens')
            ->join('fees_assigns', 'fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
            ->where('fees_assigns.fees_group_id', '!=', '1')
            ->sum('fees_amount');
        $data['paid_amount'] = DB::table('fees_assign_childrens')
            ->join('fees_assigns', 'fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
            ->where('fees_assigns.fees_group_id', '!=', '1')
            ->sum('paid_amount');
        $data['remained_amount'] = DB::table('fees_assign_childrens')
            ->join('fees_assigns', 'fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
            ->where('fees_assigns.fees_group_id', '!=', '1')
            ->sum('remained_amount');

        $data['paid_amount_outstanding'] = DB::table('fees_assign_childrens')
            ->join('fees_assigns', 'fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
            ->where('fees_assigns.fees_group_id', '=', 1)
            ->sum('paid_amount');
        $data['remained_amount_outstanding'] = DB::table('fees_assign_childrens')
            ->join('fees_assigns', 'fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
            ->where('fees_assigns.fees_group_id', '=', 1)
            ->sum('remained_amount');

        if ($request->expectsJson()) {
            $meta = $this->feesSummaryMeta($request, $data);

            return $this->feesCollectionPaginatedJsonResponse($data['result'], $meta);
        }
        return view('backend.report.fees-summary', compact('data'));
    }

    private function normalizeFeesSummaryRequest(Request $request): Request
    {
        if (! $request->filled('dates') && $request->filled('date_from') && $request->filled('date_to')) {
            $request->merge([
                'dates' => $request->date_from . ' - ' . $request->date_to,
            ]);
        }

        return $request;
    }

    private function feesSummaryTotalsFromResult($result): array
    {
        $rows = $result instanceof LengthAwarePaginator ? collect($result->items()) : collect($result);

        return [
            'total_amount' => (float) $rows->sum(fn ($row) => (float) ($row->fees_amount ?? 0)),
            'paid_amount' => (float) $rows->sum(fn ($row) => (float) ($row->paid_amount ?? 0)),
            'remained_amount' => (float) $rows->sum(fn ($row) => (float) ($row->remained_amount ?? 0)),
            'outstanding_amount' => (float) $rows->sum(fn ($row) => (float) ($row->outstanding_amount ?? 0)),
            'paid_amount_outstanding' => (float) $rows->sum(fn ($row) => (float) ($row->outstanding_paid_amount ?? 0)),
            'remained_amount_outstanding' => (float) $rows->sum(fn ($row) => (float) ($row->outstanding_remained_amount ?? 0)),
            'transport_amount' => (float) $rows->sum(fn ($row) => (float) ($row->group3_amount ?? 0)),
            'transport_paid_amount' => (float) $rows->sum(fn ($row) => (float) ($row->group3_paid_amount ?? 0)),
            'transport_remained_amount' => (float) $rows->sum(fn ($row) => (float) ($row->group3_remained_amount ?? 0)),
            'lunch_amount' => (float) $rows->sum(fn ($row) => (float) ($row->group4_amount ?? 0)),
            'admission_fees_amount' => (float) $rows->sum(fn ($row) => (float) ($row->admission_fees_amount ?? 0)),
            'fees_excluding_outstanding' => (float) $rows->sum(fn ($row) => (float) ($row->fees_amount_excluding_outstanding ?? 0)),
            'total_assigned_amount' => (float) $rows->sum(fn ($row) => (float) ($row->total_assigned_amount ?? 0)),
            'paid_from_collections' => (float) $rows->sum(fn ($row) => (float) ($row->paid_from_collections ?? 0)),
            'remained_after_collections' => (float) $rows->sum(fn ($row) => (float) ($row->remained_after_collections ?? 0)),
            'students_count' => $rows->count(),
        ];
    }

    private function feesSummaryExportQuery(Request $request): string
    {
        return http_build_query(array_filter([
            'class' => $request->input('class', '0'),
            'section' => $request->input('section', '0'),
            'fee_group_id' => $request->input('fee_group_id', '0'),
            'amount' => $request->input('amount', ''),
            'dates' => $request->input('dates', ''),
            'date_from' => $request->input('date_from', ''),
            'date_to' => $request->input('date_to', ''),
        ], fn ($value) => $value !== null && $value !== ''));
    }

    private function feesSummaryMeta(Request $request, array $data): array
    {
        $request = $this->normalizeFeesSummaryRequest($request);
        $classId = $request->input('class', '0');
        $query = $this->feesSummaryExportQuery($request);

        return [
            'title' => 'Fees Summary',
            'classes' => $data['classes'] ?? $this->classRepo->assignedAll(),
            'sections' => $classId && $classId !== '0' ? $this->classSetupRepo->getSections($classId) : [],
            'fee_groups' => FeesGroup::where('status', 1)->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'class' => $request->input('class', '0'),
                'section' => $request->input('section', '0'),
                'fee_group_id' => $request->input('fee_group_id', '0'),
                'amount' => $request->input('amount', ''),
                'dates' => $request->input('dates', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
            ],
            'totals' => $this->feesSummaryTotalsFromResult($data['result']),
            'pdf_download_url' => route('report-fees-summary.pdf-generate') . ($query ? '?' . $query : ''),
            'excel_download_url' => route('report-fees-summary.excel-generate') . ($query ? '?' . $query : ''),
        ];
    }

    /**
     * Generate Outstanding Balance for 2026 from 2025 balances
     * This function sums all outstandingbalance from all fee groups for each student in 2025
     * and creates Outstanding Balance group entries for 2026
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateOutstandingBalance2026(Request $request)
    {
        try {
            DB::beginTransaction();

            $previousYear = 2025;
            $newYear = 2026;
            $newSessionId = setting('session');

            // Step 1: Get Outstanding Balance fees_group_id (should be 1 based on codebase)
            $outstandingBalanceGroup = DB::select("
                SELECT id FROM fees_groups WHERE name = 'Outstanding Balance' LIMIT 1
            ");
            
            if (empty($outstandingBalanceGroup)) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Outstanding Balance fees group not found'
                ], 404);
            }
            $outstandingBalanceGroupId = $outstandingBalanceGroup[0]->id;

            // Step 2: Use existing fees_master with id = 53
            $feesMasterId = 53;
            
            // Verify that fees_master 53 exists and is for Outstanding Balance
            $feesMasterCheck = DB::select("
                SELECT id FROM fees_masters 
                WHERE id = ? 
                  AND fees_group_id = ?
                LIMIT 1
            ", [$feesMasterId, $outstandingBalanceGroupId]);

            if (empty($feesMasterCheck)) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Fees Master ID 53 not found or not for Outstanding Balance group'
                ], 404);
            }

            // Step 3: Get all students with outstanding balances in 2025
            // Sum outstandingbalance from all fee groups for each student
            // For "Outstanding Balance" group: only include if remained_amount != 0
            // For other groups: include all outstandingbalance
            // Keep negative values as negative
            $studentsWithOutstanding = DB::select("
                SELECT 
                    fees_assign_childrens.student_id,
                    students.first_name,
                    students.last_name,
                    SUM(
                        CASE 
                            WHEN fees_groups.name = 'Outstanding Balance' AND fees_assign_childrens.remained_amount != 0 
                            THEN fees_assign_childrens.outstandingbalance 
                            WHEN fees_groups.name != 'Outstanding Balance'
                            THEN fees_assign_childrens.outstandingbalance
                            ELSE 0 
                        END
                    ) as total_outstanding_balance
                FROM fees_assign_childrens
                INNER JOIN students ON students.id = fees_assign_childrens.student_id
                INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
                INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
                WHERE YEAR(fees_assign_childrens.created_at) = ?
                  AND (
                      (fees_groups.name = 'Outstanding Balance' AND fees_assign_childrens.remained_amount != 0)
                      OR (fees_groups.name != 'Outstanding Balance' AND fees_assign_childrens.outstandingbalance != 0)
                  )
                GROUP BY fees_assign_childrens.student_id, students.first_name, students.last_name
                HAVING total_outstanding_balance != 0
            ", [$previousYear]);

            $createdCount = 0;
            $skippedCount = 0;
            $errors = [];

            // Step 5: For each student, create Outstanding Balance entry for 2026
            foreach ($studentsWithOutstanding as $student) {
                try {
                    $studentId = $student->student_id;
                    $totalOutstanding = $student->total_outstanding_balance;

                    // Skip if outstanding balance is 0 (but include negative values)
                    if ($totalOutstanding == 0) {
                        $skippedCount++;
                        continue;
                    }

                    // Get student's class and section from session_class_students for 2026
                    $studentClassInfo = DB::select("
                        SELECT classes_id, section_id
                        FROM session_class_students
                        WHERE student_id = ? AND session_id = ?
                        LIMIT 1
                    ", [$studentId, $newSessionId]);

                    if (empty($studentClassInfo)) {
                        $errors[] = "Student {$student->first_name} {$student->last_name} (ID: {$studentId}) not found in session {$newSessionId}";
                        $skippedCount++;
                        continue;
                    }

                    $classId = $studentClassInfo[0]->classes_id;
                    $sectionId = $studentClassInfo[0]->section_id;

                    // Step 6: Get or create fees_assign for Outstanding Balance group in 2026
                    $feesAssign = DB::select("
                        SELECT id FROM fees_assigns
                        WHERE classes_id = ?
                          AND fees_group_id = ?
                          AND section_id = ?
                          AND session_id = ?
                        LIMIT 1
                    ", [$classId, $outstandingBalanceGroupId, $sectionId, $newSessionId]);

                    $feesAssignId = null;
                    if (empty($feesAssign)) {
                        // Create fees_assign
                        $feesAssignModel = new FeesAssign();
                        $feesAssignModel->session_id = $newSessionId;
                        $feesAssignModel->classes_id = $classId;
                        $feesAssignModel->section_id = $sectionId;
                        $feesAssignModel->fees_group_id = $outstandingBalanceGroupId;
                        $feesAssignModel->save();
                        $feesAssignId = $feesAssignModel->id;
                    } else {
                        $feesAssignId = $feesAssign[0]->id;
                    }

                    // Step 7: Check if Outstanding Balance already exists for this student in 2026 session
                    // Check by fees_master_id (53) and student_id in active session to avoid duplicates
                    // We check via fees_assigns to ensure it's in the correct session
                    $existingOutstanding = DB::select("
                        SELECT fees_assign_childrens.id 
                        FROM fees_assign_childrens
                        INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
                        WHERE fees_assign_childrens.fees_master_id = ?
                          AND fees_assign_childrens.student_id = ?
                          AND fees_assigns.session_id = ?
                        LIMIT 1
                    ", [$feesMasterId, $studentId, $newSessionId]);

                    // Handle positive and negative outstanding balances
                    // If negative, set remained_amount = 0 and paid_amount = abs(negative value)
                    // If positive, set remained_amount = totalOutstanding and paid_amount = 0
                    if ($totalOutstanding < 0) {
                        // Negative outstanding balance (overpayment)
                        $feesAmount = abs($totalOutstanding);
                        $remainedAmount = 0;
                        $paidAmount = $feesAmount;
                    } else {
                        // Positive outstanding balance (unpaid)
                        $feesAmount = $totalOutstanding;
                        $remainedAmount = $totalOutstanding;
                        $paidAmount = 0;
                    }

                    if (!empty($existingOutstanding)) {
                        // Update existing record - prevent duplicate entries
                        // Also update fees_assign_id in case student's class/section changed
                        DB::update("
                            UPDATE fees_assign_childrens
                            SET fees_assign_id = ?,
                                fees_amount = ?,
                                remained_amount = ?,
                                paid_amount = ?,
                                outstandingbalance = ?,
                                quater_one = 0,
                                quater_two = 0,
                                quater_three = 0,
                                quater_four = 0
                            WHERE id = ?
                        ", [
                            $feesAssignId, // Update fees_assign_id in case class/section changed
                            $feesAmount,
                            $remainedAmount,
                            $paidAmount,
                            $totalOutstanding, // Keep original value (can be negative)
                            $existingOutstanding[0]->id
                        ]);
                        $createdCount++;
                    } else {
                        // Create new fees_assign_childrens record
                        $feesAssignChild = new FeesAssignChildren();
                        $feesAssignChild->fees_assign_id = $feesAssignId;
                        $feesAssignChild->fees_master_id = $feesMasterId;
                        $feesAssignChild->student_id = $studentId;
                        $feesAssignChild->fees_amount = $feesAmount;
                        $feesAssignChild->remained_amount = $remainedAmount;
                        $feesAssignChild->paid_amount = $paidAmount;
                        $feesAssignChild->outstandingbalance = $totalOutstanding; // Keep original value (can be negative)
                        $feesAssignChild->quater_one = 0;
                        $feesAssignChild->quater_two = 0;
                        $feesAssignChild->quater_three = 0;
                        $feesAssignChild->quater_four = 0;
                        
                        // Get control number if method exists
                        $controlNumber = DB::table('fees_assign_childrens')
                            ->where('student_id', $studentId)
                            ->value('control_number');
                        $feesAssignChild->control_number = $controlNumber ?? null;
                        
                        $feesAssignChild->save();
                        $createdCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error processing student {$student->first_name} {$student->last_name} (ID: {$studentId}): " . $e->getMessage();
                    Log::error('Error creating Outstanding Balance for student', [
                        'student_id' => $studentId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Outstanding Balance for 2026 generated successfully',
                'created_count' => $createdCount,
                'skipped_count' => $skippedCount,
                'total_students' => count($studentsWithOutstanding),
                'errors' => $errors
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error generating Outstanding Balance for 2026: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error generating Outstanding Balance: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Generate Outstanding Balance for 2026 for a single student from 2025 balances
     * This function sums all outstandingbalance from all fee groups for the student in 2025
     * and creates Outstanding Balance group entry for 2026
     *
     * @param int $studentId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateOutstandingBalance2026ForStudent($studentId, Request $request)
    {
        try {
            DB::beginTransaction();

            $previousYear = 2025;
            $newYear = 2026;
            $newSessionId = setting('session');

            // Step 1: Get Outstanding Balance fees_group_id
            $outstandingBalanceGroup = DB::select("
                SELECT id FROM fees_groups WHERE name = 'Outstanding Balance' LIMIT 1
            ");
            
            if (empty($outstandingBalanceGroup)) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Outstanding Balance fees group not found'
                ], 404);
            }
            $outstandingBalanceGroupId = $outstandingBalanceGroup[0]->id;

            // Step 2: Use existing fees_master with id = 53
            $feesMasterId = 53;
            
            // Verify that fees_master 53 exists
            $feesMasterCheck = DB::select("
                SELECT id FROM fees_masters 
                WHERE id = ? 
                  AND fees_group_id = ?
                LIMIT 1
            ", [$feesMasterId, $outstandingBalanceGroupId]);

            if (empty($feesMasterCheck)) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Fees Master ID 53 not found or not for Outstanding Balance group'
                ], 404);
            }

            // Step 3: Get student's outstanding balance in 2025
            // Sum outstandingbalance from all fee groups
            // For "Outstanding Balance" group: only include if remained_amount != 0
            // For other groups: include all outstandingbalance
            // Keep negative values as negative
            $studentOutstanding = DB::select("
                SELECT 
                    students.id as student_id,
                    students.first_name,
                    students.last_name,
                    SUM(
                        CASE 
                            WHEN fees_groups.name = 'Outstanding Balance' AND fees_assign_childrens.remained_amount != 0 
                            THEN fees_assign_childrens.outstandingbalance 
                            WHEN fees_groups.name != 'Outstanding Balance'
                            THEN fees_assign_childrens.outstandingbalance
                            ELSE 0 
                        END
                    ) as total_outstanding_balance
                FROM fees_assign_childrens
                INNER JOIN students ON students.id = fees_assign_childrens.student_id
                INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
                INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
                WHERE fees_assign_childrens.student_id = ?
                  AND YEAR(fees_assign_childrens.created_at) = ?
                  AND (
                      (fees_groups.name = 'Outstanding Balance' AND fees_assign_childrens.remained_amount != 0)
                      OR (fees_groups.name != 'Outstanding Balance' AND fees_assign_childrens.outstandingbalance != 0)
                  )
                GROUP BY students.id, students.first_name, students.last_name
            ", [$studentId, $previousYear]);

            if (empty($studentOutstanding) || ($studentOutstanding[0]->total_outstanding_balance ?? 0) == 0) {
                DB::rollBack();
                return response()->json([
                    'status' => 'info',
                    'message' => 'No outstanding balance found for this student in 2025'
                ], 200);
            }

            $student = $studentOutstanding[0];
            $totalOutstanding = $student->total_outstanding_balance;

            // Get student's class and section from session_class_students for 2026
            $studentClassInfo = DB::select("
                SELECT classes_id, section_id
                FROM session_class_students
                WHERE student_id = ? AND session_id = ?
                LIMIT 1
            ", [$studentId, $newSessionId]);

            if (empty($studentClassInfo)) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => "Student not found in session {$newSessionId} (2026). Please ensure the student is enrolled for 2026."
                ], 404);
            }

            $classId = $studentClassInfo[0]->classes_id;
            $sectionId = $studentClassInfo[0]->section_id;

            // Step 4: Get or create fees_assign for Outstanding Balance group in 2026
            $feesAssign = DB::select("
                SELECT id FROM fees_assigns
                WHERE classes_id = ?
                  AND fees_group_id = ?
                  AND section_id = ?
                  AND session_id = ?
                LIMIT 1
            ", [$classId, $outstandingBalanceGroupId, $sectionId, $newSessionId]);

            $feesAssignId = null;
            if (empty($feesAssign)) {
                // Create fees_assign
                $feesAssignModel = new FeesAssign();
                $feesAssignModel->session_id = $newSessionId;
                $feesAssignModel->classes_id = $classId;
                $feesAssignModel->section_id = $sectionId;
                $feesAssignModel->fees_group_id = $outstandingBalanceGroupId;
                $feesAssignModel->save();
                $feesAssignId = $feesAssignModel->id;
            } else {
                $feesAssignId = $feesAssign[0]->id;
            }

            // Step 5: Check if Outstanding Balance already exists for this student in 2026 session
            $existingOutstanding = DB::select("
                SELECT fees_assign_childrens.id 
                FROM fees_assign_childrens
                INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
                WHERE fees_assign_childrens.fees_master_id = ?
                  AND fees_assign_childrens.student_id = ?
                  AND fees_assigns.session_id = ?
                LIMIT 1
            ", [$feesMasterId, $studentId, $newSessionId]);

            // Handle positive and negative outstanding balances
            if ($totalOutstanding < 0) {
                // Negative outstanding balance (overpayment)
                $feesAmount = abs($totalOutstanding);
                $remainedAmount = 0;
                $paidAmount = $feesAmount;
            } else {
                // Positive outstanding balance (unpaid)
                $feesAmount = $totalOutstanding;
                $remainedAmount = $totalOutstanding;
                $paidAmount = 0;
            }

            if (!empty($existingOutstanding)) {
                // Update existing record - prevent duplicate entries
                DB::update("
                    UPDATE fees_assign_childrens
                    SET fees_assign_id = ?,
                        fees_amount = ?,
                        remained_amount = ?,
                        paid_amount = ?,
                        outstandingbalance = ?,
                        quater_one = 0,
                        quater_two = 0,
                        quater_three = 0,
                        quater_four = 0
                    WHERE id = ?
                ", [
                    $feesAssignId,
                    $feesAmount,
                    $remainedAmount,
                    $paidAmount,
                    $totalOutstanding, // Keep original value (can be negative)
                    $existingOutstanding[0]->id
                ]);
                $action = 'updated';
            } else {
                // Create new fees_assign_childrens record
                $feesAssignChild = new FeesAssignChildren();
                $feesAssignChild->fees_assign_id = $feesAssignId;
                $feesAssignChild->fees_master_id = $feesMasterId;
                $feesAssignChild->student_id = $studentId;
                $feesAssignChild->fees_amount = $feesAmount;
                $feesAssignChild->remained_amount = $remainedAmount;
                $feesAssignChild->paid_amount = $paidAmount;
                $feesAssignChild->outstandingbalance = $totalOutstanding; // Keep original value (can be negative)
                $feesAssignChild->quater_one = 0;
                $feesAssignChild->quater_two = 0;
                $feesAssignChild->quater_three = 0;
                $feesAssignChild->quater_four = 0;
                
                // Get control number
                $controlNumber = DB::table('fees_assign_childrens')
                    ->where('student_id', $studentId)
                    ->value('control_number');
                $feesAssignChild->control_number = $controlNumber ?? null;
                
                $feesAssignChild->save();
                $action = 'created';
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Outstanding Balance for 2026 {$action} successfully",
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'total_outstanding_balance' => $totalOutstanding,
                'action' => $action
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error generating Outstanding Balance for 2026 for student: ' . $th->getMessage(), [
                'student_id' => $studentId,
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error generating Outstanding Balance: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk recalculate balances for all students in a given year
     * This function recalculates balances for all students who have fees in the specified year
     * 
     * This function works for any year (2025, 2026, etc.) by filtering:
     * - fees_assign_childrens by YEAR(created_at) = $year
     * - fees_collects transactions by YEAR(created_at) = $year
     * 
     * Usage:
     * - For 2025: Pass year = 2025 in request
     * - For 2026: Pass year = 2026 in request
     *
     * @param Request $request Should contain 'year' parameter (e.g., 2025, 2026)
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkRecalculateBalances(Request $request)
    {
        try {
            $year = $request->year ?? date('Y');
            
            if (empty($year)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Year is required'
                ], 400);
            }

            // Get all students with fees in the specified year
            $students = DB::select("
                SELECT DISTINCT fees_assign_childrens.student_id,
                       students.first_name,
                       students.last_name
                FROM fees_assign_childrens
                INNER JOIN students ON students.id = fees_assign_childrens.student_id
                WHERE YEAR(fees_assign_childrens.created_at) = ?
                ORDER BY students.first_name ASC, students.last_name ASC
            ", [$year]);

            if (empty($students)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No students found for year ' . $year
                ], 404);
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            // Recalculate balances for each student
            foreach ($students as $student) {
                try {
                    // Call the existing recalculateBalances function logic
                    // We'll use a simplified version that doesn't return JSON but processes the data
                    DB::beginTransaction();

                    $studentId = $student->student_id;

                    // Get all fees_assign_childrens for the student in the specified year
                    $feesAssignments = DB::select("
                        SELECT fees_assign_childrens.*,
                               fees_assigns.fees_group_id,
                               fees_groups.name as fees_group_name
                        FROM fees_assign_childrens
                        INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
                        INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
                        WHERE fees_assign_childrens.student_id = ?
                          AND YEAR(fees_assign_childrens.created_at) = ?
                        ORDER BY 
                            CASE 
                                WHEN fees_groups.name = 'Outstanding Balance' THEN 1
                                WHEN fees_groups.name = 'School Fees' THEN 2
                                WHEN fees_groups.name = 'Transportation' THEN 3
                                WHEN fees_groups.name = 'Lunch Fee' THEN 4
                                ELSE 5
                            END,
                            fees_assign_childrens.id
                    ", [$studentId, $year]);

                    // Get all transactions (excluding Admission Fee)
                    $transactions = DB::select("
                        SELECT fees_collects.*
                        FROM fees_collects
                        INNER JOIN fees_assign_childrens ON fees_assign_childrens.id = fees_collects.fees_assign_children_id
                        INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
                        INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
                        WHERE fees_assign_childrens.student_id = ?
                          AND YEAR(fees_collects.created_at) = ?
                          AND fees_groups.name != 'Admission Fee'
                        ORDER BY fees_collects.date ASC, fees_collects.created_at ASC
                    ", [$studentId, $year]);

                    // Get Admission Fee transactions separately
                    $admissionFeeTransactions = DB::select("
                        SELECT SUM(fees_collects.amount) as total_amount
                        FROM fees_collects
                        INNER JOIN fees_assign_childrens ON fees_assign_childrens.id = fees_collects.fees_assign_children_id
                        INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
                        INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
                        WHERE fees_assign_childrens.student_id = ?
                          AND YEAR(fees_collects.created_at) = ?
                          AND fees_groups.name = 'Admission Fee'
                    ", [$studentId, $year]);
                    
                    $admissionFeeTotal = $admissionFeeTransactions[0]->total_amount ?? 0;

                    // Check for excess payment from previous year
                    $excessFrom2024 = 0;
                    foreach ($feesAssignments as $assignment) {
                        if ($assignment->fees_group_name == 'Outstanding Balance') {
                            if ($assignment->outstandingbalance < 0) {
                                $excessFrom2024 = abs($assignment->outstandingbalance);
                            }
                            break;
                        }
                    }

                    // Calculate total payment amount
                    $totalPaymentAmount = 0;
                    foreach ($transactions as $transaction) {
                        $totalPaymentAmount += $transaction->amount;
                    }

                    $remainingPayment = $excessFrom2024 + $totalPaymentAmount;

                    // Reset balances
                    foreach ($feesAssignments as $assignment) {
                        $isOutstanding = $assignment->fees_group_name == 'Outstanding Balance';
                        $isAdmissionFee = $assignment->fees_group_name == 'Admission Fee';
                        
                        if ($isOutstanding) {
                            $currentOutstanding = $assignment->outstandingbalance;
                            
                            if ($currentOutstanding > 0) {
                                $resetRemained = $currentOutstanding;
                                $resetPaid = $assignment->fees_amount - $resetRemained;
                            } else {
                                $resetRemained = 0;
                                $resetPaid = abs($currentOutstanding);
                            }
                            
                            DB::update("
                                UPDATE fees_assign_childrens 
                                SET paid_amount = ?,
                                    remained_amount = ?,
                                    quater_one = 0,
                                    quater_two = 0,
                                    quater_three = 0,
                                    quater_four = 0
                                WHERE id = ?
                            ", [$resetPaid, $resetRemained, $assignment->id]);
                        } elseif ($isAdmissionFee) {
                            if ($admissionFeeTotal > 0) {
                                $newPaid = $admissionFeeTotal;
                                $newRemained = max(0, $assignment->fees_amount - $newPaid);
                            } else {
                                $newPaid = 0;
                                $newRemained = $assignment->fees_amount;
                            }
                            
                            DB::update("
                                UPDATE fees_assign_childrens 
                                SET paid_amount = ?,
                                    remained_amount = ?,
                                    quater_one = 0,
                                    quater_two = 0,
                                    quater_three = 0,
                                    quater_four = 0
                                WHERE id = ?
                            ", [$newPaid, $newRemained, $assignment->id]);
                        } else {
                            $quarterAmount = $assignment->fees_amount / 4;
                            
                            DB::update("
                                UPDATE fees_assign_childrens 
                                SET paid_amount = 0,
                                    remained_amount = fees_amount,
                                    quater_one = ?,
                                    quater_two = ?,
                                    quater_three = ?,
                                    quater_four = ?
                                WHERE id = ?
                            ", [
                                $quarterAmount,
                                $quarterAmount,
                                $quarterAmount,
                                $quarterAmount,
                                $assignment->id
                            ]);
                        }
                    }

                    // Apply to Outstanding Balance first
                    foreach ($feesAssignments as $assignment) {
                        if ($remainingPayment <= 0) break;
                        if ($assignment->fees_group_name == 'Outstanding Balance') {
                            $currentOutstanding = DB::table('fees_assign_childrens')
                                ->where('id', $assignment->id)
                                ->value('outstandingbalance');
                            
                            if ($currentOutstanding > 0) {
                                $currentRemained = DB::table('fees_assign_childrens')
                                    ->where('id', $assignment->id)
                                    ->value('remained_amount');
                                
                                $deduct = min($remainingPayment, $currentRemained);
                                $newRemained = $currentRemained - $deduct;
                                $currentPaid = DB::table('fees_assign_childrens')
                                    ->where('id', $assignment->id)
                                    ->value('paid_amount');
                                $newPaid = $currentPaid + $deduct;
                                
                                DB::update("
                                    UPDATE fees_assign_childrens 
                                    SET paid_amount = ?,
                                        remained_amount = ?
                                    WHERE id = ?
                                ", [$newPaid, $newRemained, $assignment->id]);
                                
                                $remainingPayment -= $deduct;
                            }
                        }
                    }

                    // Apply to quarters
                    $quarters = ['quater_one', 'quater_two', 'quater_three', 'quater_four'];
                    $groups = ['School Fees', 'Transportation', 'Lunch Fee'];
                    
                    foreach ($quarters as $quarter) {
                        foreach ($groups as $groupName) {
                            if ($remainingPayment <= 0) break 2;
                            
                            foreach ($feesAssignments as $assignment) {
                                if ($remainingPayment <= 0) break;
                                if ($assignment->fees_group_name == $groupName) {
                                    $currentQuarter = DB::table('fees_assign_childrens')
                                        ->where('id', $assignment->id)
                                        ->value($quarter);
                                    
                                    if ($currentQuarter > 0) {
                                        $deduct = min($remainingPayment, $currentQuarter);
                                        $newQuarter = $currentQuarter - $deduct;
                                        $currentPaid = DB::table('fees_assign_childrens')
                                            ->where('id', $assignment->id)
                                            ->value('paid_amount');
                                        $newPaid = $currentPaid + $deduct;
                                        $feesAmount = $assignment->fees_amount;
                                        $newRemained = $feesAmount - $newPaid;
                                        
                                        DB::update("
                                            UPDATE fees_assign_childrens 
                                            SET {$quarter} = ?,
                                                paid_amount = ?,
                                                remained_amount = ?
                                            WHERE id = ?
                                        ", [$newQuarter, $newPaid, $newRemained, $assignment->id]);
                                        
                                        $remainingPayment -= $deduct;
                                    }
                                }
                            }
                        }
                    }

                    // Handle excess payment
                    if ($remainingPayment > 0) {
                        if ($admissionFeeTotal > 0) {
                            $admissionFeeAssignment = null;
                            foreach ($feesAssignments as $assignment) {
                                if ($assignment->fees_group_name == 'Admission Fee') {
                                    $admissionFeeAssignment = $assignment;
                                    break;
                                }
                            }
                            
                            if ($admissionFeeAssignment) {
                                $currentRemained = DB::table('fees_assign_childrens')
                                    ->where('id', $admissionFeeAssignment->id)
                                    ->value('remained_amount');
                                
                                if ($currentRemained > 0) {
                                    $deduct = min($remainingPayment, $currentRemained);
                                    $newRemained = $currentRemained - $deduct;
                                    $currentPaid = DB::table('fees_assign_childrens')
                                        ->where('id', $admissionFeeAssignment->id)
                                        ->value('paid_amount');
                                    $newPaid = $currentPaid + $deduct;
                                    
                                    DB::update("
                                        UPDATE fees_assign_childrens 
                                        SET paid_amount = ?,
                                            remained_amount = ?
                                        WHERE id = ?
                                    ", [$newPaid, $newRemained, $admissionFeeAssignment->id]);
                                    
                                    $remainingPayment -= $deduct;
                                }
                            }
                        }
                        
                        if ($remainingPayment > 0) {
                            $schoolFeesAssignment = null;
                            foreach ($feesAssignments as $assignment) {
                                if ($assignment->fees_group_name == 'School Fees') {
                                    $schoolFeesAssignment = $assignment;
                                    break;
                                }
                            }
                            
                            if ($schoolFeesAssignment) {
                                $currentPaid = DB::table('fees_assign_childrens')
                                    ->where('id', $schoolFeesAssignment->id)
                                    ->value('paid_amount');
                                
                                $newPaid = $currentPaid + $remainingPayment;
                                
                                DB::update("
                                    UPDATE fees_assign_childrens 
                                    SET paid_amount = ?,
                                        remained_amount = 0,
                                        outstandingbalance = ?
                                    WHERE id = ?
                                ", [$newPaid, -$remainingPayment, $schoolFeesAssignment->id]);
                            }
                        }
                    }

                    // Update outstandingbalance for different groups
                    foreach ($feesAssignments as $assignment) {
                        if ($assignment->fees_group_name == 'Admission Fee') {
                            $currentRemained = DB::table('fees_assign_childrens')
                                ->where('id', $assignment->id)
                                ->value('remained_amount');
                            
                            DB::update("
                                UPDATE fees_assign_childrens 
                                SET outstandingbalance = ?
                                WHERE id = ?
                            ", [$currentRemained, $assignment->id]);
                            break;
                        }
                    }
                    
                    foreach ($feesAssignments as $assignment) {
                        if ($assignment->fees_group_name == 'Outstanding Balance' || $assignment->fees_group_name == 'Admission Fee') {
                            continue;
                        }
                        
                        $currentOutstanding = DB::table('fees_assign_childrens')
                            ->where('id', $assignment->id)
                            ->value('outstandingbalance');
                        $currentRemained = DB::table('fees_assign_childrens')
                            ->where('id', $assignment->id)
                            ->value('remained_amount');
                        
                        if ($assignment->fees_group_name == 'School Fees' && $currentOutstanding < 0) {
                            continue;
                        }
                        
                        DB::update("
                            UPDATE fees_assign_childrens 
                            SET outstandingbalance = ?
                            WHERE id = ?
                        ", [$currentRemained, $assignment->id]);
                    }

                    DB::commit();
                    $successCount++;

                } catch (\Exception $e) {
                    DB::rollBack();
                    $errorCount++;
                    $errors[] = "Error processing student {$student->first_name} {$student->last_name} (ID: {$student->student_id}): " . $e->getMessage();
                    Log::error('Error recalculating balances for student', [
                        'student_id' => $student->student_id,
                        'year' => $year,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Bulk recalculation completed',
                'year' => $year,
                'total_students' => count($students),
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ]);

        } catch (\Throwable $th) {
            Log::error('Error in bulk recalculation: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Error in bulk recalculation: ' . $th->getMessage()
            ], 500);
        }
    }

    //     public function generateSummaryExcel($class, $section, $dates, $fee_group_id)
//     {
//         $request = new Request([
//             'class'        => $class,
//             'dates'        => Crypt::decryptString($dates),
//             'section'      => $section,
//             'fee_group_id' => $fee_group_id,
//         ]);

//         $dates = explode(' - ', $request->dates);

//         // Parse start and end dates
//         $startDate = date('Y-m-d', strtotime($dates[0] ?? 'now'));
//         $endDate   = date('Y-m-d', strtotime($dates[1] ?? 'now'));

//         $groups = DB::table('fees_assign_childrens')
//             ->join('fees_assigns', 'fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
//             ->join('fees_masters', 'fees_masters.id', '=', 'fees_assign_childrens.fees_master_id')
//             ->join('fees_types', 'fees_masters.fees_type_id', '=', 'fees_types.id')
//             ->join('students', 'students.id', '=', 'fees_assign_childrens.student_id')
//             ->join('classes', 'classes.id', '=', 'fees_assigns.classes_id')
//             ->where('fees_assigns.session_id', setting('session'));

// // Apply Fee Group Filter
//         if (isset($request->fee_group_id)) {
//             if (in_array($request->fee_group_id, [1, 2, 3])) {
//                 $groups = $groups->whereExists(function ($query) use ($request) {
//                     $query->select(DB::raw(1))
//                         ->from('fees_assigns')
//                         ->whereColumn('fees_assigns.id', 'fees_assign_childrens.fees_assign_id')
//                         ->where('fees_assigns.fees_group_id', $request->fee_group_id);
//                 });
//             }
//         }

// // Only join group tables if needed (either for all groups or specific group filtering)
//         if (! isset($request->fee_group_id) || $request->fee_group_id == 0) {
//             $groups = $groups
//                 ->leftJoin('fees_assign_childrens as group1', function ($join) {
//                     $join->on('students.id', '=', 'group1.student_id')
//                         ->on('group1.fees_assign_id', '=', DB::raw('(SELECT id FROM fees_assigns WHERE fees_group_id = 1 LIMIT 1)'));
//                 })
//                 ->leftJoin('fees_assign_childrens as group2', function ($join) {
//                     $join->on('students.id', '=', 'group2.student_id')
//                         ->on('group2.fees_assign_id', '=', DB::raw('(SELECT id FROM fees_assigns WHERE fees_group_id = 2 LIMIT 1)'));
//                 })
//                 ->leftJoin('fees_assign_childrens as group3', function ($join) {
//                     $join->on('students.id', '=', 'group3.student_id')
//                         ->on('group3.fees_assign_id', '=', DB::raw('(SELECT id FROM fees_assigns WHERE fees_group_id = 3 LIMIT 1)'));
//                 });
//         }else if ( $request->fee_group_id == 1){

//         }

// // Select fields
//         $groups = $groups->select(
//             DB::raw(
//                 isset($request->fee_group_id) && in_array($request->fee_group_id, [1, 2, 3])
//                 ? 'COALESCE(SUM(fees_assign_childrens.outstandingbalance), 0) as outstanding_remained'
//                 : 'COALESCE(SUM(group1.remained_amount), 0) + COALESCE(SUM(group2.remained_amount), 0) + COALESCE(SUM(group3.remained_amount), 0) as outstanding_remained'
//             ),
//             'students.first_name',
//             'students.last_name',
//             'students.mobile',
//             'fees_assign_childrens.fees_amount',
//             'fees_assign_childrens.paid_amount',
//             'fees_assign_childrens.remained_amount',
//             'classes.name as class_name',
//             'fees_types.name as type_name',
//             'fees_assign_childrens.quater_one',
//             'fees_assign_childrens.quater_two',
//             'fees_assign_childrens.quater_three',
//             'fees_assign_childrens.quater_four'
//         );

// // Filter by Class
//         if (! empty($request->class) && $request->class != "0") {
//             $groups = $groups->where('fees_assigns.classes_id', $request->class);
//         }

// // Filter by Section
//         if (! empty($request->section) && $request->section != "0") {
//             if ($request->section == "2") {
//                 $groups = $groups->whereRaw('fees_assign_childrens.paid_amount < fees_assign_childrens.fees_amount');
//             } else {
//                 $groups = $groups->whereRaw('fees_assign_childrens.paid_amount >= fees_assign_childrens.fees_amount');
//             }
//         }

// // Filter by Date
//         if (! empty($request->dates)) {
//             $dates = explode(' - ', $request->dates);
//             if (count($dates) == 2) {
//                 $startDate = date('Y-m-d', strtotime($dates[0]));
//                 $endDate   = date('Y-m-d', strtotime($dates[1]));
//                 // $groups    = $groups->whereBetween('fees_assign_childrens.created_at', [$startDate, $endDate]);
//             }
//         }

// // Group By
//         $groups = $groups->groupBy(
//             'fees_types.name', 'classes.name', 'students.id', 'students.first_name',
//             'students.last_name', 'fees_assign_childrens.fees_amount',
//             'fees_assign_childrens.paid_amount', 'fees_assign_childrens.remained_amount',
//             'fees_assign_childrens.id', 'fees_assigns.id', 'classes.id', 'fees_types.id',
//             'fees_assign_childrens.quater_one', 'fees_assign_childrens.quater_two',
//             'fees_assign_childrens.quater_three', 'fees_assign_childrens.quater_four',
//             'students.mobile'
//         );

//         $groups->orderBy(DB::raw("CONCAT(students.first_name, ' ', students.last_name)"), 'ASC');
//         $data = $groups->get()->toArray();

//         // Prepare the data for the report
//         // $classOrder = DB::Select('select orders from classes where id = ?',[$request->class])[0]->orders;
//         if(false){
//             $reportData = $this->formatSummaryForDayCareReportData($data);
//              // Generate Excel
//         $export = new class($reportData) implements FromArray, WithHeadings, WithEvents
//         {
//             protected $data;

//             public function __construct(array $data)
//             {
//                 $this->data = $data;
//             }

//             public function array(): array
//             {
//                 return $this->data;
//             }

//             public function headings(): array
//             {
//                 return [
//                     // 'No.',
//                     'Student Name',
//                     'Mobile',
//                     'Class',
//                     'Fee Type',
//                     'Fees Amount',
//                     'Paid',
//                     'Remained',
//                     'Outstanding',
//                     'Total Remained',
//                     'Monthly',
//                     'January',
//                     'February'    ,
//                     'March'   ,
//                     'April'  ,
//                     'May'   ,
//                     'June' ,
//                     'July'  ,
//                     'August'   ,
//                     'September'   ,
//                     'October'  ,
//                     'November' ,
//                     'December'  ,
//                     'Term One',
//                     'Term Two',
//                     'Term Three',
//                     'Term Four',
                    
//                 ];
//             }

//             public function registerEvents(): array
//             {
//                 return [
//                     AfterSheet::class => function (AfterSheet $event) {
//                         $sheet = $event->sheet->getDelegate();
//                         $sheet->getStyle('A1:K1')->getFont()->setBold(true);
//                     },
//                 ];
//             }
//         };
//         }else{
//             $reportData = $this->formatSummaryReportData($data);
//              // Generate Excel
//         $export = new class($reportData) implements FromArray, WithHeadings, WithEvents
//         {
//             protected $data;

//             public function __construct(array $data)
//             {
//                 $this->data = $data;
//             }

//             public function array(): array
//             {
//                 return $this->data;
//             }

//             public function headings(): array
//             {
//                 return [
//                     // 'No.',
//                     'Student Name',
//                     'Mobile',
//                     'Class',
//                     'Fee Type',
//                     'Fees Amount',
//                     'Paid',
//                     'Remained',
//                     'Outstanding',
//                     'Total Remained',
//                     'Term One',
//                     'Term Two',
//                     'Term Three',
//                     'Term Four',
//                 ];
//             }

//             public function registerEvents(): array
//             {
//                 return [
//                     AfterSheet::class => function (AfterSheet $event) {
//                         $sheet = $event->sheet->getDelegate();
//                         $sheet->getStyle('A1:K1')->getFont()->setBold(true);
//                     },
//                 ];
//             }
//         };
//         }
        

       

//         return Excel::download($export, 'Collection_Report.xlsx');
//     }

    public function searchFeeSummary(Request $request): JsonResponse|View
    {
        $request = $this->normalizeFeesSummaryRequest($request);
        $data['classes']  = $this->classRepo->assignedAll();
        $data['sections'] = $request->class && $request->class !== '0' ? $this->classSetupRepo->getSections($request->class) : [];
        $data['result']   = $this->repo->getAllSumary($request);

        $baseQuery        = DB::table('fees_assign_childrens')
            ->join('fees_assigns', 'fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
            ->join('students', 'students.id', '=', 'fees_assign_childrens.student_id')
            ->join('classes', 'classes.id', '=', 'fees_assigns.classes_id');

        // Total fees amount
        $totalQuery = clone $baseQuery;
        $totalQuery->where('fees_assigns.fees_group_id', '!=', 1);
        if (! empty($request->class) && $request->class != "0") {
            $totalQuery->where('fees_assigns.classes_id', $request->class);
        }
        $data['total_amount'] = $totalQuery->sum('fees_amount');

        // Paid amount
        $paidQuery = clone $baseQuery;
        $paidQuery->where('fees_assigns.fees_group_id', '!=', 1);
        if (! empty($request->class) && $request->class != "0") {
            $paidQuery->where('fees_assigns.classes_id', $request->class);
        }
        $data['paid_amount'] = $paidQuery->sum('paid_amount');

        // Remaining amount
        $remainingQuery = clone $baseQuery;
        $remainingQuery->where('fees_assigns.fees_group_id', '!=', 1);
        if (! empty($request->class) && $request->class != "0") {
            $remainingQuery->where('fees_assigns.classes_id', $request->class);
        }
        $data['remained_amount'] = $remainingQuery->sum('remained_amount');

        // Paid amount for outstanding fees
        $outstandingPaidQuery = clone $baseQuery;
        $outstandingPaidQuery->where('fees_assigns.fees_group_id', '=', 1);
        if (! empty($request->class) && $request->class != "0") {
            $outstandingPaidQuery->where('fees_assigns.classes_id', $request->class);
        }
        $data['paid_amount_outstanding'] = $outstandingPaidQuery->sum('paid_amount');

        // Remaining amount for outstanding fees
        $outstandingRemainingQuery = clone $baseQuery;
        $outstandingRemainingQuery->where('fees_assigns.fees_group_id', '=', 1);
        if (! empty($request->class) && $request->class != "0") {
            $outstandingRemainingQuery->where('fees_assigns.classes_id', $request->class);
        }
        $data['remained_amount_outstanding'] = $outstandingRemainingQuery->sum('remained_amount');

        $data['request'] = $request;
        if ($request->expectsJson()) {
            return $this->feesCollectionPaginatedJsonResponse($data['result'], $this->feesSummaryMeta($request, $data));
        }
        return view('backend.report.fees-summary', compact('data'));
    }

    public function search(FeesCollectionRequest $request): JsonResponse|View
    {
        $data['result']   = $request->expectsJson() ? $this->repo->collectionReport($request) : $this->repo->search($request);
        $data['request']  = $request;
        $data['classes']  = $this->classRepo->assignedAll();
        $data['sections'] = $this->classSetupRepo->getSections($request->class);
        if ($request->expectsJson()) {
            return $this->feesCollectionPaginatedJsonResponse($data['result'], $this->feesCollectionReportMeta($request));
        }
        return view('backend.report.fees-collection', compact('data'));
    }

    public function generatePDF($class, $section, $dates)
    {
        $decodedDates = Crypt::decryptString($dates);
        $request = new Request(array_merge([
            'class'   => $class,
            'dates'   => $decodedDates === '__all__' ? '' : $decodedDates,
            'section' => $section,
        ], request()->query()));

        $data['result']    = $this->repo->collectionReport($request, false);
        $data['totals']    = $this->repo->collectionReportTotals($request);
        $data['filters']   = $this->feesCollectionReportMeta($request)['filters'];
        $data['printedby'] = Auth::user()?->name;

        $pdf = PDF::loadView('backend.report.fees-collectionPDF', compact('data'));
        return $pdf->download('fees_collection' . '_' . date('d_m_Y') . '.pdf');
    }

    public function generateExcel($class, $section, $dates)
    {
        $decodedDates = Crypt::decryptString($dates);
        $request = new Request(array_merge([
            'class'   => $class,
            'dates'   => $decodedDates === '__all__' ? '' : $decodedDates,
            'section' => $section,
        ], request()->query()));

        $rows = $this->repo->collectionReport($request, false)->values();
        $totals = $this->repo->collectionReportTotals($request);
        $reportData = $rows->map(function ($row, $index) {
            $remaining = (float) ($row->remained_amount ?? 0);

            return [
                $index + 1,
                trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')),
                $row->mobile ?? '',
                $row->class_name ?? '',
                $row->section_name ?? '',
                (float) ($row->total_fees_amount ?? 0),
                (float) ($row->paid_amount ?? 0),
                $remaining,
                $remaining > 0 ? 'With balance' : 'Paid',
            ];
        })->all();

        $reportData[] = [
            '',
            'TOTAL',
            '',
            '',
            '',
            (float) ($totals['total_fees_amount'] ?? 0),
            (float) ($totals['paid_amount'] ?? 0),
            (float) ($totals['remained_amount'] ?? 0),
            '',
        ];

        $export = new class($reportData) implements FromArray, WithHeadings, WithEvents
        {
            protected $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                return [
                    'No.',
                    'Student Name',
                    'Phone Number',
                    'Class',
                    'Section',
                    'Total Fees Assigned',
                    'Total Paid',
                    'Remaining Amount',
                    'Status',
                ];
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
                    },
                ];
            }
        };

        return Excel::download($export, 'Collection_Report.xlsx');
    }

    private function feesSummaryExportData(Request $request): array
    {
        $request = $this->normalizeFeesSummaryRequest($request);
        $result = $this->repo->getAllSumary($request);
        $rows = $result instanceof LengthAwarePaginator ? collect($result->items()) : collect($result);

        return [
            'rows' => $rows->values(),
            'totals' => $this->feesSummaryTotalsFromResult($result),
            'filters' => [
                'class' => $request->input('class', '0'),
                'section' => $request->input('section', '0'),
                'fee_group_id' => $request->input('fee_group_id', '0'),
                'amount' => $request->input('amount', ''),
                'dates' => $request->input('dates', ''),
            ],
        ];
    }

    public function generateSummaryPDF(Request $request)
    {
        $data = $this->feesSummaryExportData($request);
        $pdf = PDF::loadView('backend.report.fees-summaryPDF', compact('data'));

        return $pdf->download('fees_summary_' . date('d_m_Y') . '.pdf');
    }

    public function generateSummaryExcelReport(Request $request)
    {
        $data = $this->feesSummaryExportData($request);
        $reportRows = $data['rows']->map(function ($row, $index) {
            $status = (string) ($row->active ?? '') === '2' ? 'Shifted' : 'Active';

            return [
                $index + 1,
                trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')),
                $row->class_name ?? '',
                $row->mobile ?? '',
                $status,
                (float) ($row->outstanding_remained_amount ?? 0),
                (float) ($row->total_assigned_amount ?? 0),
                (float) ($row->fees_amount_excluding_outstanding ?? 0),
                (float) ($row->paid_from_collections ?? 0),
                (float) ($row->remained_after_collections ?? 0),
                (string) ($row->assign_comments ?? ''),
            ];
        })->all();

        $reportRows[] = [
            '',
            'TOTAL',
            '',
            '',
            '',
            (float) ($data['totals']['remained_amount_outstanding'] ?? 0),
            (float) ($data['totals']['total_assigned_amount'] ?? 0),
            (float) ($data['totals']['fees_excluding_outstanding'] ?? 0),
            (float) ($data['totals']['paid_from_collections'] ?? 0),
            (float) ($data['totals']['remained_after_collections'] ?? 0),
            '',
        ];

        $export = new class($reportRows) implements FromArray, WithHeadings, WithEvents {
            protected $rows;

            public function __construct(array $rows)
            {
                $this->rows = $rows;
            }

            public function array(): array
            {
                return $this->rows;
            }

            public function headings(): array
            {
                return [
                    'No.',
                    'Student Name',
                    'Class',
                    'Phone Number',
                    'Status',
                    'Outstanding Fee',
                    'Total Amount',
                    'Fees Amount',
                    'Paid Amount',
                    'Remained Amount',
                    'Comment',
                ];
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $event->sheet->getDelegate()->getStyle('A1:K1')->getFont()->setBold(true);
                    },
                ];
            }
        };

        return Excel::download($export, 'Fees_Summary_Report.xlsx');
    }

  public function generateSummaryExcel($class, $section, $dates, $fee_group_id,$amount = null)
    {
        $amount = $amount ?? 0; 
        $request = new Request([
            'class'        => $class,
            'dates'        => Crypt::decryptString($dates),
            'section'      => $section,
            'fee_group_id' => $fee_group_id,
            'amount'  => $amount
        ]);

        $dates = explode(' - ', $request->dates);

        // Parse start and end dates
        $startDate = date('Y-m-d', strtotime($dates[0] ?? 'now'));
        $endDate   = date('Y-m-d', strtotime($dates[1] ?? 'now'));

 

         $groups = DB::table('students')
    ->select(
        'students.id',
        'students.first_name',
        'students.last_name',
        'students.mobile',
        // Total fees amount from fees_assign_childrens for 2026
        DB::raw('COALESCE(SUM(fees_assign_childrens.fees_amount), 0) AS fees_amount'),
        // Total paid amount from fees_collects for 2026 (using subquery to avoid duplication)
        DB::raw('COALESCE((
            SELECT SUM(fc.amount)
            FROM fees_collects fc
            WHERE fc.student_id = students.id
            AND YEAR(fc.created_at) = 2026
        ), 0) AS paid_amount'),
        // Outstanding fee: sum of fees_amount from group 1 (Outstanding Balance)
        DB::raw('COALESCE(SUM(CASE WHEN fees_assigns.fees_group_id = 1 THEN fees_assign_childrens.fees_amount ELSE 0 END), 0) AS outstanding_amount'),
        // Group 3 amount for fee_group_id filter
        DB::raw('COALESCE(SUM(CASE WHEN fees_assigns.fees_group_id = 3 THEN fees_assign_childrens.fees_amount ELSE 0 END), 0) AS group3_amount'),
        'classes.name as class_name'
    )
    ->join('session_class_students', function($join) {
        $join->on('session_class_students.student_id', '=', 'students.id')
             ->where('session_class_students.session_id', setting('session'));
    })
    ->join('classes', 'classes.id', '=', 'session_class_students.classes_id')
    ->join('fees_assign_childrens', function ($join) {
        $join->on('fees_assign_childrens.student_id', '=', 'students.id')
             ->whereRaw('YEAR(fees_assign_childrens.created_at) = 2026');
    })
    ->join('fees_assigns', function ($join) {
        $join->on('fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
             ->where('fees_assigns.session_id', setting('session'));
    });

// 🔍 Filter by Class
if (!empty($request->class) && $request->class != "0") {
    $groups->where('session_class_students.classes_id', $request->class);
}

if (!empty($request->fee_group_id) && $request->fee_group_id == "3") {
    $groups->havingRaw('group3_amount > 0');
}

$groups->whereNotIn('students.status', ['0']);

// 🔍 Filter by Section
if (!empty($request->section) && $request->section != "0") {
    if ($request->section == "2") {
        $groups->havingRaw('paid_amount < fees_amount');
    } else {
        $groups->havingRaw('paid_amount >= fees_amount');
    }
}

if (!empty($request->amount) && $request->amount != "0") {
    $groups->havingRaw('(fees_amount - paid_amount) > ?', [$request->amount]);
}

// 🔍 Filter by Date (only if fee_group_id is not 1 or 2)
if ($request->fee_group_id != 1 && $request->fee_group_id != 2 && $request->fee_group_id != 3) {
    if (!empty($request->dates)) {
        $dates = explode(' - ', $request->dates);
        if (count($dates) == 2) {
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
            // $groups->whereBetween('group2.created_at', [$startDate, $endDate]);
        }
    }
}

// 📦 Group & Paginate
$groups = $groups
    ->groupBy(
        'students.id',
        'students.first_name',
        'students.last_name',
        'students.mobile',
        'classes.name'
    )
    ->get();


        $data = $groups->sortBy(function ($item) {
    return strtolower(
        $item->class_name . '|' .
        $item->first_name . '|' .
        $item->last_name
    );
    })->values()->toArray();

        


      
            $reportData = $this->formatSummaryReportDataNalopa($data);
             // Generate Excel
        $export = new class($reportData) implements FromArray, WithHeadings, WithEvents
        {
            protected $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                return [
                    'Student Name',
                    'Class',
                    'Phone Number',
                    'Status',
                    'Outstanding Fee',
                    'Total Amount',
                    'Fees Amount',
                    'Paid Amount',
                    'Remained Amount',
                ];
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
                    },
                ];
            }
        };
        
        

       

        return Excel::download($export, 'Collection_Report.xlsx');


    }
     public function generateSummaryExcelNalopa($class, $section, $dates, $fee_group_id,$amount)
    {
        $request = new Request([
            'class'        => $class,
            'dates'        => Crypt::decryptString($dates),
            'section'      => $section,
            'fee_group_id' => $fee_group_id,
            'amount'  => $amount
        ]);

        $dates = explode(' - ', $request->dates);

        // Parse start and end dates
        $startDate = date('Y-m-d', strtotime($dates[0] ?? 'now'));
        $endDate   = date('Y-m-d', strtotime($dates[1] ?? 'now'));

 

         $groups = DB::table('students')
    ->select(
        'students.id',
        'students.first_name',
        'students.last_name',
        // Total fees amount from fees_assign_childrens for 2026
        DB::raw('COALESCE(SUM(fees_assign_childrens.fees_amount), 0) AS fees_amount'),
        // Total paid amount from fees_collects for 2026 (using subquery to avoid duplication)
        DB::raw('COALESCE((
            SELECT SUM(fc.amount)
            FROM fees_collects fc
            WHERE fc.student_id = students.id
            AND YEAR(fc.created_at) = 2026
        ), 0) AS paid_amount'),
        // Outstanding fee: sum of fees_amount from group 1 (Outstanding Balance)
        DB::raw('COALESCE(SUM(CASE WHEN fees_assigns.fees_group_id = 1 THEN fees_assign_childrens.fees_amount ELSE 0 END), 0) AS outstanding_amount'),
        // Group 3 amount for fee_group_id filter
        DB::raw('COALESCE(SUM(CASE WHEN fees_assigns.fees_group_id = 3 THEN fees_assign_childrens.fees_amount ELSE 0 END), 0) AS group3_amount'),
        'classes.name as class_name'
    )
    ->join('session_class_students', function($join) {
        $join->on('session_class_students.student_id', '=', 'students.id')
             ->where('session_class_students.session_id', setting('session'));
    })
    ->join('classes', 'classes.id', '=', 'session_class_students.classes_id')
    ->join('fees_assign_childrens', function ($join) {
        $join->on('fees_assign_childrens.student_id', '=', 'students.id')
             ->whereRaw('YEAR(fees_assign_childrens.created_at) = 2026');
    })
    ->join('fees_assigns', function ($join) {
        $join->on('fees_assigns.id', '=', 'fees_assign_childrens.fees_assign_id')
             ->where('fees_assigns.session_id', setting('session'));
    })
    ->whereRaw('LOWER(classes.name) != ?', ['completed']);

// 🔍 Filter by Class
if (!empty($request->class) && $request->class != "0") {
    $groups->where('session_class_students.classes_id', $request->class);
}

if (!empty($request->fee_group_id) && $request->fee_group_id == "3") {
    $groups->havingRaw('group3_amount > 0');
}

// 🔍 Filter by Section
if (!empty($request->section) && $request->section != "0") {
    if ($request->section == "2") {
        $groups->havingRaw('paid_amount < fees_amount');
    } else {
        $groups->havingRaw('paid_amount >= fees_amount');
    }
}

if (!empty($request->amount) && $request->amount != "0") {
    $groups->havingRaw('(fees_amount - paid_amount) >= ?', [$request->amount]);
}

// 🔍 Filter by Date (only if fee_group_id is not 1 or 2)
if ($request->fee_group_id != 1 && $request->fee_group_id != 2 && $request->fee_group_id != 3) {
    if (!empty($request->dates)) {
        $dates = explode(' - ', $request->dates);
        if (count($dates) == 2) {
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
            // $groups->whereBetween('group2.created_at', [$startDate, $endDate]);
        }
    }
}

// 📦 Group & Paginate
$groups = $groups
    ->groupBy(
        'students.id',
        'students.first_name',
        'students.last_name',
        'classes.name'
    )
    ->get();

        // Filter out "completed" classes
        $groups = $groups->filter(function ($item) {
            $className = strtolower(trim($item->class_name ?? ''));
            return $className !== 'completed';
        });

        // Track student names to identify duplicates (using full name)
        $studentNameCounts = [];
        foreach ($groups as $item) {
            $fullName = trim(($item->first_name ?? '') . ' ' . ($item->last_name ?? ''));
            $fullName = strtolower($fullName);
            $studentNameCounts[$fullName] = ($studentNameCounts[$fullName] ?? 0) + 1;
        }

        // Add duplicate status to each record based on name
        foreach ($groups as $item) {
            $fullName = trim(($item->first_name ?? '') . ' ' . ($item->last_name ?? ''));
            $fullName = strtolower($fullName);
            $item->is_duplicate = ($studentNameCounts[$fullName] ?? 0) > 1 ? 'Duplicate' : '';
        }

        // Sort by class name first, then by student name (first name, then last name)
        $data = $groups->sortBy([
            function ($item) {
                return strtolower($item->class_name ?? '');
            },
            function ($item) {
                return strtolower($item->first_name ?? '');
            },
            function ($item) {
                return strtolower($item->last_name ?? '');
            }
        ])->values()->toArray();

        


        // Prepare the data for the report
       if($request->fee_group_id == 1){
            $reportData = $this->formatSummaryReportDataOut($data);
             // Generate Excel
        $export = new class($reportData) implements FromArray, WithHeadings, WithEvents
        {
            protected $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                return [
                    'Student Name',
                    'Class',
                    'Status',
                    'Fees Amount',
                    'Paid Amount',
                    'Remained',
                ];
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
                    },
                ];
            }
        };
        }else{
            $reportData = $this->formatSummaryReportDataNalopa($data);
             // Generate Excel
        $export = new class($reportData) implements FromArray, WithHeadings, WithEvents
        {
            protected $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                return [
                    'Student Name',
                    'Class',
                    'Student Type',
                    'Status',
                    'Outstanding Fee',
                    'Total Amount',
                    'Fees Amount',
                    'Paid Amount',
                    'Remained Amount',
                ];
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
                    },
                ];
            }
        };
        }
        

       

        return Excel::download($export, 'Collection_Report.xlsx');


    }

  

    private function formatReportData($data)
    {
        $formattedData       = [];
        $previousStudentName = '';
        $totalSchoolFees     = 0;
        $totalTransportFees  = 0;

        foreach ($data as $key => $row) {
            $formattedData[] = [
                'No.'                         => $row->full_name === $previousStudentName ? '' : $key + 1,
                'Student Name'                => $row->full_name === $previousStudentName ? '' : $row->full_name,
                'Year'                        => $row->year,
                'Class'                       => $this->changeClass($row->class_name),
                'Status'                      => 'day',
                'Receipt Date'                => $row->a_updated_at,
                'Q'                           => '',
                'Fees Per Year'               => number_format($row->fees_amount, 2, '.', ','),
                'q'                           => '',
                'Paid Amount (TZS)'           => number_format($row->paid_amount, 2, '.', ','),
                'Outstanding Amount (2024)'   => number_format($row->amount, 2, '.', ','),
                'Remaining Amount (TZS) 2025' => '',
                'Phone Number'                => $row->mobile,
                'Reconciliation With Bank'    => '',
            ];

            // Update the previous student name to the current one
            $previousStudentName = $row->full_name;

            // Sum totals for fees
            if ($row->fees_type == 'School Fees') {
                $totalSchoolFees += $row->paid_amount;
            } elseif ($row->fees_type == 'Transport Fees') {
                $totalTransportFees += $row->paid_amount;
            }

            $formattedData[] = [
                'No.'                         => '',
                'Student Name'                => '',
                'Year'                        => '2025',
                'Class'                       => $row->class_name,
                'Status'                      => 'day',
                'Receipt Date'                => $row->b_updated_at,
                'Q'                           => '',
                'Fees Per Year'               => number_format($row->b_fees_amount, 2, '.', ','),
                ''                            => '',
                'Paid Amount (TZS)'           => '',
                'Outstanding Amount (2024)'   => '',
                'Remaining Amount (TZS) 2025' => '',
                'Phone Number'                => '',
                'Reconciliation With Bank'    => '',
            ];

            $formattedData[] = [
                'No.'                         => '',
                'Student Name'                => '',
                'Year'                        => '',
                'Class'                       => '',
                'Status'                      => '',
                'Receipt Date'                => '',
                'Q'                           => 'q1',
                'Fees Per Year'               => number_format($row->quater_amount, 2, '.', ','),
                'q'                           => 'q1',
                'Paid Amount (TZS)'           => number_format($row->quater_amount - $row->quater_one, 2, '.', ','),
                'Outstanding Amount (2024)'   => '',
                'Remaining Amount (TZS) 2025' => number_format($row->quater_one, 2, '.', ','),
                'Phone Number'                => '',
                'Reconciliation With Bank'    => '',
            ];

            $formattedData[] = [
                'No.'                         => '',
                'Student Name'                => '',
                'Year'                        => '',
                'Class'                       => '',
                'Status'                      => '',
                'Receipt Date'                => '',
                'Q'                           => 'q2',
                'Fees Per Year'               => number_format($row->quater_amount, 2, '.', ','),
                'q'                           => 'q2',
                'Paid Amount (TZS)'           => number_format($row->quater_amount - $row->quater_two, 2, '.', ','),
                'Outstanding Amount (2024)'   => '',
                'Remaining Amount (TZS) 2025' => number_format($row->quater_two, 2, '.', ','),
                'Phone Number'                => '',
                'Reconciliation With Bank'    => '',
            ];

            $formattedData[] = [
                'No.'                         => '',
                'Student Name'                => '',
                'Year'                        => '',
                'Class'                       => '',
                'Status'                      => '',
                'Receipt Date'                => '',
                'Q'                           => 'q3',
                'Fees Per Year'               => number_format($row->quater_amount, 2, '.', ','),
                'q'                           => 'q3',
                'Paid Amount (TZS)'           => number_format($row->quater_amount - $row->quater_three, 2, '.', ','),
                'Outstanding Amount (2024)'   => '',
                'Remaining Amount (TZS) 2025' => number_format($row->quater_three, 2, '.', ','),
                'Phone Number'                => '',
                'Reconciliation With Bank'    => '',
            ];

            $formattedData[] = [
                'No.'                         => '',
                'Student Name'                => '',
                'Year'                        => '',
                'Class'                       => '',
                'Status'                      => '',
                'Receipt Date'                => '',
                'Q'                           => 'q4',
                'Fees Per Year'               => number_format($row->quater_amount, 2, '.', ','),
                'q'                           => 'q4',
                'Paid Amount (TZS)'           => number_format($row->quater_amount - $row->quater_four, 2, '.', ','),
                'Outstanding Amount (2024)'   => '',
                'Remaining Amount (TZS) 2025' => number_format($row->quater_four, 2, '.', ','),
                'Phone Number'                => '',
                'Reconciliation With Bank'    => '',
            ];
            // Append totals
            $formattedData[] = [
                'No.'                         => '',
                'Student Name'                => 'TOTAL SCHOOL FEES',
                'Year'                        => '',
                'Class'                       => '',
                'Status'                      => '',
                'Receipt Date'                => '',
                'Q'                           => '',
                'Fees Per Year'               => '',
                'q'                           => '',
                'Paid Amount (TZS)'           => number_format($row->b_paid_amount, 2, '.', ','),
                'Outstanding Amount (2024)'   => '',
                'Remaining Amount (TZS) 2025' => number_format($row->b_amount, 2, '.', ','),
                'Phone Number'                => '',
                'Reconciliation With Bank'    => '',
            ];

        }

        $formattedData[] = [
            'No.'                         => '',
            'Student Name'                => 'TOTAL SCHOOL FEES',
            'Year'                        => '',
            'Class'                       => '',
            'Status'                      => '',
            'Receipt Date'                => '',
            'Q'                           => '',
            'Fees Per Year'               => '',
            'q'                           => '',
            'Paid Amount (TZS)'           => $totalSchoolFees,
            'Outstanding Amount (2024)'   => '',
            'Remaining Amount (TZS) 2025' => '',
            'Phone Number'                => '',
            'Reconciliation With Bank'    => '',
        ];

        return $formattedData;
    }

    private function formatSummaryReportDataNalopa($data)
    {
        $formattedData       = [];
        $totalSchoolFees     = 0;
        $totalPaid           = 0;
        $totalRemained       = 0;
        $totalOutstanding    = 0;

        foreach ($data as $key => $row) {
            $name = trim($row->first_name . ' ' . $row->last_name);
            
            // Determine student type (Day or Boarding) - default to Day if not specified
            $studentType = ' '; // You can modify this logic based on your student_category or other field
            
            // Get duplicate status
            $status = $row->is_duplicate ?? '';
            
            // Calculate amounts
            $outstandingFee = $row->outstanding_amount ?? 0;
            $feeAmount2026 = $row->fees_amount ?? 0; // This already includes outstanding
            $paidAmount = $row->paid_amount ?? 0;
            
            // Total Amount = fees_amount (already includes outstanding + 2026 fees)
            $totalAmount = $feeAmount2026;
            // Fees Amount = 2026 Fees only (Total Amount - Outstanding)
            $feesAmount = $feeAmount2026 - $outstandingFee;
            // Remained Amount = Total Amount - Paid Amount
            $remainedAmount = $totalAmount - $paidAmount;
            
            // Format with separate columns
            $formattedData[] = [
                'Student Name' => $name,
                'Class' => $row->class_name ?? '',
         'Phone Number' => !empty($row->mobile)
    ? (preg_replace('/^(?:\+?255)/', '0', (string) $row->mobile))
    : '',
                'Status' => $status,
                'Outstanding Fee' => number_format($outstandingFee, 0, '.', ','),
                'Total Amount' => number_format($totalAmount, 0, '.', ','),
                'Fees Amount' => number_format($feesAmount, 0, '.', ','),
                'Paid Amount' => number_format($paidAmount, 0, '.', ','),
                'Remained Amount' => number_format($remainedAmount, 0, '.', ','),
            ];
            
            $totalSchoolFees += $feeAmount2026;
            $totalPaid += $paidAmount;
            $totalOutstanding += $outstandingFee;
        }

        // Calculate totals
        $totalTotalAmount = $totalSchoolFees; // fees_amount already includes outstanding
        $totalFeesAmount = $totalSchoolFees - $totalOutstanding; // Fees without outstanding
        $totalRemained = $totalTotalAmount - $totalPaid;

        // Add totals row
        $formattedData[] = [
            'Student Name' => 'TOTAL',
            'Class' => '',
            'Phone Number' => '',
            'Status' => '',
            'Outstanding Fee' => number_format($totalOutstanding, 0, '.', ','),
            'Total Amount' => number_format($totalTotalAmount, 0, '.', ','),
            'Fees Amount' => number_format($totalFeesAmount, 0, '.', ','),
            'Paid Amount' => number_format($totalPaid, 0, '.', ','),
            'Remained Amount' => number_format($totalRemained, 0, '.', ','),
        ];

        return $formattedData;
    }

    private function formatSummaryReportData($data)
    {
        $formattedData       = [];
        $previousStudentName = '';
        $totalSchoolFees     = 0;
        $totalPaid           = 0;
        $totalRemained       = 0;
        $totalOutstanding    = 0;
        $totalPaidOutstanding    = 0;
        $totalRemainedOutstanding    = 0;
        $totalTransportFees = 0;
        $totalPaidTransportFees = 0;
        $totalRemainedTransportFees = 0;
        $totalTotalRemained  = 0;
        $termOne = 0;
        $termTwo = 0;

        foreach ($data as $key => $row) {
            $totaTotalRemained = $row->remained_amount;
            $name              = $row->first_name . ' ' . $row->last_name;
            $row->type_name = "Fees";
            // dd($data);
            $formattedData[]   = [
                'Student Name'   => $name === $previousStudentName ? '' : $name,
                'Class'          => $name === $previousStudentName ? '' : $row->class_name,
                'Section'       => $row->section_name,
                'Fees Amount'    =>  number_format($row->fees_amount, 2, '.', ','),
                'Paid Amount'           => number_format(($row->paid_amount), 2, '.', ','),
                'Remained'       =>  number_format($row->remained_amount, 2, '.', ','),
                'Outstanding'    => number_format($row->outstanding_amount, 2, '.', ','),
                'Paid Outstanding'    => number_format($row->outstanding_paid_amount, 2, '.', ','),
                'Remained Outstanding'    => number_format($row->outstanding_remained_amount, 2, '.', ','),
                   'Transport'    => number_format($row->group3_amount, 2, '.', ','),
                'Paid Transport'    => number_format($row->group3_paid_amount, 2, '.', ','),
                'Remained Transport'    => number_format($row->group3_remained_amount, 2, '.', ','),
                'Total Remained' =>  number_format(($row->outstanding_remained_amount + $row->remained_amount+$row->group3_remained_amount), 2, '.', ','),
                'Remained Term One'     => $row->type_name == "Outstanding Balance Fee"
                 ? '0': $row->quater_one + $row->quater_two ,
                'Remained Term Two'     =>$row->type_name == "Outstanding Balance Fee"
                 ? number_format($row->remained_amount, 2, '.', ',') : $row->quater_three +  $row->quater_four ,
            ];
            $previousStudentName = $name;
            $termOne_ = $row->quater_one + $row->quater_two;
            $termTwo_ = $row->quater_three + $row->quater_four;
            $totalSchoolFees += $row->fees_amount;
            $totalPaid += $row->paid_amount;
            $totalRemained += $row->remained_amount;
            $totalOutstanding += $row->outstanding_amount;
            $totalPaidOutstanding += $row->outstanding_paid_amount;
             $totalRemainedOutstanding += $row->outstanding_remained_amount;
             $totalTransportFees += $row->group3_amount;
             $totalPaidTransportFees += $row->group3_paid_amount;
             $totalRemainedTransportFees += $row->group3_remained_amount;
            $totalTotalRemained += $totaTotalRemained;
            $termOne += $termOne_;
            $termTwo += $termTwo_;

        }

        $formattedData[] = [
            'Student Name'   => 'TOTAL SCHOOL FEES',
            'Class'          => '',
            'Section'       => '',
            'Fees Amount'    => number_format($totalSchoolFees, 2, '.', ','),
            'Paid Amount'           => number_format($totalPaid, 2, '.', ','),
            'Remained'       => number_format($totalRemained, 2, '.', ','),
            'Outstanding'    => number_format($totalOutstanding, 2, '.', ','),
            'Paid Outstanding'    => number_format($totalPaidOutstanding, 2, '.', ','),
            'Remained Outstanding'    => number_format($totalRemainedOutstanding, 2, '.', ','),
            'Transport'    => number_format($totalTransportFees, 2, '.', ','),
            'Paid Transport'    => number_format($totalPaidTransportFees, 2, '.', ','),
            'Remained Transport'    => number_format($totalRemainedTransportFees, 2, '.', ','),
            'Total Remained' => number_format($totalTotalRemained, 2, '.', ','),
            'Remained Term One'     => number_format($termOne, 2, '.', ','),
            'Remained Term Two'     => number_format($termTwo, 2, '.', ','),
        ];

        return $formattedData;
    }

      private function formatSummaryReportDataOut($data)
    {
        $formattedData       = [];
        $previousStudentName = '';
        $totalSchoolFees     = 0;
        $totalPaid           = 0;
        $totalRemained       = 0;
        $totalOutstanding    = 0;
        $totalTotalRemained  = 0;
        $termOne = 0;
        $termTwo = 0;

        foreach ($data as $key => $row) {
           
            $totaTotalRemained = 
                     $row->remained_amount;
            $name              = $row->first_name . ' ' . $row->last_name;
          
            // Get duplicate status
            $status = $row->is_duplicate ?? '';
               
            $formattedData[]   = [
                'Student Name'   => $name === $previousStudentName ? '' : $name,
                'Class'          => $name === $previousStudentName ? '' : $row->class_name,
                'Status'         => $status,
                'Fees Amount'    => number_format($row->outstanding_amount, 2, '.', ',') ,
                 'Paid Amount'           =>number_format($row->outstanding_paid_amount, 2, '.', ','),
                'Remained'       =>  number_format($row->outstanding_remained_amount, 2, '.', ','),
               
                
            ];
            $previousStudentName = $name;
            $termOne_ = $row->quater_one + $row->quater_two;
            $termTwo_ = $row->quater_three + $row->quater_four;
            $totalSchoolFees += $row->outstanding_amount;
            $totalPaid += $row->outstanding_paid_amount;
            $totalRemained += $row->outstanding_remained_amount;
            $totalTotalRemained += $totaTotalRemained;
            $termOne += $termOne_;
            $termTwo += $termTwo_;

        }

        $formattedData[] = [
            'Student Name'   => 'TOTAL SCHOOL FEES',
            'Class'          => '',
            'Status'         => '',
            'Fees Amount'    => number_format($totalSchoolFees, 2, '.', ','),
            'Paid Amount'           => number_format($totalPaid, 2, '.', ','),
            'Remained'       => number_format($totalRemained, 2, '.', ','),
        ];
             


        return $formattedData;
    }

         private function formatSummaryReportDataOutNariva($data)
    {
        $formattedData       = [];
        $previousStudentName = '';
        $totalSchoolFees     = 0;
        $totalPaid           = 0;
        $totalRemained       = 0;
        $totalOutstanding    = 0;
        $totalTotalRemained  = 0;
        $termOne = 0;
        $termTwo = 0;

        foreach ($data as $key => $row) {
           
            $totaTotalRemained = 
                     $row->remained_amount;
            $name              = $row->first_name . ' ' . $row->last_name;
          
               
            $formattedData[]   = [
                'Student Name'   => $name === $previousStudentName ? '' : $name,
                'Class'          => $name === $previousStudentName ? '' : $row->class_name,
                'Fees Amount'    => number_format($row->outstanding_amount, 2, '.', ',') ,
                 'Paid Amount'           =>number_format($row->outstanding_paid_amount, 2, '.', ','),
                'Remained'       =>  number_format($row->outstanding_remained_amount, 2, '.', ','),
               
                
            ];
            $previousStudentName = $name;
            $termOne_ = $row->quater_one + $row->quater_two;
            $termTwo_ = $row->quater_three + $row->quater_four;
            $totalSchoolFees += $row->outstanding_amount;
            $totalPaid += $row->outstanding_paid_amount;
            $totalRemained += $row->outstanding_remained_amount;
            $totalTotalRemained += $totaTotalRemained;
            $termOne += $termOne_;
            $termTwo += $termTwo_;

        }

        $formattedData[] = [
            'Student Name'   => 'TOTAL SCHOOL FEES',
            'Class'          => '',
            'Fees Amount'    => number_format($totalSchoolFees, 2, '.', ','),
            'Paid Amount'           => number_format($totalPaid, 2, '.', ','),
            'Remained'       => number_format($totalRemained, 2, '.', ','),
        ];
             


        return $formattedData;
    }

    private function formatSummaryForDayCareReportData($data)
    {
        $formattedData       = [];
        $previousStudentName = '';
        $totalSchoolFees     = 0;
        $totalPaid           = 0;
        $totalRemained       = 0;
        $totalOutstanding    = 0;
        $totalTotalRemained  = 0;

        foreach ($data as $key => $row) {
            $totaTotalRemained = $row->outstanding_remained + $row->remained_amount;
            $name              = $row->first_name . ' ' . $row->last_name;
            $monthlyFee = $row->fees_amount / 12;
            $remainingBalance = $row->paid_amount;
            $monthsPaid = [];

            for ($i = 0; $i < 12; $i++) {
                if ($remainingBalance >= $monthlyFee) {
                    $monthsPaid[$i] = number_format($monthlyFee, 2, '.', ',');
                    $remainingBalance -= $monthlyFee;
                } else {
                    $monthsPaid[$i] = number_format($remainingBalance, 2, '.', ',');
                    $remainingBalance = 0;
                }
            }

            $formattedData[] = [
                'Student Name'   => $name === $previousStudentName ? '' : $name,
                'Mobile'         => $name === $previousStudentName ? '' : $row->mobile,
                'Class'          => $name === $previousStudentName ? '' : $row->class_name,
                'Fee Type'       => $row->type_name,
                'Fees Amount'    => number_format($row->fees_amount, 2, '.', ','),
                'Paid'           => number_format($row->paid_amount, 2, '.', ','),
                'Remained'       => number_format($row->remained_amount, 2, '.', ','),
                'Outstanding'    => number_format($row->outstanding_remained, 2, '.', ','),
                'Total Remained' => number_format($totaTotalRemained, 2, '.', ','),
                'Monthly'        => number_format($monthlyFee, 2, '.', ','),
                'January'        => $monthsPaid[0] ?? '',
                'February'       => $monthsPaid[1] ?? '',
                'March'          => $monthsPaid[2] ?? '',
                'April'          => $monthsPaid[3] ?? '',
                'May'            => $monthsPaid[4] ?? '',
                'June'           => $monthsPaid[5] ?? '',
                'July'           => $monthsPaid[6] ?? '',
                'August'         => $monthsPaid[7] ?? '',
                'September'      => $monthsPaid[8] ?? '',
                'October'        => $monthsPaid[9] ?? '',
                'November'       => $monthsPaid[10] ?? '',
                'December'       => $monthsPaid[11] ?? '',
                'Term One'     => $row->quater_one + $row->quater_two,
                'Term Two'     => $row->quater_three + $row->quater_four,
            ];
            $previousStudentName = $name;

            $totalSchoolFees += $row->fees_amount;
            $totalPaid += $row->paid_amount;
            $totalRemained += $row->remained_amount;
            $totalOutstanding += $row->outstanding_remained;
            $totalTotalRemained += $totaTotalRemained;

        }

        $formattedData[] = [
            'Student Name'   => 'TOTAL SCHOOL FEES',
            'Mobile'         => '',
            'Class'          => '',
            'Fee Type'       => '',
            'Fees Amount'    => number_format($totalSchoolFees, 2, '.', ','),
            'Paid'           => number_format($totalPaid, 2, '.', ','),
            'Remained'       => number_format($totalRemained, 2, '.', ','),
            'Outstanding'    => number_format($totalOutstanding, 2, '.', ','),
            'Total Remained' => number_format($totalTotalRemained, 2, '.', ','),
            'Monthly'        => '',
            'January'        => '',
            'February'        => '',
            'March'        => '',
            'April'     => '',
            'May'     => '',
            'June'   => '',
            'July'    => '',
            'August'     => '',
            'September'     => '',
            'October'   => '',
            'November'    => '',
            'December'    => '',
            'Quater One'     => '',
            'Quater Two'     => '',
            'Quater Three'   => '',
            'Quater Four'    => '',
        ];

        return $formattedData;
    }

    public function changeClass($className)
    {

        if ($className == 'Baby Class') {
            return "Baby Class";
        } else if ($className == 'Middle Class') {
            return "Baby Class";
        } else if ($className == 'PRE UNIT CLASS') {
            return "Middle Class";
        } else if ($className == 'CLASS I') {
            return "PRE UNIT CLASS";
        } else if ($className == 'CLASS II') {
            return "CLASS I";
        } else if ($className == 'CLASS III') {
            return "CLASS II";
        } else if ($className == 'CLASS IV') {
            return "CLASS III";
        } else if ($className == 'CLASS V') {
            return "CLASS IV";
        } else if ($className == 'CLASS VI') {
            return "CLASS V";
        } else if ($className == 'CLASS VII') {
            return "CLASS VI";
        }
    }

    public function generateStudentsPDF(Request $request)
    {
        $data = [
            'rows' => $this->studentsReportRows($request, false)->values(),
        ];
        $data['totals'] = ['students_count' => $data['rows']->count()];
        $pdf = PDF::loadView('backend.report.studentsPDF', compact('data'));

        return $pdf->download('students_report_' . date('d_m_Y') . '.pdf');
    }

    public function generateStudentsExcel(Request $request)
    {
        // Define the database fields and their custom column headings
        $columns = [
            'id'              => 'No.',
            'full_name'       => 'Student Name',
            'class_name'      => 'Class',
            'section_name'      => 'Section',
            'guardian_email' => 'Guardian Email',
            'guardian_mobile' => 'Phone Number',
            'residance_address' => 'Current Location',
            'student_address' =>'Previous Location'
            
        ];

        $data = $this->studentsReportRows($request, false)
            ->values()
            ->map(function ($row, $index) {
                return [
                    'id' => $index + 1,
                    'full_name' => trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')),
                    'class_name' => $row->class_name ?? '',
                    'section_name' => $row->section_name ?? '',
                    'guardian_email' => $row->guardian_email ?? '',
                    'guardian_mobile' => $row->guardian_mobile ?? $row->mobile ?? '',
                    'residance_address' => $row->residance_address ?? '',
                    'student_address' => $row->student_address ?? '',
                ];
            })
            ->all();

        $export = new class($data, $columns) implements FromArray, WithHeadings, WithEvents
        {
            protected $data;
            protected $columns;

            public function __construct(array $data, array $columns)
            {
                $this->data    = $data;
                $this->columns = $columns;
            }

            public function array(): array
            {
                // Convert objects to arrays and only include specified columns
                return array_map(function ($item) {
                    return (array) $item;
                }, $this->data);
            }

            public function headings(): array
            {
                // Return the custom column headings
                return array_values($this->columns);
            }

            public function registerEvents(): array
            {
                // Apply sheet protection before export
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->getSheet(); // Accessing the sheet

                    },
                ];
            }
        };

        return Excel::download($export, 'Students Report.xlsx');
    }

    /**
     * Display boarding students report with school fees by year
     *
     * @return \Illuminate\View\View
     */
    public function boardingStudents(Request $request): JsonResponse|View
    {
        $data = $this->boardingStudentsData($request->year);
        
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['result'], 'meta' => $this->boardingStudentsMeta($data)]);
        }
        return view('backend.report.boarding-students', compact('data'));
    }

    /**
     * Search boarding students report by year
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function searchBoardingStudents(Request $request): JsonResponse|View
    {
        $data = $this->boardingStudentsData($request->year);
        $data['request'] = $request;
        
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['result'], 'meta' => $this->boardingStudentsMeta($data)]);
        }
        return view('backend.report.boarding-students', compact('data'));
    }

    public function generateBoardingStudentsPDF(Request $request)
    {
        $data = $this->boardingStudentsData($request->year);
        $pdf = PDF::loadView('backend.report.boarding-studentsPDF', compact('data'))->setPaper('a4', 'landscape');

        return $pdf->download('boarding_students_' . date('d_m_Y') . '.pdf');
    }

    public function generateBoardingStudentsExcel(Request $request)
    {
        $data = $this->boardingStudentsData($request->year);
        $rows = collect($data['result'])->map(function ($item) {
            return [
                trim(($item->first_name ?? '') . ' ' . ($item->last_name ?? '')),
                $item->admission_no ?? '',
                $item->year ?? '',
                $item->class_name ?? '',
                $item->section_name ?? '',
                (float) ($item->school_fees_amount ?? 0),
                (float) ($item->school_fees_paid ?? 0),
                (float) ($item->school_fees_remained ?? 0),
                (float) ($item->school_fees_outstanding ?? 0),
            ];
        })->values()->all();

        $export = new class($rows) implements FromArray, WithHeadings, WithEvents {
            protected $rows;

            public function __construct(array $rows)
            {
                $this->rows = $rows;
            }

            public function array(): array
            {
                return $this->rows;
            }

            public function headings(): array
            {
                return ['Student', 'Admission No.', 'Year', 'Class', 'Section', 'School Fees Amount', 'Paid Amount', 'Remained Amount', 'Outstanding Balance'];
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $event->sheet->getDelegate()->getStyle('A1:I1')->getFont()->setBold(true);
                    },
                ];
            }
        };

        return Excel::download($export, 'Boarding_Students_Report.xlsx');
    }

    private function boardingStudentsData($year = null): array
    {
        $selectedYear = $year ?: date('Y');
        $bindings = [$selectedYear];

        $data['result'] = DB::select("
            SELECT 
                students.id as student_id,
                students.first_name,
                students.last_name,
                students.admission_no,
                YEAR(fees_assign_childrens.created_at) as year,
                MAX(classes.name) as class_name,
                MAX(sections.name) as section_name,
                SUM(CASE WHEN fees_groups.name = 'School Fees' THEN fees_assign_childrens.fees_amount ELSE 0 END) as school_fees_amount,
                SUM(CASE WHEN fees_groups.name = 'School Fees' THEN fees_assign_childrens.paid_amount ELSE 0 END) as school_fees_paid,
                SUM(CASE WHEN fees_groups.name = 'School Fees' THEN fees_assign_childrens.remained_amount ELSE 0 END) as school_fees_remained,
                SUM(CASE WHEN fees_groups.name = 'School Fees' THEN fees_assign_childrens.outstandingbalance ELSE 0 END) as school_fees_outstanding
            FROM students
            INNER JOIN fees_assign_childrens ON fees_assign_childrens.student_id = students.id
            INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
            INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
            LEFT JOIN session_class_students ON session_class_students.student_id = students.id 
                AND session_class_students.session_id = fees_assigns.session_id
            LEFT JOIN classes ON classes.id = session_class_students.classes_id
            LEFT JOIN sections ON sections.id = session_class_students.section_id
            WHERE (students.category_id = 1 OR students.student_category_id = 1)
              AND fees_groups.name = 'School Fees'
              AND YEAR(fees_assign_childrens.created_at) = ?
            GROUP BY students.id, students.first_name, students.last_name, students.admission_no, YEAR(fees_assign_childrens.created_at)
            ORDER BY YEAR(fees_assign_childrens.created_at) DESC, students.first_name ASC, students.last_name ASC
        ", $bindings);

        $data['years'] = DB::select("
            SELECT DISTINCT YEAR(fees_assign_childrens.created_at) as year
            FROM students
            INNER JOIN fees_assign_childrens ON fees_assign_childrens.student_id = students.id
            INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
            INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
            WHERE (students.category_id = 1 OR students.student_category_id = 1)
              AND fees_groups.name = 'School Fees'
            ORDER BY year DESC
        ");

        $data['selected_year'] = $selectedYear;

        return $data;
    }

    private function boardingStudentsMeta(array $data): array
    {
        $year = $data['selected_year'] ?? date('Y');

        return array_merge($data, [
            'totals' => [
                'students_count' => count($data['result'] ?? []),
                'school_fees_amount' => collect($data['result'] ?? [])->sum('school_fees_amount'),
                'school_fees_paid' => collect($data['result'] ?? [])->sum('school_fees_paid'),
                'school_fees_remained' => collect($data['result'] ?? [])->sum('school_fees_remained'),
                'school_fees_outstanding' => collect($data['result'] ?? [])->sum('school_fees_outstanding'),
            ],
            'pdf_download_url' => route('report-boarding-students.pdf-generate', [], false) . '?' . http_build_query(['year' => $year]),
            'excel_download_url' => route('report-boarding-students.excel-generate', [], false) . '?' . http_build_query(['year' => $year]),
        ]);
    }

    /**
     * Update school fees to 2,200,000.00 for all boarding students in 2026
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBoardingSchoolFees2026(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $newFeesAmount = 2200000.00;
            $quarterAmount = 550000.00; // 550,000.00 per quarter (explicitly set to avoid floating point issues)
            $year = 2026;
            
            Log::info('Boarding School Fees Update - Starting', [
                'new_fees_amount' => $newFeesAmount,
                'quarter_amount' => $quarterAmount,
                'year' => $year
            ]);
            
            // Boarding students with School Fees for the active session / year filter
            $assignments = DB::select("
                SELECT fees_assign_childrens.id,
                       fees_assign_childrens.fees_amount,
                       fees_assign_childrens.paid_amount,
                       fees_assign_childrens.remained_amount,
                       fees_assign_childrens.outstandingbalance,
                       students.id as student_id,
                       students.first_name,
                       students.last_name,
                       fees_assigns.session_id
                FROM fees_assign_childrens
                INNER JOIN students ON students.id = fees_assign_childrens.student_id
                INNER JOIN fees_assigns ON fees_assigns.id = fees_assign_childrens.fees_assign_id
                INNER JOIN fees_groups ON fees_groups.id = fees_assigns.fees_group_id
                WHERE (students.category_id = 1 OR students.student_category_id = 1)
                  AND fees_groups.name = 'School Fees'
                  AND YEAR(fees_assign_childrens.created_at) = ?
                  AND fees_assigns.session_id = ?
            ", [$year, setting('session')]);
            
            Log::info('Boarding School Fees Update - Found records', [
                'year' => $year,
                'total_records' => count($assignments),
                'sample_record' => !empty($assignments) ? [
                    'id' => $assignments[0]->id ?? null,
                    'current_fees_amount' => $assignments[0]->fees_amount ?? null,
                    'student_id' => $assignments[0]->student_id ?? null
                ] : null
            ]);
            
            $updatedCount = 0;
            $errors = [];
            
            foreach ($assignments as $assignment) {
                try {
                    // Calculate new remained_amount = new fees_amount - existing paid_amount
                    $newRemainedAmount = $newFeesAmount - $assignment->paid_amount;
                    
                    // For School Fees: Set outstandingbalance to 0 (only Outstanding Balance group uses this column)
                    // Do NOT update outstandingbalance for School Fees - keep it at 0
                    
                    // Update the fees assignment
                    DB::update("
                        UPDATE fees_assign_childrens 
                        SET fees_amount = ?,
                            quater_one = ?,
                            quater_two = ?,
                            quater_three = ?,
                            quater_four = ?,
                            remained_amount = ?,
                            outstandingbalance = 0
                        WHERE id = ?
                    ", [
                        $newFeesAmount,
                        $quarterAmount,
                        $quarterAmount,
                        $quarterAmount,
                        $quarterAmount,
                        $newRemainedAmount,
                        $assignment->id
                    ]);
                    
                    $updatedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Student ID {$assignment->student_id} ({$assignment->first_name} {$assignment->last_name}): " . $e->getMessage();
                    Log::error("Error updating boarding student fees: " . $e->getMessage(), [
                        'student_id' => $assignment->student_id,
                        'assignment_id' => $assignment->id
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'School fees updated successfully for boarding students in 2026',
                'total_found' => count($assignments),
                'updated_count' => $updatedCount,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating boarding school fees: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update school fees: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find all students who had School Fees in 2025 but are missing School Fees for 2026
     *
     * @return \Illuminate\View\View
     */
    public function findMissingBoardingStudents2026(Request $request): JsonResponse|View
    {
        $activeSessionId = setting('session');
        // Students with School Fees in 2025 but no School Fees row for current session (2026 year in assign_children)
        $missingStudents = DB::select("
            SELECT DISTINCT
                students.id as student_id,
                students.first_name,
                students.last_name,
                students.admission_no,
                classes_2026.name as class_2026,
                classes_2026.id as class_id_2026,
                sections_2026.name as section_2026,
                sections_2026.id as section_id_2026
            FROM students
            INNER JOIN fees_assign_childrens fac_2025 ON fac_2025.student_id = students.id
            INNER JOIN fees_assigns fa_2025 ON fa_2025.id = fac_2025.fees_assign_id
            INNER JOIN fees_groups fg_2025 ON fg_2025.id = fa_2025.fees_group_id
                AND fg_2025.name = 'School Fees'
            INNER JOIN session_class_students scs_2026 ON scs_2026.student_id = students.id
                AND scs_2026.session_id = ?
            INNER JOIN classes classes_2026 ON classes_2026.id = scs_2026.classes_id
            INNER JOIN sections sections_2026 ON sections_2026.id = scs_2026.section_id
            LEFT JOIN (
                SELECT DISTINCT fac.student_id
                FROM fees_assign_childrens fac
                INNER JOIN fees_assigns fa ON fa.id = fac.fees_assign_id
                INNER JOIN fees_groups fg ON fg.id = fa.fees_group_id
                    AND fg.name = 'School Fees'
                WHERE YEAR(fac.created_at) = 2026
                  AND fa.session_id = ?
            ) has_school_fees_2026 ON has_school_fees_2026.student_id = students.id
            WHERE YEAR(fac_2025.created_at) = 2025
              AND has_school_fees_2026.student_id IS NULL
            ORDER BY students.first_name ASC, students.last_name ASC
        ", [$activeSessionId, $activeSessionId]);
        
        Log::info('Missing School Fees 2026 - Query Results', [
            'total_found' => count($missingStudents),
            'sample' => !empty($missingStudents) ? $missingStudents[0] : null
        ]);

        $data['missing_students'] = $missingStudents;
        $data['total_missing'] = count($missingStudents);
        
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['missing_students'], 'meta' => ['total_missing' => $data['total_missing']]]);
        }
        return view('backend.report.missing-boarding-students-2026', compact('data'));
    }

    /**
     * Create School Fees for missing students for 2026
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createMissingBoardingSchoolFees2026(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $newFeesAmount = 2200000.00;
            $quarterAmount = 550000.00;
            $sessionId2026 = setting('session');
            $schoolFeesGroupId = 2; // School Fees group_id is 2
            $year = 2026;
            
            // Find missing students - ALL students who had School Fees in 2025 but missing for 2026
            // Filter ONLY by School Fees - no boarding/day filters
            $missingStudents = DB::select("
                SELECT DISTINCT
                    students.id as student_id,
                    students.first_name,
                    students.last_name,
                    classes_2026.id as class_id_2026,
                    sections_2026.id as section_id_2026
                FROM students
                -- Check they had School Fees in 2025 (filter by fees_group name = 'School Fees' only)
                INNER JOIN fees_assign_childrens fac_2025 ON fac_2025.student_id = students.id
                INNER JOIN fees_assigns fa_2025 ON fa_2025.id = fac_2025.fees_assign_id
                INNER JOIN fees_groups fg_2025 ON fg_2025.id = fa_2025.fees_group_id
                    AND fg_2025.name = 'School Fees'
                -- Check they have 2026 class assignment
                INNER JOIN session_class_students scs_2026 ON scs_2026.student_id = students.id
                    AND scs_2026.session_id = ?
                INNER JOIN classes classes_2026 ON classes_2026.id = scs_2026.classes_id
                INNER JOIN sections sections_2026 ON sections_2026.id = scs_2026.section_id
                -- Check they DON'T have School Fees for 2026
                LEFT JOIN (
                    SELECT DISTINCT fac.student_id
                    FROM fees_assign_childrens fac
                    INNER JOIN fees_assigns fa ON fa.id = fac.fees_assign_id
                    INNER JOIN fees_groups fg ON fg.id = fa.fees_group_id
                        AND fg.name = 'School Fees'
                    WHERE YEAR(fac.created_at) = ?
                      AND fa.session_id = ?
                ) has_school_fees_2026 ON has_school_fees_2026.student_id = students.id
                WHERE YEAR(fac_2025.created_at) = 2025
                  AND has_school_fees_2026.student_id IS NULL
            ", [$sessionId2026, $year, $sessionId2026]);
            
            Log::info('Create Missing School Fees 2026 - Query Results', [
                'total_found' => count($missingStudents),
                'session_id' => $sessionId2026,
                'year' => $year
            ]);
            
            $createdCount = 0;
            $errors = [];
            
            foreach ($missingStudents as $student) {
                try {
                    // Get or create fees_assign for School Fees for this class/section in 2026
                    $feesAssign = DB::select("
                        SELECT id 
                        FROM fees_assigns 
                        WHERE session_id = ? 
                          AND classes_id = ? 
                          AND section_id = ? 
                          AND fees_group_id = ?
                        LIMIT 1
                    ", [$sessionId2026, $student->class_id_2026, $student->section_id_2026, $schoolFeesGroupId]);
                    
                    $feesAssignId = null;
                    if (empty($feesAssign)) {
                        // Create fees_assign
                        $feesAssignModel = new FeesAssign();
                        $feesAssignModel->session_id = $sessionId2026;
                        $feesAssignModel->classes_id = $student->class_id_2026;
                        $feesAssignModel->section_id = $student->section_id_2026;
                        $feesAssignModel->fees_group_id = $schoolFeesGroupId;
                        $feesAssignModel->save();
                        $feesAssignId = $feesAssignModel->id;
                    } else {
                        $feesAssignId = $feesAssign[0]->id;
                    }
                    
                    // Get fees_master_id for School Fees based on class
                    // Find fees_master for School Fees (fees_group_id = 2) for this class
                    $feesMaster = DB::select("
                        SELECT fees_masters.id
                        FROM fees_masters
                        INNER JOIN fees_types ON fees_types.id = fees_masters.fees_type_id
                        WHERE fees_masters.fees_group_id = ?
                          AND fees_types.class_id = ?
                          AND fees_masters.session_id = ?
                        LIMIT 1
                    ", [$schoolFeesGroupId, $student->class_id_2026, $sessionId2026]);
                    
                    if (empty($feesMaster)) {
                        $errors[] = "Student ID {$student->student_id} ({$student->first_name} {$student->last_name}): No fees_master found for class ID {$student->class_id_2026}";
                        continue;
                    }
                    
                    $feesMasterId = $feesMaster[0]->id;
                    
                    // Check if fees_assign_childrens already exists (shouldn't, but double-check)
                    $existing = DB::select("
                        SELECT id 
                        FROM fees_assign_childrens 
                        WHERE fees_assign_id = ? 
                          AND fees_master_id = ? 
                          AND student_id = ?
                        LIMIT 1
                    ", [$feesAssignId, $feesMasterId, $student->student_id]);
                    
                    if (!empty($existing)) {
                        $errors[] = "Student ID {$student->student_id} ({$student->first_name} {$student->last_name}): School Fees already exists";
                        continue;
                    }
                    
                    // Get control number
                    $controlNumber = DB::select("SELECT control_number FROM students WHERE id = ?", [$student->student_id]);
                    $controlNumber = !empty($controlNumber) ? $controlNumber[0]->control_number : null;
                    
                    // Create fees_assign_childrens
                    DB::insert("
                        INSERT INTO fees_assign_childrens 
                        (fees_assign_id, fees_master_id, student_id, fees_amount, paid_amount, remained_amount, outstandingbalance, quater_one, quater_two, quater_three, quater_four, control_number, created_at, updated_at)
                        VALUES (?, ?, ?, ?, 0, ?, 0, ?, ?, ?, ?, ?, NOW(), NOW())
                    ", [
                        $feesAssignId,
                        $feesMasterId,
                        $student->student_id,
                        $newFeesAmount,
                        $newFeesAmount,
                        $quarterAmount,
                        $quarterAmount,
                        $quarterAmount,
                        $quarterAmount,
                        $controlNumber
                    ]);
                    
                    $createdCount++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Student ID {$student->student_id} ({$student->first_name} {$student->last_name}): " . $e->getMessage();
                    Log::error("Error creating School Fees for boarding student: " . $e->getMessage(), [
                        'student_id' => $student->student_id,
                        'class_id' => $student->class_id_2026 ?? null
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'School Fees created successfully for missing students in 2026',
                'total_found' => count($missingStudents),
                'created_count' => $createdCount,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating School Fees for missing students: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create School Fees: ' . $e->getMessage()
            ], 500);
        }
    }

}
