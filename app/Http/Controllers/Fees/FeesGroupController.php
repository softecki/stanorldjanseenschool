<?php

namespace App\Http\Controllers\Fees;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fees\Group\FeesGroupStoreRequest;
use App\Http\Requests\Fees\Group\FeesGroupUpdateRequest;
use App\Interfaces\Fees\FeesGroupInterface;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class FeesGroupController extends Controller
{
    private $repo;

    function __construct(FeesGroupInterface $repo)
    {
        $this->repo       = $repo;
    }

    public function index(Request $request): JsonResponse|View
    {
        $data['title']              = ___('fees.fees_group');
        $data['fees_groups'] = $this->repo->getPaginateAll();

        if ($request->expectsJson()) {
            return response()->json(['data' => $data['fees_groups'], 'meta' => ['title' => $data['title']]]);
        }

        return view('backend.fees.group.index', compact('data'));

    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']              = ___('fees.fees_group');
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(url('/app/fees/groups/create'));

    }

    public function store(FeesGroupStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->repo->store($request);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('fees-group.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $feesGroup = $this->repo->show($id);
        if ($feesGroup === null) {
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.no_data_found')], 404);
            }
            abort(404);
        }

        $data['fees_group'] = $feesGroup;
        $data['title'] = ___('fees.fees_group');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['fees_group'], 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(url('/app/fees/groups/'.$id.'/edit'));
    }

    public function update(FeesGroupUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->repo->update($request, $id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('fees-group.index')->with('success', $result['message']);
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
