<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Academic\ClassesRepository;
use App\Repositories\Report\MarksheetRepository;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\Examination\ExamTypeRepository;
use App\Repositories\StudentInfo\StudentRepository;
use App\Http\Requests\Report\Marksheet\SearchRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Facades\Excel;

class MarksheetController extends Controller
{
    private $repo;
    private $classRepo;
    private $classSetupRepo;
    private $studentRepo;
    private $examTypeRepo;

    function __construct(
        MarksheetRepository    $repo,
        ClassesRepository      $classRepo,
        ClassSetupRepository   $classSetupRepo,
        StudentRepository      $studentRepo,
        ExamTypeRepository     $examTypeRepo,
    ) 
    {
        $this->repo               = $repo;
        $this->classRepo          = $classRepo;
        $this->classSetupRepo     = $classSetupRepo;
        $this->studentRepo        = $studentRepo;
        $this->examTypeRepo       = $examTypeRepo;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['classes']            = $this->classRepo->assignedAll();
        $data['sections']           = [];
        $data['students']           = [];
        $data['exam_types']         = $this->examTypeRepo->all();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return view('backend.report.marksheet', compact('data'));
    }

    public function getStudents(Request $request){
        return $this->studentRepo->getStudents($request);
    }

    public function sections($class): JsonResponse
    {
        return response()->json([
            'data' => $class ? $this->classSetupRepo->getSections($class) : [],
        ]);
    }

    public function search(SearchRequest $request): JsonResponse|View
    {
        $data['student']      = $this->studentRepo->show($request->student);
        $data['resultData']   = $this->repo->search($request);
        $data['request']      = $request;
        $data['classes']      = $this->classRepo->assignedAll();
        $data['sections']     = $this->classSetupRepo->getSections($request->class);
        $data['students']     = $this->studentRepo->getStudents($request);
        $data['exam_types']   = $this->examTypeRepo->all();
        
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['resultData'] ?? [],
                'meta' => array_merge($data, [
                    'pdf_download_url' => route('report-marksheet.pdf-generate', [
                        'id' => $request->student,
                        'type' => $request->exam_type,
                        'class' => $request->class,
                        'section' => $request->section,
                    ], false),
                    'excel_download_url' => route('report-marksheet.excel-generate', [
                        'id' => $request->student,
                        'type' => $request->exam_type,
                        'class' => $request->class,
                        'section' => $request->section,
                    ], false),
                ]),
            ]);
        }
        return view('backend.report.marksheet', compact('data'));
    }

    public function generatePDF($id, $type, $class, $section)
    {
        $request = new Request([
            'student'   => $id,
            'exam_type' => $type,
            'class'     => $class,
            'section'   => $section,
        ]);

        $data['student']      = $this->studentRepo->show($request->student);
        $data['resultData']   = $this->repo->search($request);
        
        
        $pdf = PDF::loadView('backend.report.marksheetPDF', compact('data'));
        return $pdf->download('marksheet'.'_'.date('d_m_Y').'_'.@$data['student']->first_name .'_'. @$data['student']->last_name .'.pdf');
    }

    public function generateExcel($id, $type, $class, $section)
    {
        $request = new Request([
            'student'   => $id,
            'exam_type' => $type,
            'class'     => $class,
            'section'   => $section,
        ]);

        $data = $this->repo->search($request);
        $rows = collect($data['marks_registers'] ?? [])->map(function ($item) {
            return [
                optional($item->subject)->name,
                collect($item->marksRegisterChilds ?? [])->sum('mark'),
            ];
        })->values()->all();

        $rows[] = ['Total Marks', $data['total_marks'] ?? 0];
        $rows[] = ['Average Marks', $data['avg_marks'] ?? 0];
        $rows[] = ['Result', $data['result'] ?? ''];
        $rows[] = ['GPA', $data['gpa'] ?? ''];
        $rows[] = ['Position', ($data['position'] ?? 0) . ' / ' . ($data['max_position'] ?? 0)];

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
                return ['Subject / Summary', 'Value'];
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $event->sheet->getDelegate()->getStyle('A1:B1')->getFont()->setBold(true);
                    },
                ];
            }
        };

        return Excel::download($export, 'Marksheet_Report.xlsx');
    }
}
