<?php

namespace App\Http\Controllers\StudentPanel;

use App\Http\Controllers\Controller;
use App\Repositories\StudentPanel\SubjectListRepository;
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
        $subjectTeacher = $this->repo->index();
        if ($request->expectsJson()) {
            return response()->json(['data' => ['subjectTeacher' => $subjectTeacher], 'meta' => ['title' => 'Subject list']]);
        }
        return redirect()->to(spa_url('student-panel/subject-list'));
    }
}
