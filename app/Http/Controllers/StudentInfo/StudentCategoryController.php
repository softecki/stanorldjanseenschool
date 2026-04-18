<?php

namespace App\Http\Controllers\StudentInfo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Repositories\StudentInfo\StudentCategoryRepository;
use App\Http\Requests\StudentInfo\StudentCategory\StudentCategoryStoreRequest;
use App\Http\Requests\StudentInfo\StudentCategory\StudentCategoryUpdateRequest;

class StudentCategoryController extends Controller
{
    private $repo;

    function __construct(StudentCategoryRepository $repo)
    {
        $this->repo       = $repo; 
    }
    
    public function index(Request $request): JsonResponse|View
    {
        $data['title']              = ___('student_info.category_list');
        $data['student_categories'] = $this->repo->getPaginateAll();

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['student_categories'],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return view('backend.student-info.student-category.index', compact('data'));
        
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('student_info.category_create');
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(url('/app/students/categories/create'));
        
    }

    public function store(StudentCategoryStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('student_category.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['student_category']        = $this->repo->show($id);
        $data['title']       = ___('student_info.category_edit');
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['student_category'],
                'meta' => ['title' => $data['title']],
            ]);
        }
        return redirect()->to(url('/app/students/categories/'.$id.'/edit'));
    }

    public function update(StudentCategoryUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if($result){
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.updated_successfully')]);
            }
            return redirect()->route('student_category.index')->with('success', ___('alert.updated_successfully'));
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 500);
        }
        return back()->with('danger', ___('alert.something_went_wrong_please_try_again'));
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
