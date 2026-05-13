<?php

namespace App\Http\Controllers\Backend;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Interfaces\RoleInterface;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Interfaces\PermissionInterface;
use App\Http\Requests\Role\RoleStoreRequest;
use App\Http\Requests\Role\RoleUpdateRequest;

class RoleController extends Controller
{
    private $role;
    private $permission;

    function __construct(RoleInterface $role, PermissionInterface $permission)
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        } 
        $this->role       = $role; 
        $this->permission = $permission; 
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['roles'] = $this->role->getAll();
        $data['title'] = ___('common.roles');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['roles'], 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(url('/roles'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']       = ___('common.create_role');
        $data['permissions'] = $this->permission->all();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return redirect()->to(url('/roles/create'));
    }

    public function store(RoleStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->role->store($request);
        if($result){
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.role_created_successfully')]);
            }
            return redirect()->route('roles.index')->with('success', ___('alert.role_created_successfully'));
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 500);
        }
        return redirect()->route('roles.index')->with('danger', ___('alert.something_went_wrong_please_try_again') );
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['role']        = $this->role->show($id);
        $data['title']       = ___('common.roles');
        $data['permissions'] = $this->permission->all();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['role'], 'meta' => ['title' => $data['title'], 'permissions' => $data['permissions']]]);
        }

        return redirect()->to(url('/roles/'.$id.'/edit'));
    }

    public function update(RoleUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->role->update($request, $id);
        if($result){
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.role_updated_successfully')]);
            }
            return redirect()->route('roles.index')->with('success', ___('alert.role_updated_successfully'));
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 500);
        }
        return redirect()->route('roles.index')->with('danger', ___('alert.something_went_wrong_please_try_again'));
    }

    public function delete(Request $request, $id)
    {
        $ok = $this->role->destroy($id);

        if ($request->expectsJson()) {
            if ($ok) {
                return response()->json([
                    'success' => true,
                    'message' => ___('alert.deleted_successfully'),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => ___('alert.something_went_wrong_please_try_again'),
            ], 422);
        }

        if ($ok) {
            $success[0] = ___('alert.deleted_successfully');
            $success[1] = 'success';
            $success[2] = ___('alert.deleted');
            $success[3] = ___('alert.OK');
        } else {
            $success[0] = ___('alert.something_went_wrong_please_try_again');
            $success[1] = 'error';
            $success[2] = ___('alert.oops');
        }

        return response()->json($success);
    }
}
