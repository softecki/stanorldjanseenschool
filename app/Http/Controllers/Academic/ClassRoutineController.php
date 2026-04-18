<?php

namespace App\Http\Controllers\Academic;

use Illuminate\Http\Request;
use App\Interfaces\UserInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Interfaces\SessionInterface;
use App\Traits\ApiReturnFormatTrait;
use App\Models\Academic\TimeSchedule;
use App\Interfaces\Academic\ShiftInterface;
use App\Interfaces\Academic\ClassesInterface;
use App\Interfaces\Academic\SectionInterface;
use App\Interfaces\Academic\SubjectInterface;
use App\Models\Academic\SubjectAssignChildren;
use App\Repositories\Academic\ClassRoomRepository;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\Academic\ClassRoutineRepository;
use App\Repositories\Academic\TimeScheduleRepository;
use App\Repositories\Academic\SubjectAssignRepository;
use App\Http\Requests\Academic\ClassRoutine\ClassRoutineStoreRequest;
use App\Http\Requests\Academic\ClassRoutine\ClassRoutineUpdateRequest;
use App\Http\Requests\Academic\SubjectAssign\SubjectAssignStoreRequest;
use App\Http\Requests\Academic\SubjectAssign\SubjectAssignUpdateRequest;

class ClassRoutineController extends Controller
{
    use ApiReturnFormatTrait;

    private $repo;
    private $sessionRepo;
    private $classesRepo;
    private $sectionRepo;
    private $shiftRepo;
    private $subjectRepo;
    private $staffRepo;
    private $classRoomRepo;
    private $subjectAssignRepo;
    private $timeScheduleRepo;
    private $classSetupRepo;

    function __construct(
        ClassRoutineRepository    $repo,
        SessionInterface          $sessionRepo,
        ClassesInterface          $classesRepo,
        SectionInterface          $sectionRepo,
        ShiftInterface            $shiftRepo,
        SubjectInterface          $subjectRepo,
        UserInterface             $staffRepo,
        ClassRoomRepository       $classRoomRepo,
        SubjectAssignRepository   $subjectAssignRepo,
        TimeScheduleRepository    $timeScheduleRepo,
        ClassSetupRepository      $classSetupRepo,
        )
    {
        $this->repo                 = $repo; 
        $this->sessionRepo          = $sessionRepo; 
        $this->classesRepo          = $classesRepo; 
        $this->sectionRepo          = $sectionRepo; 
        $this->shiftRepo            = $shiftRepo; 
        $this->subjectRepo          = $subjectRepo; 
        $this->staffRepo            = $staffRepo; 
        $this->classRoomRepo        = $classRoomRepo; 
        $this->subjectAssignRepo    = $subjectAssignRepo; 
        $this->timeScheduleRepo     = $timeScheduleRepo; 
        $this->classSetupRepo       = $classSetupRepo; 
    }
    
    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']             = ___('academic.class_routine');
        $data['class_routines']    = $this->repo->getPaginateAll();

        if ($request->expectsJson()) return response()->json(['data' => $data['class_routines'], 'meta' => ['title' => $data['title']]]);
        return redirect()->to(url('/app/academic/class-routines'));
        
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('academic.class_routine');
        $data['classes']            = $this->classesRepo->assignedAll();
        $data['sections']           = $this->sectionRepo->all();
        $data['shifts']             = $this->shiftRepo->all();
        // $data['subjects']           = $this->subjectRepo->all();
        if ($request->expectsJson()) return response()->json(['meta' => $data]);
        return redirect()->to(url('/app/academic/class-routines/create'));
        
    }
    
    public function addClassRoutine(Request $request): JsonResponse|RedirectResponse
    {
        $counter                 = $request->counter;

        
        $data['subjects']        = $this->subjectAssignRepo->getSubjects($request);
        // $data['subjects']        = $this->subjectRepo->all();
        // $data['teachers']        = $this->staffRepo->all();
        $data['class_rooms']     = $this->classRoomRepo->all();
        $data['time_schedules']  = $this->timeScheduleRepo->allClassSchedule();
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['counter' => $counter] + $data]);
        }

        return redirect()->to(url('/app/academic/class-routines/create'));
    }

    public function store(ClassRoutineStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('class-routine.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('academic.class_routine');
        $data['class_routine']      = $this->repo->show($id);
    
        $data['classes']            = $this->classesRepo->assignedAll();
        $data['sections']           = $this->classSetupRepo->getSections($data['class_routine']->classes_id);
    

        $data['shifts']             = $this->shiftRepo->all();
        $data['subjects']           = $this->subjectAssignRepo->getSubjects($data['class_routine']);

        $data['class_rooms']        = $this->classRoomRepo->all();
        $data['time_schedules']     = $this->timeScheduleRepo->allClassSchedule();

        if ($request->expectsJson()) {
            return response()->json(['data' => $data['class_routine'], 'meta' => $data]);
        }
        return redirect()->to(url('/app/academic/class-routines/'.$id.'/edit'));
    }

    public function update(ClassRoutineUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        // dd($request->all());
        $result = $this->repo->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('class-routine.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function delete($id)
    {
        
        $result = $this->repo->destroy($id);
        if($result['status']):
            $success[0] = $result['message'];
            $success[1] = 'success';
            $success[2] = ___('alert.deleted');
            $success[3] = ___('alert.OK');
            return response()->json($success);
        else:
            $success[0] = $result['message'];
            $success[1] = 'error';
            $success[2] = ___('alert.oops');
            return response()->json($success);
        endif;      
    }

    public function checkClassRoutine(Request $request)
    {
        
        $result = $this->repo->checkClassRoutine($request);
       
        return response()->json($result);
    
    }




}
