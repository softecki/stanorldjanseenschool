<?php

namespace App\Http\Controllers\Fees;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Fees\Assign\FeesAssignStoreRequest;
use App\Http\Requests\Fees\Assign\FeesAssignUpdateRequest;
use App\Interfaces\Fees\FeesTypeInterface;
use App\Interfaces\Fees\FeesGroupInterface;
use App\Interfaces\Fees\FeesAssignInterface;
use App\Repositories\Academic\ClassesRepository;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\Academic\SectionRepository;
use App\Repositories\Fees\FeesMasterRepository;
use App\Repositories\GenderRepository;
use App\Repositories\StudentInfo\StudentCategoryRepository;
use App\Repositories\StudentInfo\StudentRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class FeesAssignController extends Controller
{
    private $repo;
    private $typeRepo;
    private $groupRepo;
    private $feesMasterRepo;
    private $genderRepo;
    private $categoryRepo;
    private $classRepo;
    private $sectionRepo;
    private $classSetupRepo;
    private $studentRepo;

    function __construct(
        FeesAssignInterface $repo,
        FeesTypeInterface $typeRepo,
        FeesGroupInterface $groupRepo,
        FeesMasterRepository $feesMasterRepo,
        GenderRepository $genderRepo,
        StudentCategoryRepository $categoryRepo,
        ClassesRepository $classRepo,
        SectionRepository $sectionRepo,
        ClassSetupRepository $classSetupRepo,
        StudentRepository $studentRepo
        )
    {
        $this->repo              = $repo;
        $this->typeRepo          = $typeRepo;
        $this->groupRepo         = $groupRepo;
        $this->feesMasterRepo    = $feesMasterRepo;
        $this->genderRepo        = $genderRepo;
        $this->categoryRepo      = $categoryRepo;
        $this->classRepo         = $classRepo;
        $this->sectionRepo       = $sectionRepo;
        $this->classSetupRepo    = $classSetupRepo;
        $this->studentRepo       = $studentRepo;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['title']        = ___('fees.fees_assign');
        $data['fees_assigns'] = $this->repo->getPaginateAll();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['fees_assigns'], 'meta' => ['title' => $data['title']]]);
        }

        return view('backend.fees.assign.index', compact('data'));
    }

    public function show(Request $request){

        $data['fees_assign']  = $this->repo->show($request->id);
        return view('backend.student-info.student.view', compact('data'))->render();
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']        = ___('fees.fees_assign');
        $data['classes']      = $this->classRepo->assignedAll();
        // $data['sections']     = $this->sectionRepo->all();
        $data['sections']     = [];
        $data['fees_groups']  = $this->feesMasterRepo->allGroups();
        $data['genders']      = $this->genderRepo->all();
        $data['categories']   = $this->categoryRepo->all();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return redirect()->to(url('/app/fees/assignments/create'));
    }

    public function store(FeesAssignStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('fees-assign.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['title']        = ___('fees.fees_assign');
        $data['fees_assign']  = $this->repo->show($id);

        if (!$data['fees_assign']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Fees assignment not found.'], 404);
            }
            return redirect()->route('fees-assign.index')->with('danger', 'Fees assignment not found.');
        }

        $data['classes']      = $this->classRepo->assignedAll();
        // On edit, show only the class that is already selected for this assignment
        $data['classes'] = $data['classes']->filter(function ($item) use ($data) {
            return (int) $item->classes_id === (int) $data['fees_assign']->classes_id;
        })->values();
        $data['fees_groups']  = $this->feesMasterRepo->allGroups();

        $data['assigned_fes_masters'] = [];
        if ($data['fees_assign']->feesAssignChilds) {
            $data['assigned_fes_masters'] = $data['fees_assign']->feesAssignChilds
                ->pluck('fees_master_id')
                ->unique()
                ->values()
                ->toArray();
        }

        $data['fees_masters'] = collect();
        if ($data['fees_assign']->fees_group_id) {
            $data['fees_masters'] = $this->feesMasterRepo->all()->where('fees_group_id', $data['fees_assign']->fees_group_id);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['fees_assign'],
                'meta' => [
                    'title' => $data['title'],
                    'classes' => $data['classes'],
                    'fees_groups' => $data['fees_groups'],
                    'categories' => $this->categoryRepo->all(),
                    'assigned_fes_masters' => $data['assigned_fes_masters'],
                    'fees_masters' => $data['fees_masters'],
                ],
            ]);
        }

        return redirect()->to(url('/app/fees/assignments/'.$id.'/edit'));
    }

    public function update(FeesAssignUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('fees-assign.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
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

    public function getFeesAssignStudents(Request $request)
    {
        $students = $this->repo->getFeesAssignStudents($request);
        $assignedStudentIds = [];
        $feesAssignId = $request->input('fees_assign_id');
        if ($feesAssignId) {
            $assign = $this->repo->show($feesAssignId);
            if ($assign) {
                $assign->load('feesAssignChilds');
                $assignedStudentIds = $assign->feesAssignChilds->pluck('student_id')->unique()->values()->map(function ($id) {
                    return (int) $id;
                })->toArray();
            }
        }
        if ($request->expectsJson()) {
            $students->loadMissing(['student.parent', 'class', 'section']);

            return response()->json([
                'data' => $students,
                'meta' => [
                    'assigned_student_ids' => $assignedStudentIds,
                ],
            ]);
        }

        return view('backend.fees.assign.fees-assing-students-list', compact('students', 'assignedStudentIds'))->render();
    }

    public function getAllTypes(Request $request)
    {
        $types = $this->repo->groupTypes($request);
        if ($request->expectsJson()) {
            $types->loadMissing('type');

            return response()->json(['data' => $types]);
        }

        return view('backend.fees.assign.fees-types', compact('types'))->render();
    }

}
