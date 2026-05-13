<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\MeritListRequest;
use App\Interfaces\Academic\ShiftInterface;
use App\Repositories\Academic\ClassesRepository;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\StudentInfo\StudentRepository;
use App\Repositories\Examination\ExamAssignRepository;
use App\Repositories\Examination\ExamTypeRepository;
use App\Repositories\Report\MeritListRepository;
// use PDF;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Facades\Excel;

class MeritListController extends Controller
{
    private $repo;
    private $classRepo;
    private $classSetupRepo;
    private $studentRepo;
    private $examTypeRepo;
    private $shiftRepo;

    function __construct(
        MeritListRepository    $repo,
        ClassesRepository      $classRepo,
        ClassSetupRepository   $classSetupRepo,
        StudentRepository      $studentRepo,
        ExamTypeRepository     $examTypeRepo,
        ShiftInterface         $shiftRepo,
    ) 
    {
        $this->repo               = $repo;
        $this->classRepo          = $classRepo;
        $this->classSetupRepo     = $classSetupRepo;
        $this->studentRepo        = $studentRepo;
        $this->examTypeRepo       = $examTypeRepo;
        $this->shiftRepo          = $shiftRepo; 
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['classes']            = $this->classRepo->assignedAll();
        $data['sections']           = [];
//        $data['exam_types']         = [];
        $data['exam_types']   = $this->examTypeRepo->all();
        $data['shifts']             = $this->shiftRepo->all();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return view('backend.report.merit-list', compact('data'));
    }

    public function sections($class): JsonResponse
    {
        return response()->json([
            'data' => $class ? $this->classSetupRepo->getSections($class) : [],
        ]);
    }

    public function search(MeritListRequest $request): JsonResponse|View
    {
        // $data['resultData']   = $this->repo->search($request);
        $data['resultData']   = $this->repo->generateResultSheet($request);
        $data['request']      = $request;
        $data['classes']      = $this->classRepo->assignedAll();
        $data['sections']     = $this->classSetupRepo->getSections($request->class);
        $data['exam_types']   = $this->examTypeRepo->all();
        $data['shifts']       = $this->shiftRepo->all();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['resultData'] ?? [],
                'meta' => array_merge($data, [
                    'pdf_download_url' => route('report-merit-list.pdf-generate', [
                        'type' => $request->exam_type,
                        'class' => $request->class,
                        'section' => $request->section ?: 0,
                    ], false),
                    'excel_download_url' => route('report-merit-list.excel-generate', [
                        'type' => $request->exam_type,
                        'class' => $request->class,
                        'section' => $request->section ?: 0,
                    ], false),
                ]),
            ]);
        }
        return view('backend.report.merit-list', compact('data'));
    }


    
    public function generatePDF($type, $class, $section)
    {
        $request = new Request([
            'exam_type' => $type,
            'class'     => $class,
            'section'   => $section,
        ]);

        // $data['resultData']   = $this->repo->searchPDF($request);
        $data['request']      = $request;
        $data['classes']      = $this->classRepo->assignedAll();
        $data['sections']     = $this->classSetupRepo->getSections($request->class);
        $data['exam_types']   = $this->examTypeRepo->all();
        $data['shifts']       = $this->shiftRepo->all();
        $data['resultData']   = $this->repo->generateResultSheet($request);
        
        $pdf = PDF::loadView('backend.report.merit-listResultPDF', compact('data'))->setPaper('a4', 'landscape');
        return $pdf->download('merit_list'.'_'.date('d_m_Y').'.pdf');
    }

    public function generateExcel($type, $class, $section)
    {
        $request = new Request([
            'exam_type' => $type,
            'class'     => $class,
            'section'   => $section,
        ]);

        $resultData = $this->repo->generateResultSheet($request);
        $subjects = $resultData['subjects'] ?? [];
        $rows = collect($resultData['results'] ?? [])->map(function ($item) use ($subjects) {
            $row = [
                $item['name'] ?? '',
                $item['position'] ?? '',
            ];
            foreach ($subjects as $subject) {
                $row[] = $item['subjects'][$subject] ?? '-';
            }
            $row[] = $item['total'] ?? 0;
            $row[] = $item['average'] ?? 0;
            $row[] = $item['grade'] ?? '';

            return $row;
        })->values()->all();

        $headings = array_merge(['Student', 'Position'], $subjects, ['Total', 'Average', 'Grade']);

        $export = new class($rows, $headings) implements FromArray, WithHeadings, WithEvents {
            protected $rows;
            protected $headings;

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

        return Excel::download($export, 'Merit_List_Report.xlsx');
    }
}
