<?php

namespace App\Http\Controllers\Academic;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Academic\Shift\ShiftStoreRequest;
use App\Http\Requests\Academic\Shift\ShiftUpdateRequest;
use App\Interfaces\Academic\ShiftInterface;
use App\Repositories\LanguageRepository;
use Illuminate\Support\Facades\Schema;

class ShiftController extends Controller
{
    private $shift;
    private $lang_repo;

    function __construct(ShiftInterface $shift , LanguageRepository $lang_repo)
    {
        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        }
        $this->shift       = $shift;
        $this->lang_repo       = $lang_repo;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['shift'] = $this->shift->getAll();
        $data['title'] = ___('academic.shift');
        if ($request->expectsJson()) return response()->json(['data' => $data['shift'], 'meta' => ['title' => $data['title']]]);
        return redirect()->to(url('/app/academic/shifts'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']       = ___('academic.create_shift');
        if ($request->expectsJson()) return response()->json(['meta' => ['title' => $data['title']]]);
        return redirect()->to(url('/app/academic/shifts/create'));
    }

    public function store(ShiftStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->shift->store($request);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('shift.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['shift']        = $this->shift->show($id);
        $data['title']        = ___('academic.edit_shift');
        if ($request->expectsJson()) return response()->json(['data' => $data['shift'], 'meta' => ['title' => $data['title']]]);
        return redirect()->to(url('/app/academic/shifts/'.$id.'/edit'));
    }

    public function update(ShiftUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->shift->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('shift.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }

    public function delete($id)
    {
        $result = $this->shift->destroy($id);
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
        $data['shift']        = $this->shift->show($id);
        $data['translates']      = $this->shift->translates($id);
        $data['languages']      = $this->lang_repo->all();
        $data['title']       = ___('academic.edit_shift');
        if ($request->expectsJson()) return response()->json(['data' => $data['shift'], 'meta' => $data]);
        return redirect()->to(url('/app/academic/shifts/'.$id.'/edit'));
    }

    public function translateUpdate(Request $request, $id): JsonResponse|RedirectResponse{
        $result = $this->shift->translateUpdate($request, $id);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('shift.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }
}
