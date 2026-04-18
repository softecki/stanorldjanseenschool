<?php

namespace App\Http\Controllers\StudentPanel;

use Carbon\Carbon;
use App\Enums\Status;
use App\Models\Gmeet;
use App\Models\Search;
use App\Models\NoticeBoard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentInfo\SessionClassStudent;
use App\Repositories\StudentPanel\DashboardRepository;

class DashboardController extends Controller
{
    private $repo;

    function __construct( DashboardRepository $repo)
    { 
        $this->repo = $repo; 
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->repo->index();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data]);
        }

        return redirect()->to(spa_url('student-panel'));
    }

    public function searchStudentMenuData(Request $request){
        try {
            $search = Search::query()
                    ->when(request()->filled('search'), fn ($q) => $q->where('title', 'like', '%' . $request->search . '%'))
                    ->where('user_type', 'Student')
                    ->take(10)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'title' => $item->title,
                            'route_name' => route($item->route_name)
                        ];
                    });


            return response()->json($search);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    public function gmeet(Request $request): JsonResponse|RedirectResponse
    {
        $session_class_student = SessionClassStudent::where('session_id', setting('session'))->where('student_id', Auth::user()->student->id)->first();
        $data['gmeets'] = Gmeet::where('session_id', setting('session'))
                                ->where('classes_id', $session_class_student->classes_id)
                                ->where('section_id', $session_class_student->section_id)
                                ->orderByDesc('id')
                                ->paginate(10);
                                $data['title']        = ___('common.gmeet');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('student-panel/gmeet'));

    }

    public function notices(Request $request): JsonResponse|RedirectResponse
    {
        $currentDateTime = Carbon::now(); // Get the current datetime
        $role_id = Auth::user()->role_id;

        $data['notice-boards'] = NoticeBoard::where('status', Status::ACTIVE)
                                ->where('publish_date', '<=', $currentDateTime)
                                ->whereJsonContains('visible_to', "$role_id")
                                ->orderByDesc('id')
                                ->paginate(10);

        $data['title']        = ___('common.notice boards');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('student-panel/notices'));

    }


}
