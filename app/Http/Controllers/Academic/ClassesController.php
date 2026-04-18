<?php

namespace App\Http\Controllers\Academic;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use App\Repositories\LanguageRepository;
use App\Interfaces\Academic\ClassesInterface;
use App\Http\Requests\Academic\Classes\ClassesStoreRequest;
use App\Http\Requests\Academic\Classes\ClassesUpdateRequest;

class ClassesController extends Controller
{
    private $classes;
    private $lang_repo;

    function __construct(ClassesInterface $classes, LanguageRepository $lang_repo)
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        }
        $this->classes       = $classes;
        $this->lang_repo       = $lang_repo;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['class'] = $this->classes->getAll();
        $data['title'] = ___('academic.class');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['class'], 'meta' => ['title' => $data['title']]]);
        }
        return view('spa.app');
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']       = ___('academic.create_class');
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(url('/classes/create'));
    }

    public function store(ClassesStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->classes->store($request);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('classes.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['class']       = $this->classes->show($id);
        $data['title']       = ___('academic.edit_class');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['class'], 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(url('/classes/'.$id.'/edit'));
    }

    public function translate(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['class']        = $this->classes->show($id);
        $data['translates']      = $this->classes->translates($id);
        $data['languages']      = $this->lang_repo->all();
        $data['title']       = ___('academic.edit_class');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['class'], 'meta' => $data]);
        }
        return redirect()->to(url('/classes/'.$id.'/edit'));
    }

    public function translateUpdate(Request $request, $id): JsonResponse|RedirectResponse{

        $result = $this->classes->translateUpdate($request, $id);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('classes.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function update(ClassesUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->classes->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('classes.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function delete($id)
    {
        $result = $this->classes->destroy($id);
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
