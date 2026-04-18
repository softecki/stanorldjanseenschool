<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;
use App\Http\Controllers\Controller;
use App\Interfaces\SettingInterface;
use App\Http\Requests\SmsStoreRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\SettingStoreRequest;
use App\Http\Requests\Settings\EmailSettingStoreRequest;
use App\Http\Requests\GeneralSetting\StorageUpdateRequest;
use App\Http\Requests\GeneralSetting\GeneralSettingStoreRequest;
use App\Repositories\LanguageRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    private $setting;
    private $lang_repo;

    function __construct(SettingInterface $settingInterface, LanguageRepository $lang_repo)
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        }
        $this->setting = $settingInterface;
        $this->lang_repo = $lang_repo;
    }

    // General setting start
    public function generalSettings(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']      = ___('common.general_settings');
        $data['data']       = $this->setting->getAll();
        $data['languages']  = $this->setting->getLanguage();
        $data['sessions']   = $this->setting->getSessions();
        $data['currencies'] = $this->setting->getCurrencies();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return redirect()->to(url('/settings/general'));
    }

    public function updateGeneralSetting(GeneralSettingStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->setting->updateGeneralSetting($request);
        if ($result) {
            if ($request->expectsJson()) return response()->json(['message' => ___('alert.general_settings_updated_successfully')]);
            return redirect()->back()->with('success', ___('alert.general_settings_updated_successfully'));
        }
        if ($request->expectsJson()) return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
        return redirect()->back()->with('danger', ___('alert.something_went_wrong_please_try_again'));
    }
    // General setting end

    // Storage setting start
    public function storagesetting(Request $request): JsonResponse|RedirectResponse
    {

        try {
            $data['title'] = ___('common.storage_settings');
            $data['data']  = $this->setting->getAll();
            if ($request->expectsJson()) {
                return response()->json(['meta' => $data]);
            }
            return redirect()->to(spa_url('settings/storage'));
        } catch (\Throwable $th) {
            return redirect('/');
        }
    }

    public function storageSettingUpdate(StorageUpdateRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $result = $this->setting->storageSettingUpdate($request);
            if ($request->expectsJson()) return response()->json(['message' => ___('alert.storage_settings_updated_successfully')]);
            return back()->with('success', ___('alert.storage_settings_updated_successfully'));
        } catch (\Throwable $th) {
            if ($request->expectsJson()) return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
            return back()->with('danger', ___('alert.something_went_wrong_please_try_again'));
        }
    }
    // Storage setting start

    // Recaptcha setting start
    public function recaptchaSetting(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('common.recaptcha_settings');
        $data['data']  = $this->setting->getAll();
        if ($request->expectsJson()) return response()->json(['meta' => $data]);
        return redirect()->to(spa_url('settings/recaptcha'));
    }

    public function updateRecaptchaSetting(SettingStoreRequest $request): JsonResponse|RedirectResponse
    {
        // return $request;
        $result = $this->setting->updateRecaptchaSetting($request);
        // dd($request);
        if ($result) {
            if ($request->expectsJson()) return response()->json(['message' => ___('alert.recaptcha_settings_updated_successfully')]);
            return redirect()->back()->with('success', ___('alert.recaptcha_settings_updated_successfully'));
        }
        if ($request->expectsJson()) return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
        return redirect()->back()->with('danger', ___('alert.something_went_wrong_please_try_again'));
    }
    // Recaptcha setting end

    // Recaptcha setting start
    public function smsSetting(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('settings.sms_settings');
        $data['data']  = $this->setting->getAll();
        if ($request->expectsJson()) return response()->json(['meta' => $data]);
        return redirect()->to(spa_url('settings/sms'));
    }

    public function updateSmsSetting(SmsStoreRequest $request): JsonResponse|RedirectResponse
    {
        // return $request;
        $result = $this->setting->updateSmsSetting($request);
        // dd($request);
        if ($result) {
            if ($request->expectsJson()) return response()->json(['message' => ___('alert.updated_successfully')]);
            return redirect()->back()->with('success', ___('alert.updated_successfully'));
        }
        if ($request->expectsJson()) return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
        return redirect()->back()->with('danger', ___('alert.something_went_wrong_please_try_again'));
    }
    // Recaptcha setting end

    // Payment Gateway setting start
    public function paymentGatewaySetting(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('common.payment_gateway_settings');
        $data['data']  = $this->setting->getAll();
        if ($request->expectsJson()) return response()->json(['meta' => $data]);
        return redirect()->to(spa_url('settings/payment'));
    }

    public function updatePaymentGatewaySetting(Request $request): JsonResponse|RedirectResponse
    {
        // return $request;
        $result = $this->setting->updatePaymentGatewaySetting($request);
        // dd($request);
        if ($result) {
            if ($request->expectsJson()) return response()->json(['message' => ___('alert.payment_gateway_settings_updated_successfully')]);
            return redirect()->back()->with('success', ___('alert.payment_gateway_settings_updated_successfully'));
        }
        if ($request->expectsJson()) return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
        return redirect()->back()->with('danger', ___('alert.something_went_wrong_please_try_again'));
    }
    // Payment Gateway setting end

    // mail settings start
    public function mailSetting(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('settings.email_settings');
        $data['data']  = $this->setting->getAll();
        if ($request->expectsJson()) return response()->json(['meta' => $data]);
        return redirect()->to(spa_url('settings/mail'));
    }

    public function updateMailSetting(EmailSettingStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->setting->updateMailSetting($request);

        if ($result) {
            if ($request->expectsJson()) return response()->json(['message' => ___('alert.email_settings_updated_successfully')]);
            return redirect()->back()->with('success', ___('alert.email_settings_updated_successfully'));
        }
        if ($request->expectsJson()) return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
        return redirect()->back()->with('danger', ___('alert.something_went_wrong_please_try_again'));
    }
    // mail settings end


    public function changeTheme(Request $request)
    {
        Session::put('user_theme', $request->theme_mode);
        return true;
    }

    public function taskSchedulers(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']      = ___('settings.task_schedules');
        if ($request->expectsJson()) return response()->json(['meta' => $data]);
        return redirect()->to(spa_url('settings/task-schedulers'));
    }
    public function resultGenerate()
    {
        try {
            \Artisan::call('exam:result-generate');
            return redirect()->back()->with('success', ___('alert.run_successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('danger', ___('alert.something_went_wrong_please_try_again'));
        }
    }




    public function softwareUpdate(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']      = ___('settings.software_update');
        if ($request->expectsJson()) return response()->json(['meta' => $data]);
        return redirect()->to(spa_url('settings/software-update'));
    }
    public function installUpdate()
    {
        try {
            \Artisan::call('migrate');
            return redirect()->back()->with('success', ___('alert.updated_successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('danger', ___('alert.something_went_wrong_please_try_again'));
        }
    }

    public function translate(Request $request): JsonResponse|RedirectResponse
    {
        $data['page']      = $this->setting->allTranslate();
        $data['translates']      = $this->setting->translates();
        $data['languages']      = $this->lang_repo->all();
        $data['title']       = ___('website.translate_general_setting');

        if ($request->expectsJson()) return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        return redirect()->to(spa_url('settings/translate'));
    }


    public function translateUpdate(Request $request): JsonResponse|RedirectResponse
    {
        $result = $this->setting->translateUpdate($request);
        if($result['status']){
            if ($request->expectsJson()) return response()->json(['message' => $result['message']]);
            return redirect()->route('settings.general-settings')->with('success', $result['message']);
        }
        if ($request->expectsJson()) return response()->json(['message' => $result['message']], 422);
        return back()->with('danger', $result['message']);
    }
}
