<?php

namespace App\Http\Controllers\StudentInfo;

use App\Http\Controllers\Controller;
use App\Models\StudentInfo\StudentDeletedHistory;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class StudentDeletedHistoryController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        $data['title'] = ___('common.deleted_student_history') ?? 'Deleted student history';
        $data['records'] = StudentDeletedHistory::with('deletedByUser')
            ->latest('deleted_at')
            ->paginate(15);
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['records'],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return view('backend.student-info.student-deleted-history.index', compact('data'));
    }

    public function show(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['record'] = StudentDeletedHistory::with(['feesAssignHistory', 'feesCollectHistory', 'deletedByUser'])->findOrFail($id);
        $data['title'] = ___('common.view_deleted_student') ?? 'View deleted student';
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['record'],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return redirect()->to(spa_url('deleted-history/'.$id));
    }
}
