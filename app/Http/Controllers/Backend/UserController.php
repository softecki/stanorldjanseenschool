<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\RoleInterface;
use App\Interfaces\UserInterface;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use App\Interfaces\PermissionInterface;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Interfaces\GenderInterface;
use App\Interfaces\Staff\DepartmentInterface;
use App\Interfaces\Staff\DesignationInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;


class UserController extends Controller
{

    private $user;
    private $permission;
    private $role;
    private $designation;
    private $department;
    private $gender;

    function __construct(
        UserInterface $user, 
        PermissionInterface $permission, 
        RoleInterface $role,
        DesignationInterface $designation,
        DepartmentInterface $department,
        GenderInterface $gender,
        
        )
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        } 
        $this->user         = $user;
        $this->permission   = $permission;
        $this->role         = $role;
        $this->designation  = $designation;
        $this->department   = $department;
        $this->gender       = $gender;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['users'] = $this->user->getAll();
        $data['title'] = ___('staff.staff');
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['users'],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return redirect()->to(url('/users'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']         = ___('staff.create_staff');
        $data['permissions']   = $this->permission->all();
        $data['roles']         = $this->role->all();
        $data['designations']  = $this->designation->all();
        $data['departments']   = $this->department->all();
        $data['genders']       = $this->gender->all();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return redirect()->to(url('/users/create'));
    }

    public function upload()
    {
        $data['title']         = ___('staff.create_staff');
        $data['permissions']   = $this->permission->all();
        $data['roles']         = $this->role->all();
        $data['designations']  = $this->designation->all();
        $data['departments']   = $this->department->all();
        $data['genders']       = $this->gender->all();
        return view('backend.users.upload', compact('data'));
    }

    public function store(UserStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->user->store( $request);
        if ($result == 2) {
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.Staff limit is over.')], 422);
            }
            return redirect()->route('users.index')->with('danger', ___('alert.Staff limit is over.'));
        }
        elseif ($result == 1) {
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.user_created_successfully')]);
            }
            return redirect()->route('users.index')->with('success', ___('alert.user_created_successfully'));
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 500);
        }

        return redirect()->route('users.index')->with('danger',  ___('alert.something_went_wrong_please_try_again'));
    }

    public function uploadTeachers(Request $request)
    {
        $result = $this->user->upload( $request);
        if ($result == 2) {
            return redirect()->route('users.index')->with('danger', ___('alert.Staff limit is over.'));
        }
        elseif ($result == 1) {
            return redirect()->route('users.index')->with('success', ___('alert.user_created_successfully'));
        }
        return redirect()->route('users.index')->with('danger',  ___('alert.something_went_wrong_please_try_again'));
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['user']          = $this->user->show($id);
        $data['title']         = ___('staff.update_staff');
        $data['permissions']   = $this->permission->all();
        $data['roles']         = $this->role->all();
        $data['designations']  = $this->designation->all();
        $data['departments']   = $this->department->all();
        $data['genders']       = $this->gender->all();
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['user'],
                'meta' => [
                    'title' => $data['title'],
                    'permissions' => $data['permissions'],
                    'roles' => $data['roles'],
                    'designations' => $data['designations'],
                    'departments' => $data['departments'],
                    'genders' => $data['genders'],
                ],
            ]);
        }

        return redirect()->to(url('/users/'.$id.'/edit'));
    }

    public function show(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data = $this->user->show($id);
        if ($request->expectsJson()) {
            return response()->json(['data' => $data]);
        }

        return redirect()->to(url('/users/'.$id));
    }

    public function update(UserUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->user->update($request, $id);
        if ($result) {
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.user_updated_successfully')]);
            }
            return redirect()->route('users.index')->with('success', ___('alert.user_updated_successfully'));
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 500);
        }

        return redirect()->route('users.index')->with('danger',  ___('alert.something_went_wrong_please_try_again'));
    }

    public function delete(Request $request, $id)
    {
        $ok = $this->user->destroy($id);

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
            $success[1] = 'Success';
            $success[2] = ___('alert.deleted');
            $success[3] = ___('alert.OK');
        } else {
            $success[0] = ___('alert.something_went_wrong_please_try_again');
            $success[1] = 'error';
            $success[2] = ___('alert.oops');
        }

        return response()->json($success);
    }

    public function changeRole(Request $request)
    {
        $data['role_permissions'] = $this->role->show($request->role_id)->permissions;
        $data['permissions']      = $this->permission->all();
        return view('backend.users.permissions', compact('data'))->render();
    }

    public function status(Request $request)
    {

        if ($request->type == 'active') {
            $request->merge([
                'status' => 1
            ]);
            $this->user->status($request);
        }

        if ($request->type == 'inactive') {
            $request->merge([
                'status' => 0
            ]);
            $this->user->status($request);
        }

        return response()->json(["message" => __("Status update successful")], 200);
    }

    public function deletes(Request $request)
    {
        $this->user->deletes($request);

        return response()->json(["message" => __('Delete successful.')], 200);
    }
}
