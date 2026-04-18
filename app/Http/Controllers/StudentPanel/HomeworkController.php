<?php

namespace App\Http\Controllers\StudentPanel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentPanel\HomeworkSubmit;
use App\Repositories\StudentPanel\Homework\HomeworkInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class HomeworkController extends Controller
{
    private $repo;

    function __construct(HomeworkInterface $repo)
    { 
        $this->repo = $repo;
    }
    
    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['homeworks'] = $this->repo->index();
        $data['title']     = 'homework';

        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => 'Homework']]);
        }
        return redirect()->to(spa_url('student-panel/homeworks'));
    }

    public function submit(HomeworkSubmit $request)
    {
        $result = $this->repo->submit($request);
        return response()->json($result);
    }
}
