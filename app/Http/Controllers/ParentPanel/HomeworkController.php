<?php

namespace App\Http\Controllers\ParentPanel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\ParentPanel\Homework\HomeworkInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class HomeworkController extends Controller
{
    private $repo;

    function __construct(HomeworkInterface $repo)
    { 
        $this->repo = $repo;
    }
    public function index(Request $request): JsonResponse|RedirectResponse{
        $data = $this->repo->indexParent();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => 'Homework']]);
        }
        return redirect()->to(spa_url('parent-panel/homework-list'));
    }

    public function search(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->repo->search($request);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => 'Homework']]);
        }
        return redirect()->to(spa_url('parent-panel/homework-list'));
    }
}
