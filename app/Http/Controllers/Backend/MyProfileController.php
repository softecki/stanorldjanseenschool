<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Interfaces\UserInterface;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Profile\ProfileUpdateRequest;
use App\Http\Requests\Profile\PasswordUpdateRequest;

class MyProfileController extends Controller
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
        $data['title'] = 'My Profile';
        if ($request->expectsJson()) {
            return response()->json([
                'meta' => ['title' => $data['title']],
                'data' => $this->user->show(Auth::user()->id),
            ]);
        }

        return redirect()->to(url('/my/profile'));
    }

    public function edit(Request $request): JsonResponse|RedirectResponse
    {
        $data['user']        = $this->user->show(Auth::user()->id);
        $data['title']       = "My Profile Edit";
        if ($request->expectsJson()) {
            return response()->json(['data' => $data['user'], 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(url('/my/profile/edit'));
    }

    public function update(ProfileUpdateRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->user->profileUpdate($request,Auth::user()->id);
        if($result){
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.profile_updated_successfully')]);
            }
            return redirect()->route('my.profile')->with('success', ___('alert.profile_updated_successfully'));
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 500);
        }
        return redirect()->route('my.profile')->with('danger', ___('alert.something_went_wrong_please_try_again'));
    }


    public function passwordUpdate(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = 'Password Update';
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(url('/my/password/update'));
    }

    public function passwordUpdateStore(PasswordUpdateRequest $request): JsonResponse|RedirectResponse
    {
        if (Hash::check($request->current_password, Auth::user()->password)) {
            $result = $this->user->passwordUpdate($request,Auth::user()->id);
            if($result){
                if ($request->expectsJson()) {
                    return response()->json(['message' => ___('alert.password_updated_successfully')]);
                }
                return redirect()->route('passwordUpdate')->with('success', ___('alert.password_updated_successfully'));
            }
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 500);
            }
            return redirect()->route('passwordUpdate')->with('danger', ___('alert.something_went_wrong_please_try_again'));
        }else {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Current password is incorrect'], 422);
            }
            return back()->with('danger','Current password is incorrect');
        }
    }
}
