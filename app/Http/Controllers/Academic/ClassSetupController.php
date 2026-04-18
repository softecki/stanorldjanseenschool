<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Academic\ClassSetup\ClassSetupStoreRequest;
use App\Http\Requests\Academic\ClassSetup\ClassSetupUpdateRequest;
use App\Interfaces\Academic\ClassesInterface;
use App\Interfaces\Academic\ClassSetupInterface;
use App\Interfaces\Academic\SectionInterface;
use App\Interfaces\SessionInterface;

class ClassSetupController extends Controller
{
    private $repo;
    private $session;
    private $classes;
    private $section;

    function __construct(
        ClassSetupInterface $repo,
        SessionInterface $session,
        ClassesInterface $classes,
        SectionInterface $section
        )
    {
        $this->repo          = $repo; 
        $this->session       = $session; 
        $this->classes       = $classes; 
        $this->section       = $section; 
    }

    public function getSections(Request $request){
        $data = $this->repo->getSections($request->id);
        return response()->json($data);
    }
    
    public function index(Request $request): JsonResponse|View
    {
        $data['title']              = ___('academic.class_setup');
        $data['class_setups']       = $this->repo->getPaginateAll();
        if ($request->expectsJson()) return response()->json(['data' => $data['class_setups'], 'meta' => ['title' => $data['title']]]);
        return view('backend.academic.class_setup.index', compact('data'));
        
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('academic.class_setup');
        $data['classes']            = $this->classes->all();
        $data['section']            = $this->section->all();
        if ($request->expectsJson()) return response()->json(['meta' => $data]);
        return redirect()->to(url('/app/academic/class-setups/create'));
        
    }

    public function store(ClassSetupStoreRequest $request): JsonResponse|RedirectResponse
    {
        // dd($request->all());
        $result = $this->repo->store($request);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('class-setup.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['class_setup']        = $this->repo->show($id);
        $data['title']              = ___('academic.class_setup');
        $data['classes']            = $this->classes->all();
        $data['section']            = $this->section->all();

        $data['class_setup_sections']  = $data['class_setup']->classSetupChildrenAll->pluck('section_id')->toArray();

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['class_setup'],
                'meta' => [
                    'title' => $data['title'],
                    'classes' => $data['classes'],
                    'section' => $data['section'],
                    'class_setup_sections' => $data['class_setup_sections'],
                ],
            ]);
        }
        return redirect()->to(url('/app/academic/class-setups/'.$id.'/edit'));
    }

    public function update(ClassSetupUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('class-setup.index')->with('success', $result['message']);
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
