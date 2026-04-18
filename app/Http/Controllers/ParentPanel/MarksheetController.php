<?php

namespace App\Http\Controllers\ParentPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentInfo\SessionClassStudent;
use App\Models\StudentInfo\Student;
use App\Repositories\Report\ExamRoutineRepository;
use App\Repositories\ParentPanel\MarksheetRepository;
use App\Repositories\Report\MarksheetRepository as ReportMarksheetRepository;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Examination\ExamAssignRepository;
use App\Repositories\StudentInfo\StudentRepository;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use PDF;

class MarksheetController extends Controller
{
    private $repo;
    private $typeRepo;
    private $reportMarksheetRepo;
    private $studentRepo;

    function __construct(
        MarksheetRepository $repo, 
        ExamAssignRepository $typeRepo,
        ReportMarksheetRepository $reportMarksheetRepo, 
        StudentRepository $studentRepo,
    ) 
    { 
        $this->repo = $repo; 
        $this->typeRepo = $typeRepo;
        $this->reportMarksheetRepo = $reportMarksheetRepo; 
        $this->studentRepo = $studentRepo;
    }

    public function getExamTypes(Request $request)
    {
        return $this->typeRepo->getExamType($this->repo->studentInfo($request->id)); // student id
    }

    public function getExamTypesForApp()
    {
        return $this->typeRepo->all(); 
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data                 = $this->repo->index();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => 'Marksheet']]);
        }
        return redirect()->to(spa_url('parent-panel/marksheet'));
    }

    public function search(Request $request): JsonResponse|RedirectResponse
    {
        $data               = $this->repo->search($request);
        $data['request']    = $request;
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => 'Marksheet']]);
        }
        return redirect()->to(spa_url('parent-panel/marksheet'));
    }

    public function searchForApp(Request $request)
    {
        $data               = $this->repo->search($request);
        $data['request']    = $request;
        return $this->responseWithSuccess(___('alert.login_successfully'), [
            'data'          => $data
        ]);
    }

    public function generatePDF($student, $type)
    {
        $student        = Student::where('id', $student)->first();
        $classSection   = SessionClassStudent::where('session_id', setting('session'))
                        ->where('student_id', @$student->id)
                        ->first();

        $request = new Request([
            'student'   => @$student->id,
            'exam_type' => $type,
            'class'     => $classSection->classes_id,
            'section'   => $classSection->section_id,
        ]);

        $data['student']      = $this->studentRepo->show(@$student->id);
        $data['resultData']   = $this->reportMarksheetRepo->search($request);
        
        $pdf = PDF::loadView('backend.report.marksheetPDF', compact('data'));
        return $pdf->download('marksheet'.'_'.date('d_m_Y').'_'.@$data['student']->first_name .'_'. @$data['student']->last_name .'.pdf');
    }
}
