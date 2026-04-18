<?php

namespace App\Http\Controllers\StudentPanel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StudentInfo\SessionClassStudent;
use App\Models\StudentInfo\Student;
use App\Repositories\Examination\ExamAssignRepository;
use App\Repositories\Report\MarksheetRepository as ReportMarksheetRepository;
use App\Repositories\StudentInfo\StudentRepository;
use App\Repositories\StudentPanel\MarksheetRepository;
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

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['exam_types']   = $this->typeRepo->getExamType($this->repo->index());
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => 'Marksheet']]);
        }
        return redirect()->to(spa_url('student-panel/marksheet'));
    }

    public function search(Request $request): JsonResponse|RedirectResponse
    {
        $data                   = $this->repo->search($request);
        $data['exam_types']     = $this->typeRepo->assignedExamType();
        $data['request']        = $request;

        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => 'Marksheet']]);
        }
        return redirect()->to(spa_url('student-panel/marksheet'));
    }

    public function generatePDF($type)
    {
        $student        = Student::where('id', request()->filled('student_id') ? request('student_id') : @auth()->user()->student->id)->first();
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
