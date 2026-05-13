<?php

namespace App\Http\Controllers\Academic;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Academic\Section\SectionStoreRequest;
use App\Http\Requests\Academic\Section\SectionUpdateRequest;
use App\Interfaces\Academic\SectionInterface;
use App\Repositories\LanguageRepository;
use Illuminate\Support\Facades\Schema;

class SectionController extends Controller
{
    private $section;
    private $lang_repo;

    function __construct(SectionInterface $section , LanguageRepository $lang_repo)
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        }
        $this->section       = $section;
        $this->lang_repo       = $lang_repo;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['section'] = $this->section->getAll();
        $data['title'] = '';
        if ($request->expectsJson()) return response()->json(['data' => $data['section'], 'meta' => ['title' => $data['title']]]);
        return view('backend.academic.section.index', compact('data'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']       = ___('academic.create_section');
        if ($request->expectsJson()) return response()->json(['meta' => ['title' => $data['title']]]);
        return redirect()->to(spa_url('sections/create'));
    }

    public function store(SectionStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->section->store($request);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('section.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['section']        = $this->section->show($id);
        $data['title']       = ___('academic.edit_section');
        if ($request->expectsJson()) return response()->json(['data' => $data['section'], 'meta' => ['title' => $data['title']]]);
        return redirect()->to(spa_url('sections/'.$id.'/edit'));
    }

    public function update(SectionUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->section->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('section.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function delete($id)
    {
        $result = $this->section->destroy($id);
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


    public function translate(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['section']        = $this->section->show($id);
        $data['translates']      = $this->section->translates($id);
        $data['languages']      = $this->lang_repo->all();
        $data['title']       = ___('academic.edit_section');
        if ($request->expectsJson()) return response()->json(['data' => $data['section'], 'meta' => $data]);
        return redirect()->to(spa_url('sections/'.$id.'/edit'));
    }

    public function translateUpdate(Request $request, $id): JsonResponse|RedirectResponse{

        $result = $this->section->translateUpdate($request, $id);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('section.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }
}
