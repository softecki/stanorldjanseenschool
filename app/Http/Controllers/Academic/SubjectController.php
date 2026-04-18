<?php

namespace App\Http\Controllers\Academic;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Academic\Subject\SubjectStoreRequest;
use App\Http\Requests\Academic\Subject\SubjectUpdateRequest;
use App\Interfaces\Academic\SubjectInterface;
use Illuminate\Support\Facades\Schema;

class SubjectController extends Controller
{
    private $subject;

    function __construct(SubjectInterface $subject)
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        } 
        $this->subject       = $subject; 
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['subject'] = $this->subject->getAll();
        $data['title'] = ___('academic.subject');
        if ($request->expectsJson()) return response()->json(['data' => $data['subject'], 'meta' => ['title' => $data['title']]]);
        return view('backend.academic.subject.index', compact('data'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']       = ___('academic.create_subject');
        if ($request->expectsJson()) return response()->json(['meta' => ['title' => $data['title']]]);
        return redirect()->to(url('/app/academic/subjects/create'));
    }

    public function store(SubjectStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->subject->store($request);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('subject.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['subject']        = $this->subject->show($id);
        $data['title']       = ___('academic.edit_subject');
        if ($request->expectsJson()) return response()->json(['data' => $data['subject'], 'meta' => ['title' => $data['title']]]);
        return redirect()->to(url('/app/academic/subjects/'.$id.'/edit'));
    }

    public function update(SubjectUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->subject->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('subject.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function delete($id)
    {
        $result = $this->subject->destroy($id);
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
