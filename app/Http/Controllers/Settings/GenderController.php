<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gender\GenderStoreRequest;
use App\Http\Requests\Gender\GenderUpdateRequest;
use App\Interfaces\GenderInterface;
use App\Repositories\LanguageRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class GenderController extends Controller
{
    private $gender;

    private $lang_repo;

    public function __construct(GenderInterface $gender, LanguageRepository $lang_repo)
    {
        if (! Schema::hasTable('settings') && ! Schema::hasTable('users')) {
            abort(400);
        }
        $this->gender = $gender;
        $this->lang_repo = $lang_repo;
    }

    public function index(Request $request): JsonResponse|RedirectResponse
    {
        $data['genders'] = $this->gender->getAll();
        $data['title'] = ___('settings.genders');
        if ($request->expectsJson()) {
            return response()->json([
                'data' => $data['genders'],
                'meta' => ['title' => $data['title']],
            ]);
        }

        return redirect()->to(spa_url('settings/genders'));
    }

    public function create(Request $request): JsonResponse|RedirectResponse
    {
        $data['title'] = ___('settings.create_gender');
        if ($request->expectsJson()) {
            return response()->json(['meta' => $data]);
        }

        return redirect()->to(spa_url('settings/genders/create'));
    }

    public function store(GenderStoreRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->gender->store($request);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('genders.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function edit(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['gender'] = $this->gender->show($id);
        $data['title'] = ___('settings.edit_gender');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(spa_url('settings/genders/'.$id.'/edit'));
    }

    public function translate(Request $request, $id): JsonResponse|RedirectResponse
    {
        $data['gender'] = $this->gender->show($id);
        $data['translates'] = $this->gender->translates($id);
        $data['languages'] = $this->lang_repo->all();
        $data['title'] = ___('settings.edit_gender');
        if ($request->expectsJson()) {
            return response()->json(['data' => $data, 'meta' => ['title' => $data['title']]]);
        }

        return redirect()->to(spa_url('settings/genders/'.$id.'/translate'));
    }

    public function translateUpdate(Request $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->gender->translateUpdate($request, $id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('genders.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function update(GenderUpdateRequest $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->gender->update($request, $id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('genders.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }

    public function delete(Request $request, $id): JsonResponse|RedirectResponse
    {
        $result = $this->gender->destroy($id);
        if ($result['status']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $result['message']]);
            }

            return redirect()->route('genders.index')->with('success', $result['message']);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message']], 422);
        }

        return back()->with('danger', $result['message']);
    }
}
