<?php

namespace App\Http\Controllers\Examination;

use App\Http\Controllers\Controller;
use App\Http\Requests\Examination\MarksGrade\MarksGradeStoreRequest;
use App\Http\Requests\Examination\MarksGrade\MarksGradeUpdateRequest;
use App\Repositories\Examination\MarksGradeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MarksGradeController extends Controller
{
    private $repo;

    public function __construct(MarksGradeRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('examination.marks_grade');
        $data['marks_grades'] = $this->repo->getPaginateAll();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['marks_grades'],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return redirect()->to(spa_url('examination/marks-grades'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('examination.marks_grade');
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return redirect()->to(spa_url('examination/marks-grades/create'));
    }

    public function store(MarksGradeStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('marks-grade.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['marks_grade'] = $this->repo->show($id);
        $data['title'] = ___('examination.marks_grade');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(spa_url('examination/marks-grades/'.$id.'/edit'));
    }

    public function update(MarksGradeUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('marks-grade.index')->with('success', $result['message']);
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

            return redirect()->route('marks-grade.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }
}
