<?php

namespace App\Http\Controllers\Examination;

use App\Http\Controllers\Controller;
use App\Http\Requests\Examination\Assign\ExamAssignStoreRequest;
use App\Http\Requests\Examination\Assign\ExamAssignUpdateRequest;
use App\Models\Academic\Section;
use App\Models\Academic\Subject;
use App\Models\Academic\SubjectAssign;
use App\Models\Academic\SubjectAssignChildren;
use App\Models\Examination\ExamType;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\Academic\ClassesRepository;
use App\Repositories\Academic\SectionRepository;
use App\Repositories\Examination\ExamAssignRepository;
use App\Repositories\Examination\ExamTypeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExamAssignController extends Controller
{
    private $repo;

    private $classRepo;

    private $sectionRepo;

    private $examTypeRepo;

    private $classSetupRepo;

    public function __construct(
        ExamAssignRepository $repo,
        ClassesRepository $classRepo,
        SectionRepository $sectionRepo,
        ExamTypeRepository $examTypeRepo,
        ClassSetupRepository $classSetupRepo,
    ) {
        $this->repo = $repo;
        $this->classRepo = $classRepo;
        $this->sectionRepo = $sectionRepo;
        $this->examTypeRepo = $examTypeRepo;
        $this->classSetupRepo = $classSetupRepo;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('examination.exam_assign');
        $data['classes'] = $this->classRepo->assignedAll();
        $data['exam_assigns'] = $this->repo->getPaginateAll();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['exam_assigns'],
                'meta' => [
                    'title' => $data['title'],
                    'classes' => $data['classes'],
                ],
            ]);
        }

        return redirect()->to(spa_url('examination/exam-assign'));
    }

    public function search(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('examination.exam_assign');
        $data['exam_assigns'] = $this->repo->searchExamAssign($request);
        $data['subjectArr'] = Subject::pluck('name', 'id')->toArray();
        $data['sectionArr'] = Section::pluck('name', 'id')->toArray();
        $data['examArr'] = ExamType::pluck('name', 'id')->toArray();
        $data['classes'] = $this->classRepo->assignedAll();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['exam_assigns'],
                'meta' => [
                    'title' => $data['title'],
                    'classes' => $data['classes'],
                    'subjectArr' => $data['subjectArr'],
                    'sectionArr' => $data['sectionArr'],
                    'examArr' => $data['examArr'],
                ],
            ]);
        }

        return redirect()->to(spa_url('examination/exam-assign'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('examination.exam_assign');
        $data['classes'] = $this->classSetupRepo->all();
        $data['exam_types'] = $this->examTypeRepo->all();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return redirect()->to(spa_url('examination/exam-assign/create'));
    }

    public function marksDistribution(Request $request): JsonResponse
    {
        $html = view('backend.examination.exam-assign.marks_distribute', compact('request'))->render();

        return response()->json(['html' => $html]);
    }

    public function subjectMarksDistribution(Request $request): JsonResponse
    {
        $subjectArr = Subject::pluck('name', 'id')->toArray();
        $html = view('backend.examination.exam-assign.subject_marks_distribute', compact('subjectArr', 'request'))->render();

        return response()->json(['html' => $html]);
    }

    public function store(ExamAssignStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('exam-assign.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->show($id);
        if (! $result) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You cannot edit this! Marks already registered.'], 422);
            }

            return redirect()->route('exam-assign.index')->with('danger', 'You cannot edit this! because, already marks registred.');
        }

        $data['exam_assign'] = $result->load('mark_distribution');
        $data['title'] = ___('examination.exam_assign');
        $data['classes'] = $this->classRepo->all();
        $data['sections'] = $this->classSetupRepo->getSections($data['exam_assign']->classes_id);

        $assignRow = SubjectAssign::active()->where('session_id', setting('session'))->where('classes_id', $data['exam_assign']->classes_id)->where('section_id', $data['exam_assign']->section_id)->first();
        $data['subjects'] = SubjectAssignChildren::with('subject')->where('subject_assign_id', @$assignRow->id)->select('subject_id')->get();

        $data['exam_types'] = $this->examTypeRepo->all();

        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(spa_url('examination/exam-assign/'.$id.'/edit'));
    }

    public function update(ExamAssignUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('exam-assign.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function checkMarkRegister($id)
    {
        $result = $this->repo->checkMarkRegister($id);

        return response()->json($result, 200);
    }

    public function delete(Request $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->destroy($id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('exam-assign.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function getSections(Request $request)
    {
        $data = $this->classSetupRepo->getSections($request->id);

        return response()->json($data);
    }

    public function getSubjects(Request $request)
    {
        $result = $this->repo->getSubjects($request);

        return response()->json($result, 200);
    }

    public function getExamType(Request $request)
    {
        $result = $this->repo->getExamType($request);

        return response()->json($result, 200);
    }

    public function checkSubmit(Request $request)
    {
        $result = $this->repo->checkSubmit($request);

        return response()->json($result, 200);
    }
}
