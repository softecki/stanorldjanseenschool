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
            return response()->json(['data' => $data['resultData'] ?? [], 'meta' => $data]);
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
        
        $pdf = PDF::loadView('backend.report.merit-listPDF', compact('data'));
        return $pdf->download('merit_list'.'_'.date('d_m_Y').'.pdf');
    }
}
