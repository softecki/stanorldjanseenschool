<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Department\DepartmentStoreRequest;
use App\Http\Requests\Staff\Department\DepartmentUpdateRequest;
use App\Interfaces\Staff\DepartmentInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    private $repo;

    function __construct(DepartmentInterface $repo)
    {
        $this->repo       = $repo; 
    }
    
    public function index(Request $request): JsonResponse|\Illuminate\View\View
    {
        $data['title']              = ___('staff.department');
        $data['departments'] = $this->repo->getPaginateAll();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['departments'], 'meta' => ['title' => $data['title']]]);
        }

        return view('backend.staff.department.index', compact('data'));
        
    }

    public function create()
    {
        $data['title']              = ___('staff.department');
        return view('backend.staff.department.create', compact('data'));
        
    }

    public function store(DepartmentStoreRequest $request)
    {
        $result = $this->repo->store($request);
        if($result['status']){
            return redirect()->route('department.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message']);
    }

    public function edit($id)
    {
        $data['department']        = $this->repo->show($id);
        $data['title']       = ___('staff.department');
        return view('backend.staff.department.edit', compact('data'));
    }

    public function update(DepartmentUpdateRequest $request, $id)
    {
        $result = $this->repo->update($request, $id);
        if($result){
            return redirect()->route('department.index')->with('success', $result['message']);
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
