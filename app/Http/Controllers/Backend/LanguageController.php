<?php

namespace App\Http\Controllers\Backend;

use App;
use Config;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use App\Interfaces\FlagIconInterface;
use App\Interfaces\LanguageInterface;
use Session;
use Illuminate\Support\Facades\Schema;
use App\Interfaces\PermissionInterface;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Language\LanguageStoreRequest;
use App\Http\Requests\Language\LanguageUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    private $language;
    private $permission;
    private $flagIcon;

    function __construct(LanguageInterface $languageInterface, PermissionInterface $permissionInterface, FlagIconInterface $flagIconInterface)
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        }
        $this->language   = $languageInterface;
        $this->permission = $permissionInterface;
        $this->flagIcon   = $flagIconInterface;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['languages']  = $this->language->getAll();
        $data['title']      = ___('common.languages');
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['languages'],
                'meta' => ['title' => $data['title']],
            ]);
        }
        return redirect()->to(spa_url('languages'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']       = ___('common.create_language');
        $data['permissions'] = $this->permission->all();
        $data['flagIcons']   = $this->flagIcon->getAll();
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return redirect()->to(spa_url('languages/create'));
    }

    public function store(LanguageStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->language->store($request);
        if ($result) {
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.language_created_successfully')]);
            }
            return redirect()->route('languages.index')->with('success', ___('alert.language_created_successfully'));
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
        }
        return redirect()->route('languages.index')->with('danger', ___('alert.something_went_wrong_please_try_again'));
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['language']    = $this->language->show($id);
        $data['title']       = ___('common.languages');
        $data['permissions'] = $this->permission->all();
        $data['flagIcons']   = $this->flagIcon->getAll();
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('languages/'.$id.'/edit'));
    }

    public function update(LanguageUpdateRequest $request,$id): JsonResponse|RedirectResponse
    {
        $result = $this->language->update($request,$id);
        if($result){
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.language_updated_successfully')]);
            }
            return redirect()->route('languages.index')->with('success', ___('alert.language_updated_successfully'));
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
        }
        return redirect()->route('languages.index')->with('danger', ___('alert.something_went_wrong_please_try_again'));
    }

    public function delete(Request $request, $id): JsonResponse|RedirectResponse
    {
        if ($this->language->destroy($id)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.deleted_successfully')]);
            }
            return redirect()->route('languages.index')->with('success', ___('alert.deleted_successfully'));
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
        }
        return redirect()->route('languages.index')->with('danger', ___('alert.something_went_wrong_please_try_again'));

    }

    public function terms(Request $request, $id): JsonResponse|RedirectResponse
    {
         $data = $this->language->terms($id);
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data,
                'meta' => ['title' => $data['title'] ?? 'Language Terms'],
            ]);
        }
        return redirect()->to(spa_url('languages/'.$id.'/terms'));
    }

    public function termsUpdate(Request $request, $code): JsonResponse|RedirectResponse
    {
        $result = $this->language->termsUpdate($request, $code);

        if($result){
            if ($request->expectsJson()) {
                return response()->json(['message' => ___('alert.language_terms_updated_successfully')]);
            }
            return redirect()->back()->with('success', ___('alert.language_terms_updated_successfully'));
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => ___('alert.something_went_wrong_please_try_again')], 422);
        }
        return redirect()->back()->with('danger', ___('alert.something_went_wrong_please_try_again'));
    }

    public function changeModule(Request $request): JsonResponse
    {
        $path           = base_path('lang/' . $request->code);
        $jsonString     = file_get_contents(base_path("lang/$request->code/$request->module.json"));
        $data['terms']  = json_decode($jsonString, true);
        return response()->json(['data' => $data]);
    }


    public function changeLanguage(Request $request)
    {
        $path = base_path('lang/' . $request->code);
        if(is_dir($path)){
            Session::put('locale', $request->code);
            return 1;
        }
        return 0;

    }

}
