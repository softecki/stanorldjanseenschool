<?php

namespace App\Http\Controllers\Academic;

use Illuminate\Http\Request;
use App\Interfaces\UserInterface;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Interfaces\SessionInterface;
use App\Traits\ApiReturnFormatTrait;
use App\Interfaces\Academic\ShiftInterface;
use App\Interfaces\Academic\ClassesInterface;
use App\Interfaces\Academic\SectionInterface;
use App\Interfaces\Academic\SubjectInterface;
use App\Models\Academic\SubjectAssignChildren;
use App\Interfaces\Academic\SubjectAssignInterface;
use App\Repositories\Academic\ClassSetupRepository;
use App\Http\Requests\Academic\SubjectAssign\SubjectAssignStoreRequest;
use App\Http\Requests\Academic\SubjectAssign\SubjectAssignUpdateRequest;

class SubjectAssignController extends Controller
{
    use ApiReturnFormatTrait;

    private $repo;
    private $sessionRepo;
    private $classesRepo;
    private $sectionRepo;
    private $shiftRepo;
    private $subjectRepo;
    private $staffRepo;
    private $classSetupRepo;

    function __construct(
        SubjectAssignInterface $repo,
        SessionInterface       $sessionRepo,
        ClassesInterface       $classesRepo,
        SectionInterface       $sectionRepo,
        ShiftInterface         $shiftRepo,
        SubjectInterface       $subjectRepo,
        UserInterface          $staffRepo,
        ClassSetupRepository   $classSetupRepo,
        )
    {
        $this->repo              = $repo; 
        $this->sessionRepo       = $sessionRepo; 
        $this->classesRepo       = $classesRepo; 
        $this->sectionRepo       = $sectionRepo; 
        $this->shiftRepo         = $shiftRepo; 
        $this->subjectRepo       = $subjectRepo; 
        $this->staffRepo         = $staffRepo; 
        $this->classSetupRepo    = $classSetupRepo; 
    }
    
    public function index(Request $request): JsonResponse|View
    {
        $data['title']              = ___('academic.subject_assign');
        $data['subject_assigns']    = $this->repo->getPaginateAll();

        if ($request->expectsJson()) return response()->json(['data' => $data['subject_assigns'], 'meta' => ['title' => $data['title']]]);
        return view('backend.academic.assign-subject.index', compact('data'));
        
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('academic.subject_assign');
        $data['classes']            = $this->classesRepo->assignedAll();
        $data['sections']           = [];
        $data['shifts']             = $this->shiftRepo->all();
        // $data['subjects']           = $this->subjectRepo->all();
        if ($request->expectsJson()) return response()->json(['meta' => $data]);
        return redirect()->to(url('/app/academic/subject-assigns/create'));
        
    }
    
    public function addSubjectTeacher(Request $request): JsonResponse|RedirectResponse
    {
        $counter          = $request->counter;
        $data['subjects'] = $this->subjectRepo->all();
        $data['teachers'] = $this->staffRepo->all();
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['counter' => $counter, 'subjects' => $data['subjects'], 'teachers' => $data['teachers']]]);
        }

        return redirect()->to(url('/app/academic/subject-assigns/create'));
    }

    public function store(SubjectAssignStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('assign-subject.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function show(Request $request): JsonResponse|RedirectResponse
    {
        $data['subject_assign_children'] = SubjectAssignChildren::where('subject_assign_id', $request->id)->get();

        if ($request->expectsJson()) {
            return response()->json(['data' => $data['subject_assign_children']]);
        }

        return redirect()->to(url('/app/academic/subject-assigns'));
    }

    public function getSubjects(Request $request){
        $result = $this->repo->getSubjects($request);
        return response()->json($result, 200);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data                       = $this->repo->show($id);
        $data['title']              = ___('academic.subject_assign');
        $data['subject_assign']     = $data['row'];
        $data['assignSubjects']     = $data['assignSubjects'];
        $data['disabled']           = $data['disabled'];
        $data['redirect']           = $data['redirect'];
        $data['classes']            = $this->classesRepo->assignedAll();
        $data['sections']           = $this->classSetupRepo->getSections($data['subject_assign']->classes_id);
        $data['shifts']             = $this->shiftRepo->all();
        $data['subjects']           = $this->subjectRepo->all();
        $data['teachers']           = $this->staffRepo->all();
        $data['all_subject_assign'] = $data['subject_assign']->subjectTeacher->pluck('subject_id')->toArray();
        
        // dd($data['redirect']);
        // if($data['redirect'])
        //     return redirect()->route('assign-subject.index')->with('danger', ___('academic.you_cannot_edit_this'));

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['subject_assign'],
                'meta' => [
                    'title' => $data['title'],
                    'assignSubjects' => $data['assignSubjects'],
                    'disabled' => $data['disabled'],
                    'redirect' => $data['redirect'],
                    'classes' => $data['classes'],
                    'sections' => $data['sections'],
                    'shifts' => $data['shifts'],
                    'subjects' => $data['subjects'],
                    'teachers' => $data['teachers'],
                    'all_subject_assign' => $data['all_subject_assign'],
                ],
            ]);
        }
        return redirect()->to(url('/app/academic/subject-assigns/'.$id.'/edit'));
    }

    public function update(SubjectAssignUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        // dd($request->all());
        $result = $this->repo->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('assign-subject.index')->with('success', $result['message']);
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

    public function checkSection(Request $request)
    {
        $result = $this->repo->checkSection($request);
        return response()->json($result, 200);
    }

    public function checkExamAssign($id)
    {
        $result = $this->repo->checkExamAssign($id);
        return response()->json($result, 200);
    }

}
