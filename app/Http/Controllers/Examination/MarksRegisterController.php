<?php

namespace App\Http\Controllers\Examination;

use App\Http\Controllers\Controller;
use App\Http\Requests\Examination\MarksRegister\MarksRegisterStoreRequest;
use App\Http\Requests\Examination\MarksRegister\MarksRegisterUpdateRequest;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\Academic\ClassesRepository;
use App\Repositories\Academic\SectionRepository;
use App\Repositories\Academic\SubjectRepository;
use App\Repositories\Examination\ExamAssignRepository;
use App\Repositories\Examination\ExamTypeRepository;
use App\Repositories\Examination\MarksRegisterRepository;
use App\Repositories\StudentInfo\StudentRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarksRegisterController extends Controller
{
    private $repo;

    private $classRepo;

    private $classSetupRepo;

    private $sectionRepo;

    private $examTypeRepo;

    private $subjectRepo;

    private $examAssignRepo;

    private $studentRepo;

    public function __construct(
        MarksRegisterRepository $repo,
        ClassSetupRepository $classSetupRepo,
        ClassesRepository $classRepo,
        SectionRepository $sectionRepo,
        ExamTypeRepository $examTypeRepo,
        SubjectRepository $subjectRepo,
        ExamAssignRepository $examAssignRepo,
        StudentRepository $studentRepo,
    ) {
        $this->repo = $repo;
        $this->classRepo = $classRepo;
        $this->classSetupRepo = $classSetupRepo;
        $this->sectionRepo = $sectionRepo;
        $this->examTypeRepo = $examTypeRepo;
        $this->subjectRepo = $subjectRepo;
        $this->examAssignRepo = $examAssignRepo;
        $this->studentRepo = $studentRepo;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('examination.marks_register');
        $data['classes'] = $this->classRepo->assignedAll();
        $data['subjects'] = $this->subjectRepo->all();
        $data['marks_registers'] = $this->repo->getPaginateAll();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['marks_registers'],
                'meta' => [
                    'title' => $data['title'],
                    'classes' => $data['classes'],
                    'subjects' => $data['subjects'],
                ],
            ]);
        }

        return redirect()->to(spa_url('examination/marks-register'));
    }

    public function search(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('examination.marks_register');
        $data['classes'] = $this->classRepo->assignedAll();
        $data['subjects'] = $this->subjectRepo->all();
        $data['marks_registers'] = $this->repo->searchMarkRegister($request);
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['marks_registers'],
                'meta' => [
                    'title' => $data['title'],
                    'classes' => $data['classes'],
                    'subjects' => $data['subjects'],
                ],
            ]);
        }

        return redirect()->to(spa_url('examination/marks-register'));
    }

    public function show(Request $request): JsonResponse|RedirectResponse
    {
        $data['marks_register'] = $this->repo->show($request->id);

        $data['subjects'] = $this->subjectRepo->all();
        $filterRequest = new Request([
            'class' => $data['marks_register']->classes_id,
            'section' => $data['marks_register']->section_id,
            'exam_type' => $data['marks_register']->exam_type_id,
            'subject' => $data['marks_register']->subject_id,
        ]);

        $data['examAssign'] = $this->examAssignRepo->getExamAssign($filterRequest);
        $data['students'] = $this->studentRepo->getStudents($filterRequest);
        Log::info($filterRequest);
        Log::info($data['examAssign']);

        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => ___('examination.marks_register')]]);
        }

        return redirect()->to(spa_url('examination/marks-register/'.$request->id.'/view'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['classes'] = $this->classSetupRepo->all();
        $data['sections'] = $this->sectionRepo->all();
        $data['exam_types'] = $this->examAssignRepo->assignedExamType();
        $data['subjects'] = $this->subjectRepo->all();
        $data['title'] = ___('examination.marks_register');
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return redirect()->to(spa_url('examination/marks-register/create'));
    }

    public function terminal(Request $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->computeGeneralResult();
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('marks-register.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function store(MarksRegisterStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('marks-register.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function getStudents2(Request $request)
    {
        $result = $this->repo->storeForApp($request);

        return response()->json([
            'status' => 'success',
            'result' => $result,
        ]);
    }

    public function storeFromApp_(MarksRegisterStoreRequest $request)
    {
        $result = $this->repo->storeForApp($request);
        if ($result['status']) {
            return response()->json([
                'status' => 'Submitted Successfully',
            ]);
        }

        return response()->json([
            'status' => 'Submitted Failed',
        ]);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['classes'] = $this->classSetupRepo->all();
        $data['sections'] = $this->sectionRepo->all();

        $data['subjects'] = $this->subjectRepo->all();
        $data['marks_register'] = $this->repo->show($id);
        if ($data['marks_register'] && $request->expectsJson()) {
            $data['marks_register']->load('marksRegisterChilds');
        }
        $data['title'] = ___('examination.marks_register');

        $req1 = new Request([
            'class' => $data['marks_register']->classes_id,
            'section' => $data['marks_register']->section_id,
        ]);
        $data['exam_types'] = $this->examAssignRepo->getExamType($req1);

        $req2 = new Request([
            'class' => $data['marks_register']->classes_id,
            'section' => $data['marks_register']->section_id,
            'exam_type' => $data['marks_register']->exam_type_id,
            'subject' => $data['marks_register']->subject_id,
        ]);

        $data['examAssign'] = $this->examAssignRepo->getExamAssign($req2);
        $data['students'] = $this->studentRepo->getStudents($req2);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(spa_url('examination/marks-register/'.$id.'/edit'));
    }

    public function update(MarksRegisterUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('marks-register.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function delete(Request $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->destroy($id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('marks-register.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }
}
