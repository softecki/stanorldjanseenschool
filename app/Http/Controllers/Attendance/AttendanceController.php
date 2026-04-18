<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\AttendanceReportRequest;
use App\Http\Requests\Attendance\AttendanceSearchRequest;
use App\Http\Requests\Attendance\AttendanceStoreRequest;
use App\Http\Requests\Report\AttendanceRequest;
use App\Repositories\Academic\ClassesRepository;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\Attendance\AttendanceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PDF;

class AttendanceController extends Controller
{
    private $repo;
    private $classRepo;
    private $classSetupRepo;

    function __construct(
        AttendanceRepository   $repo,
        ClassesRepository      $classRepo, 
        ClassSetupRepository   $classSetupRepo, 
    )
    {
        $this->repo              = $repo;  
        $this->classRepo         = $classRepo; 
        $this->classSetupRepo    = $classSetupRepo; 
    }
    
    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('attendance.Attendance');
        $data['classes']            = $this->classRepo->assignedAll();
        $data['sections']           = [];

        if ($request->expectsJson()) return response()->json(['meta' => $data]);
        return redirect()->to(url('/app/attendance'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
      
        $result = $this->repo->store($request);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect(route('attendance.index'))->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function searchStudents(AttendanceSearchRequest $request): JsonResponse|RedirectResponse
    {
        $data = $this->repo->searchStudents($request);
        $data['title']    = ___('attendance.Attendance');
        $data['request']  = $request;
        $data['students'] = $data['students'];
        $data['status']   = $data['status'];
        $data['classes']  = $this->classRepo->assignedAll();
        $data['sections'] = $this->classSetupRepo->getSections($request->class);
        if ($request->expectsJson()) return response()->json(['data' => ['students' => $data['students'], 'status' => $data['status']], 'meta' => $data]);
        return redirect()->to(url('/app/attendance'));
    }


    // report start----------------------------------------------------------------------------------------------
    
    public function report(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('attendance.Attendance');
        $data['classes']            = $this->classRepo->assignedAll();
        $data['sections']           = [];
        $data['students']           = [];
        $data['request']            = [];

        if ($request->expectsJson()) return response()->json(['meta' => $data]);
        return redirect()->to(url('/app/attendance/report'));
    }

    
    public function reportSearch(AttendanceRequest $request): JsonResponse|RedirectResponse
    {
        $data['title']        = ___('attendance.Attendance');
        $data['request']      = $request;
        $data['classes']      = $this->classRepo->assignedAll();
        $data['sections']     = $this->classSetupRepo->getSections($request->class);
        $results              = $this->repo->searchReport($request);
        $data['students']     = $results['students'];
        $data['days']         = $results['days'];
        $data['attendances']  = $results['attendances'];
        if ($request->expectsJson()) {
            return response()->json([
                'data' => ['students' => $data['students'], 'days' => $data['days'], 'attendances' => $data['attendances']],
                'meta' => $data,
            ]);
        }
        return redirect()->to(url('/app/attendance/report'));
    }

    public function generatePDF(Request $request)
    {
        $results              = $this->repo->searchReportPDF($request);
        $data['students']     = $results['students'];
        $data['days']         = $results['days'];
        $data['attendances']  = $results['attendances'];
        $data['request']      = $request;
        
        $pdf = PDF::loadView('backend.attendance.reportPDF', compact('data'));

        if($request->view == '0')
            $pdf->setPaper('A4', 'landscape');
            
        return $pdf->download('attendance'.'_'.date('d_m_Y').'.pdf');
    }

    // report end----------------------------------------------------------------------------------------------
}
