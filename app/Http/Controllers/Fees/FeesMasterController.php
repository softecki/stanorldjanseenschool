<?php

namespace App\Http\Controllers\Fees;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fees\Master\FeesMasterQuartersUpdateRequest;
use App\Http\Requests\Fees\Master\FeesMasterStoreRequest;
use App\Http\Requests\Fees\Master\FeesMasterUpdateRequest;
use App\Interfaces\Fees\FeesGroupInterface;
use App\Interfaces\Fees\FeesMasterInterface;
use App\Interfaces\Fees\FeesTypeInterface;
use App\Repositories\Academic\ClassesRepository;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class FeesMasterController extends Controller
{
    private $repo;
    private $type;
    private $group;
    private $classRepo;

    function __construct(FeesMasterInterface $repo,FeesTypeInterface $type,FeesGroupInterface $group, ClassesRepository $classRepo)
    {
        $this->repo       = $repo; 
        $this->type       = $type; 
        $this->group      = $group; 
        $this->classRepo  = $classRepo; 
    }
    
    public function index(Request $request): JsonResponse|View
    {
        $data['title']        = ___('fees.fees_master');
        $data['fees_masters'] = $this->repo->getPaginateAll();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['fees_masters'], 'meta' => ['title' => $data['title']]]);
        }

        return view('backend.fees.master.index', compact('data'));
    }
    
    public function getAllTypes(Request $request)
    {
        $types = $this->repo->groupTypes($request);
        return view('backend.fees.master.fees-types', compact('types'))->render();
    }

    public function quartersOverview(Request $request): JsonResponse
    {
        $data['title'] = ___('fees.fees_master');
        $data['fees_masters'] = $this->repo->quartersOverview();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['fees_masters'], 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(url('/app/fees/masters'));
    }

    public function quartersUpdate(FeesMasterQuartersUpdateRequest $request, int $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->syncMasterQuarters($id, $request->input('amounts', []));
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('fees-master.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('fees.fees_master');
        $data['fees_types'] = $this->type->all();
        $data['fees_groups'] = $this->group->all();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return redirect()->to(url('/app/fees/masters/create'));
    }

    public function store(FeesMasterStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('fees-master.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $feesMaster = $this->repo->show($id);
        if ($feesMaster === null) {
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.no_data_found')], 404);
            }
            abort(404);
        }

        $data['title'] = ___('fees.fees_master');
        $data['fees_master'] = $feesMaster;
        $data['fees_types'] = $this->type->all();
        $data['fees_groups'] = $this->group->all();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['fees_master'],
                'meta' => [
                    'title' => $data['title'],
                    'fees_types' => $data['fees_types'],
                    'fees_groups' => $data['fees_groups'],
                ],
            ]);
        }

        return redirect()->to(url('/app/fees/masters/'.$id.'/edit'));
    }

    public function update(FeesMasterUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('fees-master.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
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
