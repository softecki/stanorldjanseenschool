<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Gmeet\GmeetRepository;
use App\Http\Requests\Gmeet\GmeetStoreRequest;
use App\Http\Requests\Gmeet\GmeetUpdateRequest;
use App\Repositories\Academic\ClassSetupRepository;
use App\Repositories\Academic\SubjectAssignRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class GmeetController extends Controller
{
    private $repo;
    private $classSetupRepo;
    private $subjectAssign;

    function __construct(GmeetRepository $repo, ClassSetupRepository $classSetupRepo, SubjectAssignRepository $subjectAssign)
    {
        $this->repo               = $repo;
        $this->classSetupRepo     = $classSetupRepo;
        $this->subjectAssign      = $subjectAssign;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('common.gmeet');
        $data['classes']            = $this->classSetupRepo->all();
        $data['gmeets']             = $this->repo->getPaginateAll();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['gmeets'],
                'meta' => [
                    'title' => $data['title'],
                    'classes' => $data['classes'],
                ],
            ]);
        }
        return redirect()->to(spa_url('liveclass/gmeet'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']                  = ___('common.gmeet_create');
        $data['classes']                = $this->classSetupRepo->all();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return redirect()->to(spa_url('liveclass/gmeet/create'));
    }

    public function store(GmeetStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('gmeet.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function edit($id, Request $request): JsonResponse|RedirectResponse
    {
        $data['gmeet']              = $this->repo->show($id);
        $data['classes']            = $this->classSetupRepo->all();
        $data['sections']           = $this->classSetupRepo->getSections($data['gmeet']->classes_id);

        $request->merge(['classes_id' => $data['gmeet']->classes_id]);
        $request->merge(['section_id' => $data['gmeet']->section_id]);

        $data['subjects']           = $this->subjectAssign->getSubjects($request);

        
        $data['title']                 = ___('common.gmeet_edit');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('liveclass/gmeet/'.$id.'/edit'));
    }

    public function update(GmeetUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('gmeet.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function delete(Request $request, $id): JsonResponse|RedirectResponse
    {

        $result = $this->repo->destroy($id);
        if($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('gmeet.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function search(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('common.gmeet');
        $data['classes']            = $this->classSetupRepo->all();
        $data['gmeets']             = $this->repo->search($request);
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['gmeets'],
                'meta' => [
                    'title' => $data['title'],
                    'classes' => $data['classes'],
                ],
            ]);
        }
        return redirect()->to(spa_url('liveclass/gmeet'));
    }

}
