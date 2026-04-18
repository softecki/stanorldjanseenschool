<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Academic\ClassesRepository;
use App\Repositories\Report\MarksheetRepository;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\StudentInfo\StudentRepository;
use App\Http\Requests\Report\Marksheet\SearchRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class MarksheetController extends Controller
{
    private $repo;
    private $classRepo;
    private $classSetupRepo;
    private $studentRepo;

    function __construct(
        MarksheetRepository    $repo,
        ClassesRepository      $classRepo,
        ClassSetupRepository   $classSetupRepo,
        StudentRepository      $studentRepo,
    ) 
    {
        $this->repo               = $repo;
        $this->classRepo          = $classRepo;
        $this->classSetupRepo     = $classSetupRepo;
        $this->studentRepo        = $studentRepo;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['classes']            = $this->classRepo->assignedAll();
        $data['sections']           = [];
        $data['students']           = [];
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return view('backend.report.marksheet', compact('data'));
    }

    public function getStudents(Request $request){
        return $this->studentRepo->getStudents($request);
    }

    public function search(SearchRequest $request): JsonResponse|View
    {
        $data['student']      = $this->studentRepo->show($request->student);
        $data['resultData']   = $this->repo->search($request);
        $data['request']      = $request;
        $data['classes']      = $this->classRepo->assignedAll();
        $data['sections']     = $this->classSetupRepo->getSections($request->class);
        $data['students']     = $this->studentRepo->getStudents($request);
        
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['resultData'] ?? [], 'meta' => $data]);
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
}
