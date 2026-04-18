<?php

namespace App\Http\Controllers\ParentPanel;

use App\Http\Controllers\Controller;
use App\Repositories\ParentPanel\AttendanceRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class AttendanceController extends Controller
{
    private $repo;

    function __construct(  AttendanceRepository $repo) 
    { 
        $this->repo = $repo;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('common.Attendance');
        
        $data                       = $this->repo->index();
        $data['results']            = [];
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('parent-panel/attendance'));
    }

    public function search(Request $request): JsonResponse|RedirectResponse
    {
        $data                 = $this->repo->search($request);
        $data['title']        = ___('common.Attendance');
        $data['request']      = $request;
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('parent-panel/attendance'));
    }
}
