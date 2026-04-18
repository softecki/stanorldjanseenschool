<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\StudentInfo\SessionClassStudent;
use App\Repositories\Academic\ClassesRepository;
use App\Repositories\Academic\SectionRepository;
use App\Repositories\Academic\SubjectRepository;
use App\Repositories\Homework\HomeworkRepository;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\Academic\SubjectAssignRepository;
use App\Http\Requests\Examination\Homework\HomeworkStoreRequest;
use App\Http\Requests\Examination\Homework\HomeworkUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class HomeworkController extends Controller
{
    private $repo;
    private $classRepo;
    private $classSetupRepo;
    private $sectionRepo;
    private $subjectRepo;
    private $assignSubjectRepo;

    function __construct(
        HomeworkRepository $repo, 
        ClassSetupRepository $classSetupRepo, 
        ClassesRepository $classRepo, 
        SectionRepository $sectionRepo, 
        SubjectRepository $subjectRepo,
        SubjectAssignRepository $assignSubjectRepo,
        )
    {
        $this->repo               = $repo;
        $this->classRepo          = $classRepo;  
        $this->classSetupRepo     = $classSetupRepo;  
        $this->sectionRepo        = $sectionRepo;  
        $this->subjectRepo        = $subjectRepo; 
        $this->assignSubjectRepo        = $assignSubjectRepo; 
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('examination.homework');
        $data['classes']            = $this->classRepo->assignedAll();
        $data['homeworks']    = $this->repo->getPaginateAll();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['homeworks'],
                'meta' => [
                    'title' => $data['title'],
                    'classes' => $data['classes'],
                ],
            ]);
        }
        return redirect()->to(spa_url('homework'));
    }

    public function search(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('examination.homework');
        $data['classes']            = $this->classRepo->assignedAll();
        $data['homeworks']    = $this->repo->search($request);
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['homeworks'],
                'meta' => [
                    'title' => $data['title'],
                    'classes' => $data['classes'],
                ],
            ]);
        }
        return redirect()->to(spa_url('homework'));
    }

    
    public function show(Request $request)
    {
        $data['homework']        = $this->repo->show($request->id);

        $request = new Request([
            'class'     => $data['homework']->classes_id,
            'section'   => $data['homework']->section_id,
            'exam_type' => $data['homework']->exam_type_id,
            'subject'   => $data['homework']->subject_id
        ]);

        return view('backend.homework.view', compact('data'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['classes']                = $this->classSetupRepo->all();
        $data['title']                  = ___('examination.homework');
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return redirect()->to(spa_url('homework/create'));
    }

    public function store(HomeworkStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('homework.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function edit($id, Request $request): JsonResponse|RedirectResponse
    {
        $data['homework']              = $this->repo->show($id);
        $data['classes']               = $this->classSetupRepo->all();
        $data['sections']              = $this->classSetupRepo->getSections($data['homework']->classes_id);

        $request->merge([
            'classes_id' => $data['homework']->classes_id,
            'section_id' => $data['homework']->section_id
        ]);

        $data['subjects']              = $this->assignSubjectRepo->getSubjects($request);

        
        $data['title']                 = ___('examination.homework');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('homework/'.$id.'/edit'));
    }

    public function update(HomeworkUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('homework.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function delete(Request $request, $id): JsonResponse|RedirectResponse
    {

        $result = $this->repo->destroy($id);
        if($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('homework.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function students(Request $request) 
    {
        $data['homework']   = $this->repo->show($request->homework_id);

        $data['students']   = SessionClassStudent::with(['homeworkStudent' => function ($query) use ($data) {
                                                        $query->where('homework_id', $data['homework']->id);
                                                    }])
                                                    ->where('session_id', setting('session'))
                                                    ->where('classes_id', $data['homework']->classes_id)
                                                    ->where('section_id', $data['homework']->section_id)
                                                    ->get();

        return response()->json($data);
        
        
    }

    public function evaluationSubmit(Request $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->evaluationSubmit($request);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('homework.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    
}
