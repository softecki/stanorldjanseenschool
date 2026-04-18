<?php

namespace Modules\MainApp\Http\Controllers;

use App;
use Config;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Session;
use Illuminate\Support\Facades\Schema;
use App\Interfaces\PermissionInterface;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Language\LanguageStoreRequest;
use App\Http\Requests\Language\LanguageUpdateRequest;
use Modules\MainApp\Http\Repositories\FlagIconRepository;
use Modules\MainApp\Http\Repositories\LanguageRepository;

class LanguageController extends Controller
{
    private $langRepo;
    private $permission;
    private $flagRepo;

    function __construct(
        LanguageRepository $langRepo, 
        PermissionInterface $permissionInterface, 
        FlagIconRepository $flagRepo
    )
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        } 
        $this->langRepo   = $langRepo;
        $this->permission = $permissionInterface;
        $this->flagRepo   = $flagRepo;
    }

    public function index()
    {
        $data['languages']  = $this->langRepo->getAll();
        $data['title']      = ___('common.languages');
        return view('mainapp::languages.index',compact('data'));
    }

    public function create()
    {
        $data['title']       = ___('common.create_language');
        $data['permissions'] = $this->permission->all();
        $data['flagIcons']   = $this->flagRepo->getAll();
        return view('mainapp::languages.create', compact('data'));
    }

    public function store(LanguageStoreRequest $request)
    {
        $result = $this->langRepo->store($request);
        if ($result) {
            return redirect()->route('languages.index')->with('success', ___('alert.language_created_successfully'));
        }
        return redirect()->route('languages.index')->with('danger', ___('alert.something_went_wrong_please_try_again'));
    }

    public function edit($id)
    {
        $data['language']    = $this->langRepo->show($id);
        $data['title']       = ___('common.languages');
        $data['permissions'] = $this->permission->all();
        $data['flagIcons']   = $this->flagRepo->getAll();
        return view('mainapp::languages.edit',compact('data'));
    }

    public function update(LanguageUpdateRequest $request,$id)
    {
        $result = $this->langRepo->update($request,$id);
        if($result){
            return redirect()->route('languages.index')->with('success', ___('alert.language_updated_successfully'));
        }
        return redirect()->route('languages.index')->with('danger', ___('alert.something_went_wrong_please_try_again'));
    }

    public function delete($id)
    {
        if ($this->langRepo->destroy($id)):
            $success[0] = ___('alert.deleted_successfully');
            $success[1] = "success";
            $success[2] = ___('alert.deleted');
            $success[3] = ___('alert.OK');
        else:
            $success[0] = ___('alert.something_went_wrong_please_try_again');
            $success[1] = 'error';
            $success[2] = ___('alert.oops');
        endif;
        return response()->json($success);

    }

    public function terms($id)
    {
        $data = $this->langRepo->terms($id);
        return view('mainapp::languages.terms',compact('data'));
    }

    public function termsUpdate(Request $request, $code)
    {
        $result = $this->langRepo->termsUpdate($request, $code);

        if($result){
            return redirect()->route('languages.index')->with('success', ___('alert.language_terms_updated_successfully'));
        }
        return redirect()->route('languages.index')->with('danger', ___('alert.something_went_wrong_please_try_again'));
    }

    public function changeModule(Request $request)
    {
        $path           = base_path('lang/' . $request->code);
        $jsonString     = file_get_contents(base_path("lang/$request->code/$request->module.json"));
        $data['terms']  = json_decode($jsonString, true);

        return view('mainapp::languages.ajax_terms', compact('data'))->render();
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
