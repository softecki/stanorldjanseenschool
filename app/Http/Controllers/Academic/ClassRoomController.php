<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Academic\ClassRoom\ClassRoomStoreRequest;
use App\Http\Requests\Academic\ClassRoom\ClassRoomUpdateRequest;
use App\Interfaces\Academic\ClassRoomInterface;

class ClassRoomController extends Controller
{
    private $repo;

    function __construct(ClassRoomInterface $repo)
    {
        $this->repo       = $repo; 
    }
    
    public function index(Request $request): JsonResponse|View
    {
        $data['title']              = ___('academic.class_room');
        $data['class_rooms'] = $this->repo->getPaginateAll();

        if ($request->expectsJson()) return response()->json(['data' => $data['class_rooms'], 'meta' => ['title' => $data['title']]]);
        return view('backend.academic.class-room.index', compact('data'));
        
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('academic.class_room');
        if ($request->expectsJson()) return response()->json(['meta' => ['title' => $data['title']]]);
        return redirect()->to(url('/app/academic/class-rooms/create'));
        
    }

    public function store(ClassRoomStoreRequest $request): JsonResponse|RedirectResponse
    {
        
        $result = $this->repo->store($request);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('class-room.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['class_room']        = $this->repo->show($id);
        $data['title']       = ___('academic.class_room');
        if ($request->expectsJson()) return response()->json(['data' => $data['class_room'], 'meta' => ['title' => $data['title']]]);
        return redirect()->to(url('/app/academic/class-rooms/'.$id.'/edit'));
    }

    public function update(ClassRoomUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('class-room.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
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
