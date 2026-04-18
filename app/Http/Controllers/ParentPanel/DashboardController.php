<?php

namespace App\Http\Controllers\ParentPanel;

use Carbon\Carbon;
use App\Enums\Status;
use App\Models\Search;
use App\Models\NoticeBoard;
use Illuminate\Http\Request;
use App\Models\StudentInfo\Student;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\StudentInfo\ParentGuardian;
use App\Repositories\ParentPanel\DashboardRepository;

class DashboardController extends Controller
{
    private $repo;

    function __construct(DashboardRepository $repo) 
    {
        $this->repo               = $repo;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->repo->index();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data]);
        }

        return redirect()->to(spa_url('parent-panel'));
    }

    public function search(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->repo->search($request);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data]);
        }

        return redirect()->to(spa_url('parent-panel'));
    }

    public function searchParentMenuData(Request $request){
        try {
            $search = Search::query()
                    ->when(request()->filled('search'), fn ($q) => $q->where('title', 'like', '%' . $request->search . '%'))
                    ->where('user_type', 'Parent')
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

    public function notices(Request $request): JsonResponse|RedirectResponse
    {
        $currentDateTime = Carbon::now(); // Get the current datetime
        $role_id         = Auth::user()->role_id;

        $data['notice-boards'] = NoticeBoard::where('status', Status::ACTIVE)
                                ->where('publish_date', '<=', $currentDateTime)
                                ->whereJsonContains('visible_to', "$role_id")
                                ->orderByDesc('id')
                                ->paginate(10);

        $data['title']        = ___('common.notice boards');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('parent-panel/notices'));

    }


}
