<?php

namespace App\Http\Controllers\StudentPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\UserInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Http\Requests\StudentPanel\Profile\ProfileUpdateRequest;
use App\Http\Requests\StudentPanel\Profile\PasswordUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    private $user;

    function __construct(UserInterface $userInterface)
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        } 
        $this->user       = $userInterface;
    }

    public function profile(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = 'Profile';
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('student-panel/profile'));
    }

    public function edit(Request $request): JsonResponse|RedirectResponse
    {
        $data['user']        = $this->user->show(Auth::user()->id);
        $data['title']       = "Profile Edit";
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('student-panel/profile/edit'));
    }

    public function update(ProfileUpdateRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->user->profileUpdate($request,Auth::user()->id);
        if($result){
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.profile_updated_successfully')]);
            }
            return redirect()->route('student-panel.profile')->with('success', ___('alert.profile_updated_successfully'));
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
        }
        return redirect()->route('student-panel.profile')->with('danger', ___('alert.something_went_wrong_please_try_again'));
    }


    public function passwordUpdate(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = 'Password Update';
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('student-panel/password/update'));
    }

    public function passwordUpdateStore(PasswordUpdateRequest $request): JsonResponse|RedirectResponse
    {
        if (Hash::check($request->current_password, Auth::user()->password)) {
            $result = $this->user->passwordUpdate($request,Auth::user()->id);
            if($result){
                if ($request->expectsJson()) {
                    return response()->json(['message' => ___('alert.password_updated_successfully')]);
                }
                return redirect()->route('student-panel.password-update')->with('success', ___('alert.password_updated_successfully'));
            }
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
            }
            return redirect()->route('student-panel.password-update')->with('danger', ___('alert.something_went_wrong_please_try_again'));
        }else {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Current password is incorrect'], 422);
            }
            return back()->with('danger','Current password is incorrect');
        }
    }
}
