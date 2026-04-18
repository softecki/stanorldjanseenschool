<?php

namespace App\Http\Controllers\Fees;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fees\Type\FeesTypeStoreRequest;
use App\Http\Requests\Fees\Type\FeesTypeUpdateRequest;
use App\Interfaces\Fees\FeesTypeInterface;
use App\Models\Academic\Classes;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class FeesTypeController extends Controller
{
    private $repo;

    function __construct(FeesTypeInterface $repo)
    {
        $this->repo       = $repo; 
    }
    
    public function index(Request $request): JsonResponse|View
    {
        $data['title']              = ___('fees.fees_type');
        $data['fees_types'] = $this->repo->getPaginateAll();

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['fees_types'],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return view('backend.fees.type.index', compact('data'));
        
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('fees.fees_type');
        $data['classes']              = Classes::all();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return redirect()->to(url('/app/fees/types/create'));
        
    }

    public function store(FeesTypeStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('fees-type.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['fees_type']        = $this->repo->show($id);
        $data['title']       = ___('fees.fees_type');
        $data['classes']              = Classes::all();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['fees_type'],
                'meta' => ['title' => $data['title'], 'classes' => $data['classes']],
            ]);
        }

        return redirect()->to(url('/app/fees/types/'.$id.'/edit'));
    }

    public function update(FeesTypeUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('fees-type.index')->with('success', $result['message']);
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
