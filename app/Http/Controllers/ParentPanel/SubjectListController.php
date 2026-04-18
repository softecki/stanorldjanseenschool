<?php

namespace App\Http\Controllers\ParentPanel;

use App\Http\Controllers\Controller;
use App\Repositories\ParentPanel\SubjectListRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class SubjectListController extends Controller
{
    private $repo;

    function __construct( SubjectListRepository $repo)
    { 
        $this->repo = $repo; 
    }
    public function index(Request $request): JsonResponse|RedirectResponse{
        $data = $this->repo->index();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => 'Subject list']]);
        }
        return redirect()->to(spa_url('parent-panel/subject-list'));
    }

    public function search(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->repo->search($request);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => 'Subject list']]);
        }
        return redirect()->to(spa_url('parent-panel/subject-list'));
    }
}
