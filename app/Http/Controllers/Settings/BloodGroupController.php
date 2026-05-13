<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use App\Interfaces\BloodGroupInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\BloodGroup\BloodGroupStoreRequest;
use App\Http\Requests\BloodGroup\BloodGroupUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;

class BloodGroupController extends Controller
{
    private $bloodGroup;

    public function __construct(BloodGroupInterface $bloodGroup)
    {
        if (! Schema::hasTable('settings') && ! Schema::hasTable('users')) {
            abort(400);
        }
        $this->bloodGroup = $bloodGroup;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['bloodGroup'] = $this->bloodGroup->getAll();
        $data['title'] = ___('settings.blood_groups');
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['bloodGroup'],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return redirect()->to(spa_url('blood-groups'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('settings.create_blood_group');
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return redirect()->to(spa_url('blood-groups/create'));
    }

    public function store(BloodGroupStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->bloodGroup->store($request);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('blood-groups.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['bloodGroup'] = $this->bloodGroup->show($id);
        $data['title'] = ___('settings.edit_blood_group');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(spa_url('blood-groups/'.$id.'/edit'));
    }

    public function update(BloodGroupUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->bloodGroup->update($request, $id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('blood-groups.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function delete(Request $request, $id)
    {
        $result = $this->bloodGroup->destroy($id);
        if ($request->expectsJson()) {
            if ($result['status']) {
                return response()->json(['message' => $result['message']]);
            }

            return response()->json(['message' => $result['message']], 422);
        }

        if ($result['status']) {
            return redirect()->route('blood-groups.index')->with('success', $result['message']);
        }

        return back()->with('danger', $result['message']);
    }
}
