<?php

namespace App\Http\Controllers\Backend;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Interfaces\AuthenticationRepositoryInterface;

class AuthenticationController extends Controller
{
    private $loginRepository;

    public function __construct(AuthenticationRepositoryInterface $loginRepository)
    {
        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {

            Artisan::call('migrate:fresh', ['--force' => true ]);
            Artisan::call('db:seed', ['--force' => true ]);
        } 
        $this->loginRepository = $loginRepository;

    }

    public function loginPage(Request $request): JsonResponse|View
    {
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['title' => 'Login']]);
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $email      = $request->safe()->only(['email']);
        $password   = $request->safe()['password'];

        $user       = User::query()->firstWhere('email', $email);
        if(!$user)
            $user   = User::query()->firstWhere('phone', $email);
        

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => ___('users_roles.the_provided_email_do_not_match_our_records'),
                ], 422);
            }
            return back()->withErrors([
                'email' =>  ___('users_roles.the_provided_email_do_not_match_our_records')
            ]);
        }

        if (!Hash::check($password, $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => ___('users_roles.the_provided_password_does_not_match_our_records'),
                ], 422);
            }
            return back()->withErrors([
                'password' => ___('users_roles.the_provided_password_does_not_match_our_records')
            ]);
        }

        if($user->email_verified_at == null){
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('users_roles.account_not_verified_yet')], 422);
            }
            return back()->with('danger', ___('users_roles.account_not_verified_yet'));
        }

        if($user->status == 0){
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('users_roles.you_are_inactive')], 422);
            }
            return back()->with('danger', ___('users_roles.you_are_inactive'));
        }
        if($user->role->status == 0){
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('users_roles.this_user_role_is_inactive')], 422);
            }
            return back()->with('danger', ___('users_roles.this_user_role_is_inactive'));
        }

        if($this->loginRepository->login($request->all())) {
            if ($request->expectsJson()) {
                if($user->role_id == 6) {
                    return response()->json(['message' => 'OK', 'redirect' => '/student-panel']);
                } elseif($user->role_id == 7) {
                    return response()->json(['message' => 'OK', 'redirect' => '/parent-panel']);
                }

                return response()->json(['message' => 'OK', 'redirect' => '/dashboard']);
            }
            if($user->role_id == 6) {
                return redirect()->to(spa_url('student-panel'));
            }
            if($user->role_id == 7) {
                return redirect()->to(spa_url('parent-panel'));
            }

            return redirect()->to(spa_url('dashboard'));
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => ___('users_roles.something_went_wrong_please_try_again')], 500);
        }
        return back()->with('danger', ___('users_roles.something_went_wrong_please_try_again'));

    }

    public function registerPage(Request $request): JsonResponse|View
    {
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['title' => 'Create Account']]);
        }

        $data['title'] = ___('common.create_account');

        return view('backend.auth.register', compact('data'));
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->loginRepository->register($request);

        if ($user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('users_roles.we_have_send_you_an_email_please_verify_your_email_address')]);
            }
            return redirect()->to(spa_url('login'))->with('success',  ___('users_roles.we_have_send_you_an_email_please_verify_your_email_address'));
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => ___('users_roles.something_went_wrong_please_try_again')], 500);
        }
        return back()->with('danger',  ___('users_roles.something_went_wrong_please_try_again'));
    }

    public function verifyEmail(Request $request, $email, $token): JsonResponse|RedirectResponse
    {
        $result = $this->loginRepository->verifyEmail($email, $token);

        if($result == 'success') {
            $message = ___('users_roles.your_email_has_been_verified_please_login');
            if ($request->expectsJson()) {
                return response()->json(['status' => 'success', 'message' => $message]);
            }
            return redirect()->to(url('/login?noticeType=success&notice='.urlencode($message)));
        } elseif($result == 'already_verified') {
            $message = ___('users_roles.your_email_has_already_been_verified_please_login');
            if ($request->expectsJson()) {
                return response()->json(['status' => 'success', 'message' => $message]);
            }
            return redirect()->to(url('/login?noticeType=success&notice='.urlencode($message)));
        } elseif($result == 'invalid_email') {
            $message = ___('users_roles.invalid_email_address');
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 422);
            }
            return redirect()->to(url('/login?noticeType=error&notice='.urlencode($message)));
        } elseif($result == 'invalid_token') {
            $message = ___('users_roles.invalid_token');
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 422);
            }
            return redirect()->to(url('/login?noticeType=error&notice='.urlencode($message)));
        } else {
            $message = ___('users_roles.something_went_wrong_please_try_again');
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 500);
            }
            return redirect()->to(url('/login?noticeType=error&notice='.urlencode($message)));
        }
    }


    public function logout(Request $request)
    {
        $this->loginRepository->logout();

        return redirect()->to(spa_url('login'));
    }

    public function forgotPasswordPage(Request $request): JsonResponse|View
    {
        if ($request->expectsJson()) {
            return response()->json(['meta' => ['title' => 'Forgot Password']]);
        }

        $data['title'] = ___('common.forgot_password');

        return view('backend.auth.forgot-password', compact('data'));
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $result = $this->loginRepository->forgotPassword($request);

        if ($result == 'success') {
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('users_roles.we_have_sent_an_reset_password_link_to_your_email_address')]);
            }
            return back()->with('success',  ___('users_roles.we_have_sent_an_reset_password_link_to_your_email_address'));
        } elseif ($result == 'invalid_email') {
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('users_roles.invalid_email_address')], 422);
            }
            return back()->with('danger',  ___('users_roles.invalid_email_address'));
        } else {
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('users_roles.something_went_wrong_please_try_again')], 500);
            }
            return back()->with('danger',  ___('users_roles.something_went_wrong_please_try_again'));
        }
    }

    public function resetPasswordPage(Request $request, $email, $token): JsonResponse|RedirectResponse|View
    {
        $result = $this->loginRepository->resetPasswordPage($email, $token);

        if ($result == 'success') {
            if ($request->expectsJson()) {
                return response()->json([
                    'meta' => [
                        'title' => 'Reset Password',
                        'email' => $email,
                        'token' => $token,
                    ],
                ]);
            }

            $data = [
                'title' => ___('common.reset_passowrd'),
                'email' => $email,
                'token' => $token,
            ];

            return view('backend.auth.reset-password', compact('data'));

        } elseif ($result == 'invalid_email') {
            return redirect()->to(spa_url('login'))->with('danger',  ___('users_roles.invalid_email_address'));
        } elseif ($result == 'invalid_token') {
            return redirect()->to(spa_url('login'))->with('danger',  ___('users_roles.invalid_token'));
        } else {
            return redirect()->to(spa_url('login'))->with('danger',  ___('users_roles.something_went_wrong_please_try_again'));
        }

    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $result = $this->loginRepository->resetPassword($request);

        if ($result == 'success') {
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('users_roles.your_password_has_been_reset_please_login')]);
            }
            return redirect()->to(spa_url('login'))->with('success', ___('users_roles.your_password_has_been_reset_please_login'));
        } elseif ($result == 'invalid_email') {
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('users_roles.invalid_email_address')], 422);
            }
            return back()->with('danger',  ___('users_roles.invalid_email_address'));
        } elseif ($result == 'invalid_token') {
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('users_roles.invalid_token')], 422);
            }
            return back()->with('danger',  ___('users_roles.invalid_token'));
        } else {
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('users_roles.something_went_wrong_please_try_again')], 500);
            }
            return back()->with('danger',  ___('users_roles.something_went_wrong_please_try_again'));
        }
    }


}
