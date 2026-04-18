<?php

namespace App\Http\Controllers\WebsiteSetup;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use App\Repositories\WebsiteSetup\SliderRepository;
use App\Http\Requests\WebsiteSetup\Slider\SliderStoreRequest;
use App\Http\Requests\WebsiteSetup\Slider\SliderUpdateRequest;
use App\Repositories\WebsiteSetup\SectionsRepository;
use App\Repositories\LanguageRepository;

class SectionsController extends Controller
{
    private $sectionsRepo;
    private $lang_repo;

    function __construct(SectionsRepository $sectionsRepo, LanguageRepository $lang_repo)
    {
        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        }
        $this->sectionsRepo                  = $sectionsRepo;
        $this->lang_repo                  = $lang_repo;
    }

    public function index()
    {
        $data['sections'] = $this->sectionsRepo->getAll();
        $data['title'] = ___('settings.sections');
        return view('website-setup.sections.index', compact('data'));
    }

    public function edit($id)
    {
        $data['sections']    = $this->sectionsRepo->show($id);
        $data['title']       = ___('website.edit_sections');
        return view('website-setup.sections.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $result = $this->sectionsRepo->update($request, $id);
        if($result['status']){
            return redirect()->route('sections.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message']);
    }

    public function addSocialLink(Request $request)
    {
        return view('website-setup.sections.add_social_link')->render();
    }

    public function addChooseUs(Request $request)
    {
        return view('website-setup.sections.add_choose_us')->render();
    }

    public function addAcademicCurriculum(Request $request)
    {
        return view('website-setup.sections.add_academic_curriculum')->render();
    }

    public function translate($id)
    {
        $data['sections']      = $this->sectionsRepo->show($id);
        $data['translates']      = $this->sectionsRepo->translates($id);
        $data['languages']      = $this->lang_repo->all();
        $data['title']       = ___('website.edit_sections');

        return view('website-setup.sections.translate', compact('data'));
    }


    public function translateUpdate(Request $request, $id)
    {
        $result = $this->sectionsRepo->translateUpdate($request, $id);
        if($result['status']){
            return redirect()->route('sections.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message']);
    }


}
