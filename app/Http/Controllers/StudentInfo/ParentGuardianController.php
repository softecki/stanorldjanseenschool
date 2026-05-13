<?php

namespace App\Http\Controllers\StudentInfo;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Repositories\StudentInfo\ParentGuardianRepository;
use App\Http\Requests\StudentInfo\ParentGuardian\ParentGuardianStoreRequest;
use App\Http\Requests\StudentInfo\ParentGuardian\ParentGuardianUpdateRequest;

class ParentGuardianController extends Controller
{
    private $repo;

    function __construct(ParentGuardianRepository $repo)
    {
        $this->repo       = $repo; 
    }
    
    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']   = ___('student_info.parent_list');
        $data['parents'] = $this->repo->getPaginateAll();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['parents'], 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(spa_url('parents'));
    }

    public function search(Request $request)
    {
        $data['title']    = ___('student_info.parent_list');
        $data['request'] = $request;
        $data['parents']  = $this->repo->searchParent($request);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['parents'],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return view('backend.student-info.parent.index', compact('data'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('student_info.parent_create');
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(spa_url('parents/create'));
    }

    public function getParent(Request $request)
    {
        $result = $this->repo->getParent($request);
        return response()->json($result);
    }

    public function store(ParentGuardianStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('parent.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['parent']      = $this->repo->show($id);
        $data['title']       = ___('student_info.parent_edit');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['parent'], 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(spa_url('parents/'.$id.'/edit'));
    }

    public function show(Request $request, $id): JsonResponse|RedirectResponse
    {
        $parent = $this->repo->show($id);
        if ($parent === null) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Parent not found.'], 404);
            }

            return redirect()->to(spa_url('parents'))->with('danger', 'Parent not found.');
        }

        $parent->load('user');

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $parent,
                'meta' => ['title' => ___('student_info.parent_list')],
            ]);
        }

        return redirect()->to(spa_url('parents/'.$id));
    }

    public function update(ParentGuardianUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if ($result['status'] ?? false) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('parent.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message'] ?? 'Update failed.'], 422);
        }

        return back()->with('danger', $result['message'] ?? 'Update failed.');
    }

    public function delete($id)
    {
        $result = $this->repo->destroy($id);
        if($result['status']):
            $success[0] = $result['message'];
            $success[1] = 'success';
            $success[2] = ___('alert.deleted');
            $success[3] = ___('alert.OK');
            return response()->json($success);
        else:
            $success[0] = $result['message'];
            $success[1] = 'error';
            $success[2] = ___('alert.oops');
            return response()->json($success);
        endif;      
    }
}
