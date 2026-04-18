<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Repositories\StudentInfo\StudentRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DuplicateStudentsController extends Controller
{
    private $studentRepo;

    public function __construct(StudentRepository $studentRepo)
    {
        $this->studentRepo = $studentRepo;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['title'] = 'Duplicate Students';
        $data['duplicates'] = [];
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['duplicates'], 'meta' => ['title' => $data['title']]]);
        }
        return view('backend.report.duplicate-students', compact('data'));
    }

    public function search(Request $request): JsonResponse|View
    {
        $data['title'] = 'Duplicate Students';
        $duplicates = $this->studentRepo->findDuplicateStudents();
        $data['duplicates'] = $duplicates;
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['duplicates'], 'meta' => ['title' => $data['title']]]);
        }
        return view('backend.report.duplicate-students', compact('data'));
    }
}

