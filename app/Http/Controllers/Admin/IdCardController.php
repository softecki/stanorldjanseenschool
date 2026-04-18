<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\IdCard\IdCardRepository;
use App\Http\Requests\IdCard\IdCardStoreRequest;
use App\Http\Requests\IdCard\IdCardSearchRequest;
use App\Http\Requests\IdCard\IdCardUpdateRequest;
use App\Repositories\Academic\ClassSetupRepository;
use App\Http\Requests\Examination\Homework\HomeworkStoreRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class IdCardController extends Controller
{
    private $repo;
    private $classSetupRepo;

    function __construct(IdCardRepository $repo, ClassSetupRepository $classSetupRepo)
    {
        $this->repo               = $repo;
        $this->classSetupRepo     = $classSetupRepo;  
        
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('common.id_cards');
        $data['id_cards']           = $this->repo->getPaginateAll();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['id_cards'],
                'meta' => ['title' => $data['title']],
            ]);
        }
        return redirect()->to(spa_url('idcard'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']                  = ___('common.id_cards_create');
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return redirect()->to(spa_url('idcard/create'));
    }

    public function store(IdCardStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('idcard.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function edit($id, Request $request): JsonResponse|RedirectResponse
    {
        $data['id_card']              = $this->repo->show($id);

        
        $data['title']                 = ___('common.id_cards_edit');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('idcard/'.$id.'/edit'));
    }

    public function update(IdCardUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('idcard.index')->with('success', $result['message']);
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
            return redirect()->route('idcard.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function preview(Request $request): JsonResponse
    {
        $data['idcard'] = $this->repo->show($request->idcard_id);

        return response()->json($data);

    }

    public function generate(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('common.generate_id_cards');
        $data['id_cards']           = $this->repo->all();
        $data['classes']            = $this->classSetupRepo->all();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return redirect()->to(spa_url('idcard/generate'));
    }

    public function generateSearch(IdCardSearchRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->generateSearch($request);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'data' => [
                        'idcard' => $result['data']['idcard'],
                        'students' => $result['data']['students'],
                    ],
                    'meta' => [
                        'title' => ___('common.generate_id_cards'),
                        'id_cards' => $this->repo->all(),
                        'classes' => $this->classSetupRepo->all(),
                    ],
                ]);
            }
            return redirect()->to(spa_url('idcard/generate'));
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);

    }

}
