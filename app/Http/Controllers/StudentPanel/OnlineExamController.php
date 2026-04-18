<?php

namespace App\Http\Controllers\StudentPanel;

use App\Http\Controllers\Controller;
use App\Repositories\StudentPanel\OnlineExaminationRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class OnlineExamController extends Controller
{
    private $repo;

    function __construct(
        OnlineExaminationRepository $repo, 
    ) 
    { 
        $this->repo = $repo;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->repo->index();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => 'Online examinations']]);
        }
        return redirect()->to(spa_url('student-panel/online-exam'));
    }
    public function view(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data = $this->repo->view($id);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => 'Online examination']]);
        }
        return redirect()->to(spa_url('student-panel/online-exam/'.$id.'/view'));
    }
    public function resultView(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data = $this->repo->resultView($id);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => 'Online examination result']]);
        }
        return redirect()->to(spa_url('student-panel/online-exam/'.$id.'/result'));
    }
    public function answerSubmit(Request $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->answerSubmit($request);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('student-panel-online-examination.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }
}
