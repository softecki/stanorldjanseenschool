<?php

namespace App\Http\Controllers\StudentInfo;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentInfo\PromoteStudent\PromoteStudentSearchRequest;
use App\Http\Requests\StudentInfo\PromoteStudent\PromoteStudentStoreRequest;
use App\Http\Requests\StudentInfo\PromoteStudent\PromoteStudentUpdateRequest;
use App\Repositories\Academic\ClassesRepository;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\Academic\SectionRepository;
use App\Repositories\SessionRepository;
use App\Repositories\StudentInfo\PromoteStudentRepository;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class PromoteStudentController extends Controller
{
    private $repo;
    private $classRepo;
    private $sectionRepo;
    private $sessionRepo;
    private $classSetupRepo;

    function __construct(
        PromoteStudentRepository $repo,
        ClassesRepository        $classRepo,
        SectionRepository        $sectionRepo,
        SessionRepository        $sessionRepo,
        ClassSetupRepository     $classSetupRepo,
        )
    {
        $this->repo              = $repo;
        $this->classRepo         = $classRepo;
        $this->sectionRepo       = $sectionRepo;
        $this->sessionRepo       = $sessionRepo;
        $this->classSetupRepo    = $classSetupRepo;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['title']              = ___('student_info.promote_list');
        $data['student_categories'] = $this->repo->getPaginateAll();
        $data['classes']            = $this->classRepo->assignedAll();
        $data['sections']           = [];
        $data['sessions']           = $this->sessionRepo->all();
        $data['promoteClasses']     = [];
        $data['promoteSections']     = [];
        $students                   = [];
        $results                    = [''];
        if ($request->expectsJson()) {
            return response()->json([
                'data' => ['students' => $students, 'results' => $results],
                'meta' => $data,
            ]);
        }
        return view('backend.student-info.promote-student.index', compact('data', 'students', 'results'));

    }

    public function search(PromoteStudentSearchRequest $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('student_info.promote_list');
        $data['student_categories'] = $this->repo->getPaginateAll();
        $data['classes']            = $this->classRepo->assignedAll();
        $data['sections']           = $this->classSetupRepo->getSections($request->class);
        $data['sessions']           = $this->sessionRepo->all();
        $data['promoteClasses']     = $this->classSetupRepo->promoteClasses($request->promote_session);
        $data['promoteSections']    = $this->classSetupRepo->promoteSections($request->promote_session, $request->promote_class);

        $items                      = $this->repo->search($request);
        $students                   = $items['data']['students'];
        $results                    = $items['data']['results'];

        // dd($data);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => ['students' => $students, 'results' => $results],
                'meta' => $data,
            ]);
        }
        return redirect()->to(url('/app/students/promote'));
    }

    public function store(PromoteStudentStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if($result['status']){
            $data['title']              = ___('student_info.promote_list');
            $data['student_categories'] = $this->repo->getPaginateAll();
            $data['classes']            = $this->classRepo->assignedAll();
            $data['sections']           = $this->classSetupRepo->getSections($request->class);
            $data['sessions']           = $this->sessionRepo->all();
            $data['promoteClasses']     = $this->classSetupRepo->promoteClasses($request->promote_session);
            $data['promoteSections']    = $this->classSetupRepo->promoteSections($request->promote_session, $request->promote_class);
            $items                   = $this->repo->search($request);
            // return view('backend.student-info.promote-student.index', [
            //     'data'     => $data,
            //     'students' => $items['data']['students'],
            //     'results'  => $items['data']['results'],
            //     'request'  => $request,
            //     'message'  => $result['message']
            // ])->with('success', $result['message']);
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('promote_students.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return redirect()->route('promote_students.index')->with('danger', $result['message'])->withInput();
    }

    public function getClass(Request $request){
        return $this->repo->getClass($request);
    }

    public function getSections(Request $request){
        return $this->repo->getSections($request);
    }

}
