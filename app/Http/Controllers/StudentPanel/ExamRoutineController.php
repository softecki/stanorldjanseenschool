<?php

namespace App\Http\Controllers\StudentPanel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Examination\ExamAssign;
use App\Models\StudentInfo\SessionClassStudent;
use App\Models\StudentInfo\Student;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Examination\ExamAssignRepository;
use App\Repositories\Report\ExamRoutineRepository as ReportExamRoutineRepository;
use App\Repositories\StudentPanel\ExamRoutineRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use PDF;

class ExamRoutineController extends Controller
{
    private $reportExamRoutineRepo;
    private $repo;
    private $typeRepo;

    function __construct(ReportExamRoutineRepository  $reportExamRoutineRepo, ExamRoutineRepository $repo, ExamAssignRepository $typeRepo,) 
    { 
        $this->reportExamRoutineRepo    = $reportExamRoutineRepo; 
        $this->repo         = $repo; 
        $this->typeRepo     = $typeRepo;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['exam_types']   = $this->typeRepo->getExamType($this->repo->index());
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => 'Exam routine']]);
        }
        return redirect()->to(spa_url('student-panel/exam-routine'));
    }

    public function search(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->repo->search($request);
        $data['exam_types']   = $this->typeRepo->getExamType($this->repo->index());
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => 'Exam routine']]);
        }
        return redirect()->to(spa_url('student-panel/exam-routine'));
    }

    public function generatePDF($type)
    {
        $student        = Student::where('user_id', Auth::user()->id)->first();
        $classSection   = SessionClassStudent::where('session_id', setting('session'))
                        ->where('student_id', @$student->id)
                        ->first();

        $request = new Request([
            'class'        => $classSection->classes_id,
            'section'      => $classSection->section_id,
            'type'         => $type,
        ]);

        $data['result']       = $this->reportExamRoutineRepo->search($request);
        $data['time']         = $this->reportExamRoutineRepo->time($request);
        
        $pdf = PDF::loadView('backend.report.exam-routinePDF', compact('data'));
        return $pdf->download('exam_routine'.'_'.date('d_m_Y').'.pdf');
    }

    public function examRoutinePDF($exam_type_id)
    {
        if (!sessionClassStudent()) {
            return ___('alert.Student not found');
        }

        $classSection       = sessionClassStudent();

        $request = new Request([
            'class'        => @$classSection->classes_id,
            'section'      => @$classSection->section_id,
            'type'         => $exam_type_id,
        ]);

        $data['result']       = $this->reportExamRoutineRepo->search($request);
        $data['time']         = $this->reportExamRoutineRepo->time($request);
        
        $pdf = PDF::loadView('backend.report.exam-routinePDF', compact('data'));
        return $pdf->download('exam_routine'.'_'.date('d_m_Y').'.pdf');
    }
}
