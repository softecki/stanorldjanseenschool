<?php

namespace App\Http\Controllers\Settings;

use App\Models\Religion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Interfaces\ReligionInterface;
use Illuminate\Support\Facades\Schema;
use App\Repositories\LanguageRepository;
use App\Http\Requests\Religion\ReligionStoreRequest;
use App\Http\Requests\Religion\ReligionUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ReligionController extends Controller
{
    private $religion;

    private $lang_repo;

    function __construct(ReligionInterface $religion , LanguageRepository $lang_repo)
    {

        if (!Schema::hasTable('settings') && !Schema::hasTable('users')  ) {
            abort(400);
        }
        $this->religion       = $religion;
        $this->lang_repo                  = $lang_repo;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['religions'] = $this->religion->getAll();
        $data['title'] = ___('settings.religions');
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['religions'],
                'meta' => ['title' => $data['title']],
            ]);
        }
        return redirect()->to(spa_url('settings/religions'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title']       = ___('settings.create_religion');
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }
        return redirect()->to(spa_url('settings/religions/create'));
    }

    public function store(ReligionStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->religion->store($request);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('religions.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['religion']        = $this->religion->show($id);
        $data['title']       = ___('settings.edit_religion');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('settings/religions/'.$id.'/edit'));
    }

    public function translate(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['religion']        = $this->religion->show($id);
        $data['translates']      = $this->religion->translates($id);
        $data['languages']      = $this->lang_repo->all();
        $data['title']       = ___('website.edit_page');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }
        return redirect()->to(spa_url('settings/religions/'.$id.'/translate'));
    }

    public function translateUpdate(Request $request, $id): JsonResponse|RedirectResponse{
        $result = $this->religion->translateUpdate($request, $id);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('religions.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }


    public function update(ReligionUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->religion->update($request, $id);
        if($result['status']){
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('religions.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }

    public function delete(Request $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->religion->destroy($id);
        if($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }
            return redirect()->route('religions.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }
        return back()->with('danger', $result['message']);
    }
}
