<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Department\DepartmentStoreRequest;
use App\Http\Requests\Staff\Department\DepartmentUpdateRequest;
use App\Interfaces\Staff\DepartmentInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    private $repo;

    public function __construct(DepartmentInterface $repo)
    {
        $this->repo = $repo;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['title'] = ___('staff.department');
        $data['departments'] = $this->repo->getPaginateAll();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['departments'], 'meta' => ['title' => $data['title']]]);
        }

        return view('backend.staff.department.index', compact('data'));
    }

    public function create(Request $request): JsonResponse|View
    {
        $data['title'] = ___('staff.department');
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['title' => $data['title']]]);
        }

        return view('backend.staff.department.create', compact('data'));
    }

    public function store(DepartmentStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        $ok = ! empty($result['status']);

        if ($request->expectsJson()) {
            if ($ok) {
                return response()->json(['message' => $result['message'] ?? ___('alert.created_successfully')]);
            }

            return response()->json(['message' => $result['message'] ?? ___('alert.something_went_wrong_please_try_again')], 422);
        }

        if ($ok) {
            return redirect()->route('department.index')->with('success', $result['message']);
        }

        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|View
    {
        $data['department'] = $this->repo->show($id);
        $data['title'] = ___('staff.department');
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['department'],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return view('backend.staff.department.edit', compact('data'));
    }

    public function update(DepartmentUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        $ok = ! empty($result['status']);

        if ($request->expectsJson()) {
            if ($ok) {
                return response()->json(['message' => $result['message'] ?? ___('alert.updated_successfully')]);
            }

            return response()->json(['message' => $result['message'] ?? ___('alert.something_went_wrong_please_try_again')], 422);
        }

        if ($ok) {
            return redirect()->route('department.index')->with('success', $result['message']);
        }

        return back()->with('danger', $result['message']);
    }

    public function delete(Request $request, $id): JsonResponse
    {
        $result = $this->repo->destroy($id);
        $ok = ! empty($result['status']);

        if ($request->expectsJson()) {
            if ($ok) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'] ?? ___('alert.deleted_successfully'),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? ___('alert.something_went_wrong_please_try_again'),
            ], 422);
        }

        if ($ok) {
            $success[0] = $result['message'];
            $success[1] = 'success';
            $success[2] = ___('alert.deleted');
            $success[3] = ___('alert.OK');
        } else {
            $success[0] = $result['message'];
            $success[1] = 'error';
            $success[2] = ___('alert.oops');
            $success[3] = ___('alert.OK');
        }

        return response()->json($success);
    }
}
