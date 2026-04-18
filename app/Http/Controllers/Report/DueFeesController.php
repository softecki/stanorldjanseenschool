<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\DueFeesRequest;
use App\Repositories\Academic\ClassesRepository;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\StudentInfo\StudentRepository;
use App\Repositories\Examination\ExamAssignRepository;
use App\Repositories\Report\DueFeesRepository;
use App\Repositories\Report\MeritListRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use PDF;

class DueFeesController extends Controller
{
    private $repo;
    private $examAssignRepo;
    private $classRepo;
    private $classSetupRepo;
    private $studentRepo;

    function __construct(
        DueFeesRepository    $repo,
        ExamAssignRepository   $examAssignRepo,
        ClassesRepository      $classRepo,
        ClassSetupRepository   $classSetupRepo,
        StudentRepository      $studentRepo,
    ) 
    {
        $this->repo               = $repo;
        $this->examAssignRepo     = $examAssignRepo;
        $this->classRepo          = $classRepo;
        $this->classSetupRepo     = $classSetupRepo;
        $this->studentRepo        = $studentRepo;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['classes']            = $this->classRepo->assignedAll();
        $data['sections']           = [];
        $data['fees_masters']       = $this->repo->assignedFeesTypes();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return view('backend.report.due-fees', compact('data'));
    }

    public function search(DueFeesRequest $request): JsonResponse|View
    {
        $data['result']       = $this->repo->search($request);
        $data['request']      = $request;
        $data['classes']      = $this->classRepo->assignedAll();
        $data['fees_masters'] = $this->repo->assignedFeesTypes();
        $data['sections']     = $this->classSetupRepo->getSections($request->class);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['result'] ?? [], 'meta' => $data]);
        }
        return view('backend.report.due-fees', compact('data'));
    }
    
    public function generatePDF(Request $request)
    {
        $request = new Request([
            'class'        => $request->class,
            'section'      => $request->section,
            'fees_master'  => $request->type,
        ]);

        $data['result']       = $this->repo->searchPDF($request);
        
        $pdf = PDF::loadView('backend.report.due-feesPDF', compact('data'));
        return $pdf->download('due_fees'.'_'.date('d_m_Y').'.pdf');
    }
}
